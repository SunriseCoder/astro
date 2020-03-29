<?php
    /**
     * This script scanning source code for the translation keywords usage and comparing it with database values
     */
    if (!class_exists('Config')) { include $_SERVER["DOCUMENT_ROOT"].'/config/config.php'; }
    if (Config::ENV != 'Dev') {
        die('Run this script on development environment only');
    }

    if (!class_exists('Db')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/db.php'; }

    // Searching all possible files
    $path = $_SERVER["DOCUMENT_ROOT"].'/';
    $excludeFolders = ['.git', '.settings', 'backups', 'config', 'css', 'dev-utils', 'fonts', 'images', 'logs', 'tmp'];
    $excludeFiles = ['.htaccess', '.buildpath', '.project', 'LICENSE', '.md', '.js', '.ico', '.css', '.', '..'];
    $it = new RecursiveDirectoryIterator($path);
    $files = [];
    foreach (new RecursiveIteratorIterator($it) as $filePath) {
        $file = substr($filePath, strlen($path));
        foreach ($excludeFolders as $excludeFolder) {
            if (substr($file, 0, strlen($excludeFolder)) == $excludeFolder) {
                continue 2;
            }
        }

        foreach ($excludeFiles as $excludeFile) {
            if (strlen($file) >= strlen($excludeFile) && substr($file, strlen($file) - strlen($excludeFile)) == $excludeFile) {
                continue 2;
            }
        }

        $files []= $file;
        echo $file.'<br />';
    }
    echo '<br />';

    // Parsing source code files
    $keywordsInCode = [];
    $translationsInCode = [];
    foreach ($files as $file) {
        echo 'Parsing file: '.$file.'<br />';
        $fileContent = file_get_contents($_SERVER["DOCUMENT_ROOT"].'/'.$file);
        $fileContent = str_replace("\n", '', $fileContent);

        // Looking for the keyword usage Tr::trs
        $offset = 0;
        while ($startPos = strpos($fileContent, 'Tr::trs(', $offset)) {
            $endPos = strpos($fileContent, ')', $startPos);
            $translationFunction = substr($fileContent, $startPos, $endPos - $startPos + 1);
            $translation = parseTrs($translationFunction);
            if (!empty($translationsInCode[$translation->keyword])) {
                $oldTranslation = $translationsInCode[$translation->keyword];
                if ($translation->text != $oldTranslation->text) {
                    die('Keyword '.$translation->keyword.' has different default translations');
                }
            }
            if (!empty($translation->text)) {
                $translationsInCode[$translation->keyword] = $translation;
            }
            $keywordsInCode[$translation->keyword] = 1;
            $offset = $endPos;
        }

        // Looking for the keyword usage Tr::format
        $offset = 0;
        while ($startPos = strpos($fileContent, 'Tr::format(', $offset)) {
            $endPos = strpos($fileContent, ')', $startPos);
            $translationFunction = substr($fileContent, $startPos, $endPos - $startPos + 1);
            $translation = parseFormat($translationFunction);
            if (!empty($translationsInCode[$translation->keyword])) {
                $oldTranslation = $translationsInCode[$translation->keyword];
                if ($translation->text != $oldTranslation->text) {
                    die('Keyword '.$translation->keyword.' has different default translations');
                }
            }
            if (!empty($translation->text)) {
                $translationsInCode[$translation->keyword] = $translation;
            }
            $keywordsInCode[$translation->keyword] = 1;
            $offset = $endPos;
        }
    }
    echo '<br />';

    // Connecting to the database
    $conn = new mysqli('localhost', 'root', '', 'astro-test');
    if ($conn->connect_error) {
        die('Connection failed: '.$conn->connect_error);
    }
    $conn->set_charset('utf8');
    $conn->query('SET time_zone = \'UTC\'');

    // Keywords Query
    $keywordsInDb = [];
    $query = $conn->query('SELECT k.code FROM i18n_keywords k WHERE k.code NOT LIKE \'entities%\'');
    while ($row = mysqli_fetch_assoc($query)) {
        $keywordsInDb[$row['code']] = $row;
    }

    // Translations Query
    $translationsInDb = [];
    $sql = 'SELECT k.code AS keyword_code,
                   t.text AS translation_text
              FROM i18n_keywords k
         LEFT JOIN i18n_translations t on t.keyword_id = k.id
         LEFT JOIN i18n_languages l on l.id = t.language_id
             WHERE l.code = (SELECT value FROM settings WHERE code = \'DEFAULT_LANGUAGE_CODE\')
               AND k.code NOT LIKE \'entities%\'';
    $query = $conn->query($sql);
    while ($row = mysqli_fetch_assoc($query)) {
        $translationsInDb[$row['keyword_code']] = [$row['keyword_code'], $row['translation_text']];
    }

    // Escaping Translations
    foreach ($translationsInDb as &$translation) {
        $translation[1] = str_replace('\'', '\\\'', $translation[1]);
        $translation[1] = str_replace("\n", '\\n', $translation[1]);
    }


    echo 'In database: Keywords: '.count($keywordsInDb).', Translations: '.count($translationsInDb).'<br />';
    echo 'In     code: Keywords: '.count($keywordsInCode).', Translations: '.count($translationsInCode).'<br /><br />';

    // New Keywords
    echo 'New Keywords:<br />';
    $newKeywords = [];
    foreach ($keywordsInCode as $key => $value) {
        if (empty($keywordsInDb[$key])) {
            $newKeywords []= ['keyword' => $key, 'translation' => $translationsInCode[$key]->text];
            echo $key.' -> '.$translationsInCode[$key]->text.'<br />';
        }
    }
    echo '<br />';

    // Keywords to delete
    echo 'Keywords to delete:<br />';
    $keywordsToDelete = [];
    foreach ($keywordsInDb as $key => $value) {
        if (empty($keywordsInCode[$key])) {
            $keywordsToDelete []= $key;
            echo $key.' -> '.$translationsInDb[$key][1].'<br />';
        }
    }
    echo '<br />';

    // Keywords with updated default translation
    $keywordsIntersection = [];
    $translationsChanged = [];
    echo 'Changed Translation:<br />';
    foreach ($keywordsInCode as $key => $value) {
        if (!empty($keywordsInDb[$key])) {
            $keywordsIntersection[$key] = 1;
        }
    }
    foreach ($keywordsIntersection as $key => $value) {
        $translationInCode = isset($translationsInCode[$key]->text) ? $translationsInCode[$key]->text : NULL;
        $translationInDb = $translationsInDb[$key][1];
        if (!empty($translationInCode) && $translationInCode != $translationInDb) {
            $translationsChanged []= ['keyword' => $key, 'translation' => $translationInCode];
            echo $key.':<br />&nbsp;&nbsp;&nbsp;&nbsp;'.$translationInDb.' -><br />&nbsp;&nbsp;&nbsp;&nbsp;'.$translationInCode.'<br />';
        }
    }
    echo '<br />';

    // SQL
    // Inserts
    foreach ($newKeywords as $keyword) {
        echo 'INSERT INTO i18n_keywords (code) VALUES (\''.$keyword['keyword'].'\');<br />';
        echo 'INSERT INTO i18n_translations (keyword_id, language_id, text, last_changed_time, last_changed_by_id)
              SELECT k.id, l.id, \''.$keyword['translation'].'\', now(), (SELECT value FROM settings WHERE code = \'DEFAULT_TRANSLATION_USER_ID\')
                FROM i18n_keywords k,
                     i18n_languages l
               WHERE k.code = \''.$keyword['keyword'].'\'
                 AND l.code = (SELECT value FROM settings WHERE code = \'DEFAULT_LANGUAGE_CODE\');<br />';
    }
    echo '<br />';
    // Deletions
    foreach ($keywordsToDelete as $keyword) {
        echo 'DELETE FROM i18n_translations WHERE keyword_id = (SELECT id FROM i18n_keywords WHERE code = \''.$keyword.'\');<br />';
        echo 'DELETE FROM i18n_keywords WHERE code = \''.$keyword.'\';<br />';
    }
    echo '<br />';
    // Updates
    foreach ($translationsChanged as $translation) {
        echo 'UPDATE i18n_translations
                 SET text = \''.$translation['translation'].'\',
                     last_changed_time = now(),
                     last_changed_by_id = (SELECT value FROM settings WHERE code = \'DEFAULT_TRANSLATION_USER_ID\')
               WHERE keyword_id = (SELECT id FROM i18n_keywords WHERE code = \''.$translation['keyword'].'\')
                 AND language_id = (SELECT id FROM i18n_languages WHERE code = (SELECT value FROM settings WHERE code = \'DEFAULT_LANGUAGE_CODE\'));<br />';
    }
    echo '<br />';

    function parseTrs($str) {
        $str = substr($str, 8);
        $str = substr($str, 0, strlen($str) - 1);
        $pos = strpos($str, ',');
        $translation = new stdClass();
        if ($pos) {
            $translation->keyword = substr($str, 1, $pos - 2);
            $translation->text = trim(substr($str, $pos + 1));
            $translation->text = substr($translation->text, 1, strlen($translation->text) - 2);
        } else {
            $translation->keyword = substr($str, 1, strlen($str) - 2);
        }
        return $translation;
    }

    function parseFormat($str) {
        //echo $str.'<br /><br />';
        $str = substr($str, 11);
        $str = substr($str, 0, strlen($str) - 1);
        $startPos = strpos($str, '[');
        $endPos = strpos($str, ']');
        $translation = new stdClass();
        $translation->keyword = trim(substr($str, 0, $startPos));
        $translation->keyword = substr($translation->keyword, 1, strlen($translation->keyword) - 3);
        $translation->text = trim(substr($str, $endPos + 2));
        $translation->text = strlen($translation->text) < 3 ? NULL : substr($translation->text, 1, strlen($translation->text) - 2);
        return $translation;
    }
?>
