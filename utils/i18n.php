<?php
    include $_SERVER["DOCUMENT_ROOT"].'/dao/i18n.php';

    class Tr {
        const DEFAULT_LANGUAGE = 'en';

        private static $keywordsById;
        private static $keywordsByCode;
        private static $languagesById;
        private static $languagesByCode;
        private static $translationMapByCodes;

        private static $currentLanguage;

        public static function init() {
            self::$currentLanguage = self::DEFAULT_LANGUAGE;

            // Keywords
            self::$keywordsById = KeywordDao::getAll();
            self::$keywordsByCode = [];
            foreach (self::$keywordsById as $keyword) {
                self::$keywordsByCode[$keyword->code] = $keyword;
            }

            // Languages
            self::$languagesById = LanguageDao::getAll();
            self::$languagesByCode = [];
            foreach (self::$languagesById as $language) {
                self::$languagesByCode[$language->code] = $language;
            }

            // Translation Map by Language, Keyword
            $translations = TranslationDao::getAll();
            self::$translationMapByCodes = [];
            self::$translationMapByCodes[self::DEFAULT_LANGUAGE] = [];
            foreach ($translations as $translation) {
                $language = self::$languagesById[$translation->languageId];
                $translationLanguageMap = isset(self::$translationMapByCodes[$language->code]) ? self::$translationMapByCodes[$language->code] : [];

                $keyword = self::$keywordsById[$translation->keywordId];
                $translationLanguageMap[$keyword->code] = $translation->text;

                self::$translationMapByCodes[$language->code] = $translationLanguageMap;
            }
        }

        public static function getLanguages() {
            return self::$languagesById;
        }

        public static function setCurrentLanguage($language) {
            self::$currentLanguage = isset(self::$languagesByCode[$language])
                ? self::$languagesByCode[$language] : self::$languagesByCode[self::DEFAULT_LANGUAGE];
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
            if (!isset(self::$keywordsByCode[$keyword])) {
                KeywordDao::insert($keyword);

                // Insering default value as translation for default language
                if ($default != NULL) {
                    $translation = new Translation();
                    $translation->keywordId = Db::insertedId();
                    $language = self::$languagesByCode[self::DEFAULT_LANGUAGE];
                    $translation->languageId = $language->id;
                    $translation->text = $default;
                    TranslationDao::insert($translation);
                }

                $result = $default == NULL ? $keyword : $default;
                return $result;
            }

            // If the Map for the Language exists (i.e. at least one translation into the Language) and it has the keyword translation
            // Othwerwise use the Map for the Default Language
            $language = self::$currentLanguage;
            $languageMap = isset(self::$translationMapByCodes[$language->code]) && isset(self::$translationMapByCodes[$language->code][$keyword])
                ? self::$translationMapByCodes[$language->code] : self::$translationMapByCodes[self::DEFAULT_LANGUAGE];

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
