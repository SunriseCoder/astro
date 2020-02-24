<?php
    if (!class_exists('Db')) {
        include $_SERVER["DOCUMENT_ROOT"].'/utils/db.php';
    }

    class Language {
        public $id;
        public $code;
        public $name;
    }

    class Keyword {
        public $id;
        public $code;
    }

    class Translation {
        public $id;
        public $keywordId;
        public $languageId;
        public $text;
    }

    class LanguageDao {
        public static function getAll() {
            $sql = 'SELECT * FROM i18n_languages ORDER BY id';
            $queryResult = Db::query($sql);
            $languages = self::fetchAll($queryResult);
            return $languages;
        }

        public static function fetchAll($queryResult) {
            $languages = [];
            foreach ($queryResult as $queryRow) {
                $language = new Language();
                $language->id = $queryRow['id'];
                $language->code = $queryRow['code'];
                $language->name = $queryRow['name'];

                $languages[$language->id] = $language;
            }
            return $languages;
        }
    }

    class KeywordDao {
        public static function getAll() {
            $sql = 'SELECT * FROM i18n_keywords ORDER BY id';
            $queryResult = Db::query($sql);
            $keywords = self::fetchAll($queryResult);
            return $keywords;
        }

        public static function fetchAll($queryResult) {
            $keywords = [];
            foreach ($queryResult as $queryRow) {
                $keyword = new Keyword();
                $keyword->id = $queryRow['id'];
                $keyword->code = $queryRow['code'];

                $keywords[$keyword->id] = $keyword;
            }
            return $keywords;
        }

        public static function insert($keyword) {
            $sql = 'INSERT INTO i18n_keywords (code) VALUES (?)';
            $result = Db::prepStmt($sql, 's', [$keyword]);
            return $result;
        }
    }

    class TranslationDao {
        public static function getAll() {
            $sql = 'SELECT * FROM i18n_translations ORDER BY id';
            $queryResult = Db::query($sql);
            $translations = self::fetchAll($queryResult);
            return $translations;
        }

        public static function fetchAll($queryResult) {
            $translations = [];
            foreach ($queryResult as $queryRow) {
                $translation = new Translation();
                $translation->id = $queryRow['id'];
                $translation->keywordId = $queryRow['keyword_id'];
                $translation->languageId = $queryRow['language_id'];
                $translation->text = $queryRow['text'];

                $translations[$translation->id] = $translation;
            }
            return $translations;
        }
    }
?>
