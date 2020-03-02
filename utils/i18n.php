<?php
    if (!class_exists('Language')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/i18n.php'; }

    class Tr {
        // Maps
        private static $keywordsById;
        private static $keywordsByCode;
        private static $languagesById;
        private static $languagesByCode;
        private static $translationMapByCodes;

        // Fields
        private static $defaultLanguage;
        private static $currentLanguage;

        public static function init() {
            // Keywords
            self::$keywordsById = KeywordDao::getAll();
            self::$keywordsByCode = [];
            foreach (self::$keywordsById as $keyword) {
                self::$keywordsByCode[$keyword->code] = $keyword;
            }

            // Languages
            self::$defaultLanguage = LanguageDao::getDefault();
            self::$languagesById = LanguageDao::getAll();
            self::$languagesByCode = [];
            foreach (self::$languagesById as $language) {
                self::$languagesByCode[$language->code] = $language;
            }
            self::$currentLanguage = self::$defaultLanguage;

            // Translation Map by Language, Keyword
            $translations = TranslationDao::getAll();
            self::$translationMapByCodes = [];
            self::$translationMapByCodes[self::$defaultLanguage->code] = [];
            foreach ($translations as $translation) {
                $language = self::$languagesById[$translation->languageId];
                $translationLanguageMap = isset(self::$translationMapByCodes[$language->code]) ? self::$translationMapByCodes[$language->code] : [];

                $keyword = self::$keywordsById[$translation->keywordId];
                $translationLanguageMap[$keyword->code] = $translation->text;

                self::$translationMapByCodes[$language->code] = $translationLanguageMap;
            }
        }

        public static function addKeyword($keyword) {
            self::$keywordsById[$keyword->id] = $keyword;
            self::$keywordsByCode[$keyword->code] = $keyword;
        }
        public static function containsKeywordByCode($keywordCode) {
            $result = isset(self::$keywordsByCode[$keywordCode]);
            return $result;
        }

        public static function getKeywordByCode($keywordCode) {
            $result = isset(self::$keywordsByCode[$keywordCode]) ? self::$keywordsByCode[$keywordCode] : NULL;
            return $result;
        }

        public static function getLanguages() {
            return self::$languagesById;
        }

        public static function getCurrentLanguage() {
            return self::$currentLanguage;
        }

        public static function setCurrentLanguage($languageCode) {
            if (isset(self::$languagesByCode[$languageCode])) {
                self::$currentLanguage = self::$languagesByCode[$languageCode];
            }
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
        public static function trs($keywordCode, $default = NULL) {
            if (!isset(self::$keywordsByCode[$keywordCode])) {
                // If keyword not found, insert new keyword to database
                KeywordDao::insert($keywordCode);
                $keywordId = Db::insertedId();
                $keyword = KeywordDao::getById($keywordId);
                self::$keywordsById[$keyword->id] = $keyword;
                self::$keywordsByCode[$keyword->code] = $keyword;

                if ($default != NULL) {
                    // Insering default value as translation for default language
                    $translation = new Translation();
                    $translation->keywordId = $keyword->id;
                    $language = self::$defaultLanguage;
                    $translation->languageId = $language->id;
                    $translation->text = $default;

                    TranslationDao::insert($translation);

                    $translationId = Db::insertedId();
                    $translation = TranslationDao::getById($translationId);
                    self::$translationMapByCodes[$language->code][$keyword->code] = $translation->text;
                }

                $result = $default == NULL ? $keyword->code : $default;
                return $result;
            }

            // If the Map for the Language exists (i.e. at least one translation into the Language) and it has the keyword translation
            // Othwerwise use the Map for the Default Language
            $language = self::$currentLanguage;
            $languageMap = isset(self::$translationMapByCodes[$language->code]) && isset(self::$translationMapByCodes[$language->code][$keywordCode])
                ? self::$translationMapByCodes[$language->code] : self::$translationMapByCodes[self::$defaultLanguage->code];

            // Getting the translation text
            if (isset($languageMap[$keywordCode])) {
                $result = $languageMap[$keywordCode];
            } else if (!empty($default)) {
                $result = $default;
            } else {
                $result = $keywordCode;
            }

            return $result;
        }

        public static function updateTranslation($translation) {
            $keyword = self::$keywordsById[$translation->keywordId];
            self::$translationMapByCodes[self::$currentLanguage->code][$keyword->code] = $translation->text;
        }

        public static function deleteKeywordById($keywordId) {
            if (isset(self::$keywordsById[$keywordId])) {
                $keyword = self::$keywordsById[$keywordId];
                unset(self::$keywordsById[$keywordId]);

                if (isset(self::$keywordsByCode[$keyword->code])) {
                    unset(self::$keywordsByCode[$keyword->code]);
                }
            }
        }

        public static function deleteAllTranslationsByKeywordCode($keywordCode) {
            foreach (self::$languagesById as $language) {
                if (isset(self::$translationMapByCodes[$language->code])) {
                    $languageMap = self::$translationMapByCodes[$language->code];
                    if (isset($languageMap[$keywordCode])) {
                        unset($languageMap[$keywordCode]);
                    }
                }
            }
        }

        public static function getQuestion($questionId, $property) {
            $prefix = KeywordDao::QUESTION_PREFIX;
            $keywordCode = $prefix.$questionId.'.'.$property;
            $result = self::trs($keywordCode);
            return $result;
        }

        public static function getQuestionOption($questionOptionId, $property) {
            $prefix = KeywordDao::QUESTION_OPTION_PREFIX;
            $keywordCode = $prefix.$questionOptionId.'.'.$property;
            $result = self::trs($keywordCode);
            return $result;
        }
    }

    Tr::init();
    if (isset($_COOKIE['language'])) {
        Tr::setCurrentLanguage($_COOKIE['language']);
    }
?>
