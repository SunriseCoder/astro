<?php
    if (!class_exists('Db')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/db.php'; }
    if (!class_exists('Tr')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/i18n.php'; }

    class Language {
        public $id;
        public $code;
        public $nameEnglish;
        public $nameNative;
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
        public $lastChangedTime;
        public $lastChangedBy;
    }

    class LanguageDao {
        public static function getAll() {
            $sql = 'SELECT * FROM i18n_languages ORDER BY id';
            $queryResult = Db::query($sql);
            $languages = self::fetchAll($queryResult);
            return $languages;
        }

        public static function getById($id) {
            $sql = 'SELECT * FROM i18n_languages WHERE id = ?';
            $queryResult = Db::prepQuery($sql, 'i', [$id]);
            $languages = self::fetchAll($queryResult);
            $result = count($languages) > 0 ? array_values($languages)[0] : NULL;
            return $result;
        }

        public static function getDefault() {
            $sql = 'SELECT * FROM i18n_languages WHERE code = (SELECT value FROM settings WHERE code = \'DEFAULT_LANGUAGE_CODE\')';
            $queryResult = Db::query($sql);
            $languages = self::fetchAll($queryResult);
            if (count($languages) > 0) {
                $result = array_values($languages)[0];
                return $result;
            }

            throw new Exception('Error: Default Language not found, look database tables "languages" and "settings", parameter "DEFAULT_LANGUAGE_CODE"');
        }

        public static function fetchAll($queryResult) {
            $languages = [];
            foreach ($queryResult as $queryRow) {
                $language = new Language();
                $language->id = $queryRow['id'];
                $language->code = $queryRow['code'];
                $language->nameEnglish = $queryRow['name_english'];
                $language->nameNative = $queryRow['name_native'];

                $languages[$language->id] = $language;
            }
            return $languages;
        }
    }

    class KeywordDao {
        const QUESTION_PREFIX = 'entities.question.';
        const QUESTION_OPTION_PREFIX = 'entities.questionOption.';

        public static function getCountAll() {
            $sql = 'SELECT COUNT(1) as c FROM i18n_keywords';
            $queryResult = Db::query($sql);
            $result = $queryResult[0]['c'];
            return $result;
        }

        public static function getAll() {
            $sql = 'SELECT * FROM i18n_keywords ORDER BY id';
            $queryResult = Db::query($sql);
            $keywords = self::fetchAll($queryResult);
            return $keywords;
        }

        public static function getById($id) {
            $sql = 'SELECT * FROM i18n_keywords WHERE id = ?';
            $queryResult = Db::prepQuery($sql, 'i', [$id]);
            $keywords = self::fetchAll($queryResult);
            $result = count($keywords) > 0 ? array_values($keywords)[0] : NULL;
            return $result;
        }

        public static function getOrInsertByCode($keywordCode) {
            $keyword = Tr::getKeywordByCode($keywordCode);
            if (!isset($keyword)) {
                self::insert($keywordCode);

                $keywordId = Db::insertedId();
                $keyword = self::getById($keywordId);
                Tr::addKeyword($keyword);
            }

            return $keyword;
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

        public static function insert($keywordCode) {
            $sql = 'INSERT INTO i18n_keywords (code) VALUES (?)';
            $result = Db::prepStmt($sql, 's', [$keywordCode]);
            return $result;
        }

        public static function deleteById($keywordId) {
            $sql = 'DELETE FROM i18n_keywords WHERE id = ?';
            $result = Db::prepStmt($sql, 'i', [$keywordId]);
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

        public static function getById($id) {
            $sql = 'SELECT * FROM i18n_translations WHERE id = ?';
            $queryResult = Db::prepQuery($sql, 'i', [$id]);
            $translations = self::fetchAll($queryResult);
            $result = count($translations) > 0 ? array_values($translations)[0] : NULL;
            return $result;
        }

        public static function getByKeywordIdAndLanguageId($keywordId, $languageId) {
            $sql = 'SELECT * FROM i18n_translations WHERE keyword_id = ? AND language_id = ? ORDER BY id';
            $queryResult = Db::prepQuery($sql, 'ii', [$keywordId, $languageId]);
            $translations = self::fetchAll($queryResult);
            $result = count($translations) > 0 ? array_values($translations)[0] : NULL;
            return $result;
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

        public static function getStatisticMapByLanguageIds() {
            $sql = 'SELECT l.id as language_id,
                           l.name_english as language_name,
                           COALESCE(1d.c, 0) as 1day_count,
                           COALESCE(3d.c, 0) as 3days_count,
                           COALESCE(7d.c, 0) as 7days_count,
                           COALESCE(30d.c, 0) as 30days_count,
                           COALESCE(t.c, 0) as total_count
                      FROM i18n_languages l
                 LEFT JOIN (SELECT language_id, COUNT(1) as c FROM i18n_translations
                             WHERE last_changed_time > now() - INTERVAL 1 day GROUP BY language_id) 1d on 1d.language_id = l.id
                 LEFT JOIN (SELECT language_id, COUNT(1) as c FROM i18n_translations
                             WHERE last_changed_time > now() - INTERVAL 3 day GROUP BY language_id) 3d on 3d.language_id = l.id
                 LEFT JOIN (SELECT language_id, COUNT(1) as c FROM i18n_translations
                             WHERE last_changed_time > now() - INTERVAL 7 day GROUP BY language_id) 7d on 7d.language_id = l.id
                 LEFT JOIN (SELECT language_id, COUNT(1) as c FROM i18n_translations
                             WHERE last_changed_time > now() - INTERVAL 30 day GROUP BY language_id) 30d on 30d.language_id = l.id
                 LEFT JOIN (SELECT language_id, COUNT(1) as c FROM i18n_translations GROUP BY language_id) t on t.language_id = l.id
                  ORDER BY l.id';
            $queryResult = Db::query($sql);
            return $queryResult;
        }

        public static function saveQuestion($question) {
            // Question Text
            self::saveByKeywordCode(KeywordDao::QUESTION_PREFIX.$question->id.'.text', $question->text);

            // Question Markup
            if (isset($question->type) && $question->type->is(QuestionType::Complex)) {
                self::saveByKeywordCode(KeywordDao::QUESTION_PREFIX.$question->id.'.markup', $question->markup);
            }
        }

        public static function saveQuestionOption($option) {
            // QuestionOption Text
            self::saveByKeywordCode(KeywordDao::QUESTION_OPTION_PREFIX.$option->id.'.text', $option->text);
        }

        private static function saveByKeywordCode($keywordCode, $text) {
            $keyword = KeywordDao::getOrInsertByCode($keywordCode);
            $language = Tr::getCurrentLanguage();
            self::saveByKeywordIdAndLanguageId($keyword->id, $language->id, $text);
        }

        public static function saveByKeywordIdAndLanguageId($keywordId, $languageId, $text) {
            $translation = TranslationDao::getByKeywordIdAndLanguageId($keywordId, $languageId);
            if (isset($translation)) {
                $translation->text = $text;
                self::update($translation);
            } else {
                $translation = new Translation();
                $translation->keywordId = $keywordId;
                $translation->languageId = $languageId;
                $translation->text = $text;
                self::insert($translation);
            }
            Tr::updateTranslation($translation);
        }

        public static function insert($translation) {
            $translation->text = trim($translation->text);
            $sql = 'INSERT INTO i18n_translations (keyword_id, language_id, text, last_changed_time, last_changed_by_id) VALUES (?, ?, ?, ?, ?)';
            $changeTime = DateTimeUtils::toDatabase(DateTimeUtils::now());
            $userId = LoginDao::getCurrentUser()->id;
            $result = Db::prepStmt($sql, 'iissi', [$translation->keywordId, $translation->languageId, $translation->text, $changeTime, $userId]);
            return $result;
        }

        public static function update($translation) {
            $translation->text = trim($translation->text);
            $sql = 'UPDATE i18n_translations SET text = ?, last_changed_time = ?, last_changed_by_id = ? WHERE id = ?';
            $changeTime = DateTimeUtils::toDatabase(DateTimeUtils::now());
            $userId = LoginDao::getCurrentUser()->id;
            $result = Db::prepStmt($sql, 'ssii', [$translation->text, $changeTime, $userId, $translation->id]);
            return $result;
        }

        public static function deleteQuestionOption($optionId) {
            $keywordCode = KeywordDao::QUESTION_OPTION_PREFIX.$optionId.'.text';
            $keyword = Tr::getKeywordByCode($keywordCode);

            TranslationDao::deleteAllByKeywordId($keyword->id);
            Tr::deleteAllTranslationsByKeywordCode($keywordCode);

            KeywordDao::deleteById($keyword->id);
            Tr::deleteKeywordById($keyword->id);
        }

        public static function deleteAllByKeywordId($keywordId) {
            $sql = 'DELETE FROM i18n_translations WHERE keyword_id = ?';
            $result = Db::prepStmt($sql, 'i', [$keywordId]);
            return $result;
        }
    }
?>
