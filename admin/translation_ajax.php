<?php
    if (!class_exists('LoginDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php'; }
    if (!LoginDao::checkPermissions(Permission::TranslationsView)) {
        echo 'Error: You don\'t have permissions for this operation';
        exit;
    }

    if (!class_exists('Language')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/i18n.php'; }
    if (!class_exists('Utils')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/utils.php'; }
    if (!class_exists('Json')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/json.php'; }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (Utils::areSet($_POST, ['id', 'text'])) {
            $translationId = $_POST['id'];
            $translation = TranslationDao::getById($translationId);
            if (isset($translation)) {
                $languageId = $translation->languageId;
                $language = LanguageDao::getById($languageId);
                if (LoginDao::checkPermissions(Permission::TRANSLATIONS_EDIT_PREFIX.$language->nameEnglish)) {
                    $translation->text = $_POST['text'];
                    TranslationDao::update($translation);
                } else {
                    echo 'Error: You don\'t have Permissions to edit translations for the language: '.$language->nameEnglish;
                    exit;
                }
            } else {
                echo 'Error: Unknown Translation ID: '.$translationId;
                exit;
            }
        } else if (Utils::areSet($_POST, ['keyword_id', 'language_id'])) {
            $languageId = $_POST['language_id'];
            $language = LanguageDao::getById($languageId);
            if (LoginDao::checkPermissions(Permission::TRANSLATIONS_EDIT_PREFIX.$language->nameEnglish)) {
                if (isset($_POST['text']) && !empty($_POST['text'])) {
                    TranslationDao::saveByKeywordIdAndLanguageId($_POST['keyword_id'], $_POST['language_id'], $_POST['text']);
                } else {
                    echo 'Error: You cannot save empty translation text';
                    exit;
                }
            } else {
                echo 'Error: You don\'t have Permissions to edit translations for the language: '.$language->nameEnglish;
                exit;
            }
        }
    }

    $data = [];
    $data['keywords'] = array_values(KeywordDao::getAll());
    $data['languages'] = array_values(LanguageDao::getAll());
    $data['translations'] = array_values(TranslationDao::getAll());
    $data['defaultLanguageId'] = LanguageDao::getDefault()->id;

    echo Json::encode($data);
?>
