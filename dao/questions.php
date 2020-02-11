<?php
    if (!class_exists('Db')) {
        include $_SERVER["DOCUMENT_ROOT"].'/utils/db.php';
    }

    class Questionnaire {

    }

    class QuestionnaireDao {

    }

    class QuestionType {
        public $id;
        public $code;
    }

    class Question {
        public $id;
        public $text;
        public $type;
        public $options;
    }

    class QuestionDao {
        public static function getDefaultQuestionnaire() {
            $sql = 'SELECT q.id as question_id,
                           q.text as question_text,
                           qt.id as question_type_id,
                           qt.code as question_type_code,
                           qo.id as question_option_id,
                           qo.text as question_option_text
                      FROM questions q
                 LEFT JOIN questionnaires qn on qn.id = q.questionnaire_id
                 LEFT JOIN question_types qt on qt.id = q.question_type_id
                 LEFT JOIN question_options qo on qo.question_id = q.id
                     WHERE qn.id = (SELECT value FROM settings WHERE code = \'DEFAULT_QUESTIONNAIRE\')
                  ORDER BY q.position, q.id, qo.position, qo.id ASC';
            $queryResult = Db::query($sql);
            $questions = [];
            $questionTypes = [];
            $question = NULL;
            foreach ($queryResult as $queryRow) {
                // If it is the first Question or question_id is changed, creating new Question
                if ($question == NULL || $question->id != $queryRow['question_id']) {
                    $question = new Question();

                    $question->id = $queryRow['question_id'];
                    $question->text = $queryRow['question_text'];

                    // If the QuestionType is not already in the Map, creating and putting
                    $questionType = $questionTypes[$queryRow['question_type_id']];
                    if (!$questionType) {
                        $questionType = new QuestionType();

                        $questionType->id = $queryRow['question_type_id'];
                        $questionType->code = $queryRow['question_type_code'];

                        $questionTypes[$questionType->id] = $questionType;
                    }

                    $question->type = $questionType;

                    $questions []= $question;
                }

                // If the Question has QuestionOptions, creating and setting
                if ($queryRow['question_option_id'] != NULL) {
                    $questionOption = new QuestionOption();

                    $questionOption->id = $queryRow['question_option_id'];
                    $questionOption->text = $queryRow['question_option_text'];

                    $question->options []= $questionOption;
                }
            }

            return $questions;
        }
    }

    class QuestionOption {
        public $id;
        public $text;
    }

    class QuestionOptionDao {

    }
?>
