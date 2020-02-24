<?php
    include $_SERVER["DOCUMENT_ROOT"].'/dao/i18n.php';

    Tr::init();

    class Tr {
        const DEFAULT_LANGUAGE = 'eng';

        private static $languages;
        private static $keywords;
        private static $translationMap;

        public static function init() {
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

        /**
         * Looking for a translation in the database
         * If $keyword for particular $language not found, return $default
         *
         * @param string $keyword keyword code
         * @param string $language language code
         * @param string $default text
         * @return string
         */
        public static function tr($keyword, $language, $default) {
            $result = self::tr($keyword, $language);
            if ($result == $keyword) {
                $result = $default;
            }
            return $result;
        }

        /**
         * Looking for a translation in the database
         * If $keyword for particular $language not found, return $keyword
         *
         * @param string $keyword keyword code
         * @param string $language language code
         * @return string
         */
        public static function tr($keyword, $language) {
            // If keyword not found, insert new keyword to database
            if (!isset(self::$keywords[$keyword])) {
                KeywordDao::insert($keyword);
                return $keyword;
            }

            // If the Map for the Language exists (i.e. at least one translation into the Language) and it has the keyword translation
            // Othwerwise use the Map for the Default Language
            $languageMap = isset(self::$translationMap[$language]) && isset(self::$translationMap[$language][$keyword])
                ? self::$translationMap[$language] : self::$translationMap[self::DEFAULT_LANGUAGE];

            $result = isset($languageMap[$keyword]) ? $languageMap[$keyword] : $keyword;
            return $result;
        }
    }
?>
