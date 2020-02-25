<?php
    include $_SERVER["DOCUMENT_ROOT"].'/dao/i18n.php';

    class Tr {
        const DEFAULT_LANGUAGE = 'en';

        private static $languages;
        private static $keywords;
        private static $translationMap;

        private static $currentLanguage;

        public static function init() {
            self::$currentLanguage = self::DEFAULT_LANGUAGE;

            self::$languages = LanguageDao::getAll();
            self::$keywords = KeywordDao::getAll();
            $translations = TranslationDao::getAll();

            // Translation Map by Language, Keyword
            self::$translationMap = [];
            self::$translationMap[self::DEFAULT_LANGUAGE] = [];
            foreach ($translations as $translation) {
                $language = self::$languages[$translation->languageId];
                $translationLanguageMap = isset(self::$translationMap[$language->code]) ? self::$translationMap[$language->code] : [];

                $keyword = self::$keywords[$translation->keywordId];
                $translationLanguageMap[$keyword->code] = $translation->text;

                self::$translationMap[$language->code] = $translationLanguageMap;
            }
        }

        public static function getLanguages() {
            return self::$languages;
        }

        public static function setCurrentLanguage($language) {
            self::$currentLanguage = $language;
        }

        /**
         * Format the translation text with the placeholders like {0}
         * If the pattern for the $keyword was not found, using $default
         * $parameters is a map with keys as placeholder text and value is the replacement
         *
         * @param string $keyword
         * @param array $parameters
         * @param string $default
         */
        public static function format($keyword, $parameters, $default = NULL) {
            $result = self::trs($keyword, $default);
            foreach ($parameters as $key => $value) {
                $result = str_replace('{'.$key.'}', $value, $result);
            }
            return $result;
        }

        /**
         * Looking for a translation in the database
         * If $keyword for $currentLanguage not found and $default is not NULL, return $default
         *
         * @param string $keyword keyword code
         * @param string $default text
         * @return string
         */
        public static function trs($keyword, $default = NULL) {
            // If keyword not found, insert new keyword to database
            if (!isset(self::$keywords[$keyword])) {
                KeywordDao::insert($keyword);
                return $keyword;
            }

            // If the Map for the Language exists (i.e. at least one translation into the Language) and it has the keyword translation
            // Othwerwise use the Map for the Default Language
            $language = self::$currentLanguage;
            $languageMap = isset(self::$translationMap[$language]) && isset(self::$translationMap[$language][$keyword])
                ? self::$translationMap[$language] : self::$translationMap[self::DEFAULT_LANGUAGE];

            // Getting the translation text
            if (isset($languageMap[$keyword])) {
                $result = $languageMap[$keyword];
            } else if (!empty($default)) {
                $result = $default;
            } else {
                $result = $keyword;
            }

            return $result;
        }
    }

    Tr::init();
?>
