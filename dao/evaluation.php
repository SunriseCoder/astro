<?php
    if (!class_exists('Db')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/db.php'; }
    if (!class_exists('Tr')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/i18n.php'; }
    if (!class_exists('Settings')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/settings.php'; }

    class AnswerEvaluationMethodEntity {
        public $id;
        public $code;
        public $description;
    }

    class AnswerEvaluationGroupEntity {
        public $id;
        public $methodId;
        public $method;
        public $astrologerAnswerGroupId;
        public $astrologerAnswerGroup;
        public $score;
    }

    class AnswerEvaluationDetailsEntity {
        public $id;
        public $evaluationGroupId;
        public $evaluationGroup;
        public $score;
    }

    class AnswerEvaluationMethodDao {
        public static function getAll() {
            $sql = 'SELECT em.*
                      FROM evaluation_methods em
                  ORDER BY id';
            $queryResult = Db::query($sql);
            $result = self::fetchAll($queryResult);
            return $result;
        }

        public static function fetchAll($queryResult) {
            $result = [];
            foreach ($queryResult as $queryRow) {
                $entry = new AnswerEvaluationMethodEntity();
                $entry->id = $queryRow['id'];
                $entry->code = $queryRow['code'];
                $entry->description = $queryRow['description'];

                $result[$entry->id] = $entry;
            }
            return $result;
        }
    }

    class AnswerEvaluationGroupDao {
        public static function getAll() {
            $sql = 'SELECT eg.*
                      FROM evaluation_groups eg
                  ORDER BY id';
            $queryResult = Db::query($sql);
            $result = self::fetchAll($queryResult);
            return $result;
        }

        public static function fetchAll($queryResult) {
            $result = [];
            foreach ($queryResult as $queryRow) {
                $entry = new AnswerEvaluationGroupEntity();
                $entry->id = $queryRow['id'];
                $entry->methodId = $queryRow['method_id'];
                $entry->astrologerAnswerGroupId = $queryRow['astrologer_answer_group_id'];
                $entry->score = $queryRow['score'];

                $result[$entry->id] = $entry;
            }
            return $result;
        }
    }

    class AnswerEvaluationDetailsDao {

    }
?>
