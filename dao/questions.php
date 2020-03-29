<?php
    if (!class_exists('Db')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/db.php'; }
    if (!class_exists('Tr')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/i18n.php'; }

    class Question {
        public $id;
        public $questionnaireId;
        public $questionnaire;
        public $typeId;
        public $type;
        public $options = [];
        public $number;
        public $position;
        public $text;
        public $markup;
        public $secret;
    }

    class Questionnaire {
        public $id;
        public $name;
    }

    class QuestionOption {
        public $id;
        public $questionId;
        public $position;
        public $text;
    }

    class QuestionType {
        const MultipleChoice = 'MULTIPLE_CHOICE';
        const SingleChoice = 'SINGLE_CHOICE';
        const TextLine = 'TEXT_LINE';
        const TextArea = 'TEXT_AREA';
        const DateAndTime = 'DATE_AND_TIME';
        const Location = 'LOCATION';
        const Complex = 'COMPLEX';
        const Date = 'DATE';
        const Time = 'TIME';

        public $id;
        public $code;
        public $name;

        public function is($type) {
            $result = $this->code == $type;
            return $result;
        }
    }

    class QuestionDao {
        /**
         * Get Questions for the Default Questionnaire
         *
         * @return Question[]
         */
        public static function getDefaultQuestionnaire() {
            $sql = 'SELECT q.id as question_id,
                           q.number as question_number,
                           q.position as question_position,
                           q.secret as question_secret,
                           qn.id as questionnaire_id,
                           qt.id as question_type_id,
                           qt.code as question_type_code,
                           qt.name as question_type_name,
                           qo.id as question_option_id
                      FROM questions q
                 LEFT JOIN questionnaires qn on qn.id = q.questionnaire_id
                 LEFT JOIN question_types qt on qt.id = q.question_type_id
                 LEFT JOIN question_options qo on qo.question_id = q.id
                     WHERE qn.id = (SELECT value FROM settings WHERE code = \'DEFAULT_QUESTIONNAIRE\')
                  ORDER BY q.position, q.id, qo.position, qo.id ASC';
            $queryResult = Db::query($sql);
            $questions = self::fetchQuestions($queryResult);
            return $questions;
        }

        public static function getAllForQuestionnaire($questionnaireId) {
            $sql = 'SELECT q.id as question_id,
                           q.number as question_number,
                           q.position as question_position,
                           q.secret as question_secret,
                           qn.id as questionnaire_id,
                           qt.id as question_type_id,
                           qt.code as question_type_code,
                           qt.name as question_type_name,
                           qo.id as question_option_id
                      FROM questions q
                 LEFT JOIN questionnaires qn on qn.id = q.questionnaire_id
                 LEFT JOIN question_types qt on qt.id = q.question_type_id
                 LEFT JOIN question_options qo on qo.question_id = q.id
                     WHERE qn.id = ?
                  ORDER BY q.position, q.id, qo.position, qo.id ASC';
            $queryResult = Db::prepQuery($sql, 'i', [$questionnaireId]);
            $questions = self::fetchQuestions($queryResult);
            return $questions;
        }

        public static function getAll() {
            $sql = 'SELECT q.id as question_id,
                           q.number as question_number,
                           q.position as question_position,
                           q.secret as question_secret,
                           qn.id as questionnaire_id,
                           qt.id as question_type_id,
                           qt.code as question_type_code,
                           qt.name as question_type_name,
                           qo.id as question_option_id
                      FROM questions q
                 LEFT JOIN questionnaires qn on qn.id = q.questionnaire_id
                 LEFT JOIN question_types qt on qt.id = q.question_type_id
                 LEFT JOIN question_options qo on qo.question_id = q.id
                  ORDER BY q.position, q.id, qo.position, qo.id ASC';
            $queryResult = Db::query($sql);
            $questions = self::fetchQuestions($queryResult);
            return $questions;
        }

        public static function getAllNonSecret() {
            $sql = 'SELECT q.id as question_id,
                           q.number as question_number,
                           q.position as question_position,
                           q.secret as question_secret,
                           qn.id as questionnaire_id,
                           qt.id as question_type_id,
                           qt.code as question_type_code,
                           qt.name as question_type_name,
                           qo.id as question_option_id
                      FROM questions q
                 LEFT JOIN questionnaires qn on qn.id = q.questionnaire_id
                 LEFT JOIN question_types qt on qt.id = q.question_type_id
                 LEFT JOIN question_options qo on qo.question_id = q.id
                     WHERE NOT q.secret
                  ORDER BY q.position, q.id, qo.position, qo.id ASC';
            $queryResult = Db::query($sql);
            $questions = self::fetchQuestions($queryResult);
            return $questions;
        }

        public static function getById($id) {
            $sql = 'SELECT q.id as question_id,
                           q.number as question_number,
                           q.position as question_position,
                           q.secret as question_secret,
                           qn.id as questionnaire_id,
                           qt.id as question_type_id,
                           qt.code as question_type_code,
                           qt.name as question_type_name,
                           qo.id as question_option_id
                      FROM questions q
                 LEFT JOIN questionnaires qn on qn.id = q.questionnaire_id
                 LEFT JOIN question_types qt on qt.id = q.question_type_id
                 LEFT JOIN question_options qo on qo.question_id = q.id
                     WHERE q.id = ?
                  ORDER BY q.position, q.id, qo.position, qo.id ASC';
            $queryResult = Db::prepQuery($sql, 'i', [$id]);
            $questions = self::fetchQuestions($queryResult);
            $result = count($questions) > 0 ? $questions[$id] : NULL;
            return $result;
        }

        private static function fetchQuestions($queryResult) {
            $questions = [];
            $questionTypes = [];
            $question = NULL;
            foreach ($queryResult as $queryRow) {
                // If it is the first Question or question_id is changed, creating new Question
                if ($question == NULL || $question->id != $queryRow['question_id']) {
                    $question = new Question();

                    $question->id = $queryRow['question_id'];
                    $question->questionnaireId = $queryRow['questionnaire_id'];
                    $question->number = $queryRow['question_number'];
                    $question->position = $queryRow['question_position'];
                    $question->secret = $queryRow['question_secret'];
                    $question->text = Tr::getQuestion($question->id, 'text');

                    // If the QuestionType is not already in the Map, creating and putting
                    if (isset($questionTypes[$queryRow['question_type_id']])) {
                        $questionType = $questionTypes[$queryRow['question_type_id']];
                    } else {
                        $questionType = new QuestionType();

                        $questionType->id = $queryRow['question_type_id'];
                        $questionType->code = $queryRow['question_type_code'];
                        $questionType->name = $queryRow['question_type_name'];

                        $questionTypes[$questionType->id] = $questionType;
                    }

                    $question->type = $questionType;
                    $question->typeId = $questionType->id;
                    if ($question->type->is(QuestionType::Complex)) {
                        $question->markup = Tr::getQuestion($question->id, 'markup');
                    }

                    $questions[$question->id]= $question;
                }

                // If the Question has QuestionOptions, creating and setting
                if ($queryRow['question_option_id'] != NULL) {
                    $questionOption = new QuestionOption();

                    $questionOption->id = $queryRow['question_option_id'];
                    $questionOption->text = Tr::getQuestionOption($questionOption->id, 'text');

                    $question->options[$questionOption->id] = $questionOption;
                }
            }

            return $questions;
        }

        public static function updateFromPost() {
            $questionId = $_POST['question_id'];
            try {
                Db::beginTransaction(0, 'SaveQuestion');

                // Saving Question
                $question = new Question();
                $question->id = $questionId;
                $question->questionnaireId = $_POST['questionnaire_id'];
                $question->typeId = $_POST['question_type_id'];
                $question->type = QuestionTypeDao::getById($question->typeId);
                $question->number = $_POST['question_number'];
                $question->position = $_POST['question_position'];
                $question->text = $_POST['question_text'];
                $question->markup = $_POST['question_markup'];
                QuestionDao::update($question);

                // Save All QuestionOptions
                QuestionOptionDao::saveAllFromPost($questionId);

                Db::commit();
            } catch (Exception $e) {
                Db::rollback();
                Logger::error($e->getMessage().'Stacktrace: '.$e->getTraceAsString());
                return $e->getMessage();
            }
        }

        public static function update($question) {
            $sql = 'UPDATE questions
                       SET questionnaire_id = ?,
                           question_type_id = ?,
                           number = ?,
                           position = ?
                     WHERE id = ?';
            $result = Db::prepStmt($sql, 'iisii', [$question->questionnaireId, $question->typeId, $question->number, $question->position, $question->id]);

            // Saving translations
            $result &= TranslationDao::saveQuestion($question);
            return $result;
        }

        public static function insertFromPost() {
            try {
                Db::beginTransaction(0, 'SaveQuestion');

                // Saving Question
                $question = new Question();
                $question->questionnaireId = $_POST['questionnaire_id'];
                $question->typeId = $_POST['question_type_id'];
                $question->number = $_POST['question_number'];
                if (isset($_POST['question_position']) && !empty($_POST['question_position'])) {
                    $question->position = $_POST['question_position'];
                } else {
                    $question->position = 10 * QuestionnaireDao::countQuestions($question->questionnaireId);
                }
                $question->text = $_POST['question_text'];
                $question->markup = $_POST['question_markup'];
                QuestionDao::insert($question);
                $question->id = Db::insertedId();

                // Saving translations
                TranslationDao::saveQuestion($question);

                // Save All QuestionOptions
                QuestionOptionDao::saveAllFromPost($question->id);


                Db::commit();
            } catch (Exception $e) {
                Db::rollback();
                Logger::error($e->getMessage().'Stacktrace: '.$e->getTraceAsString());
                return $e->getMessage();
            }
        }

        public static function insert($question) {
            $sql = 'INSERT INTO questions (questionnaire_id, question_type_id, number, position) VALUES (?, ?, ?, ?)';
            $result = Db::prepStmt($sql, 'iisi', [$question->questionnaireId, $question->typeId, $question->number, $question->position]);
            return $result;
        }
    }

    class QuestionnaireDao {
        public static function countQuestions($id) {
            $sql = 'SELECT count(1) as c FROM questions WHERE questionnaire_id = ?';
            $queryResult = Db::prepQuery($sql, 'i', [$id]);
            $result = $queryResult[0]['c'];
            return $result;
        }

        public static function getAll() {
            $sql = 'SELECT * FROM questionnaires ORDER BY id';
            $queryResult = Db::query($sql);
            $questionnaires = self::fetchAll($queryResult);
            return $questionnaires;
        }

        public static function getById($questionnaireId) {
            $sql = 'SELECT * FROM questionnaires WHERE id = ?';
            $queryResult = Db::prepQuery($sql, 'i', [$questionnaireId]);
            $questionnaires = self::fetchAll($queryResult);
            $result = count($questionnaires) > 0 ? array_values($questionnaires)[0] : NULL;
            return $result;
        }

        public static function fetchAll($queryResult) {
            $questionnaires = [];
            foreach ($queryResult as $queryRow) {
                $questionnaire = new Questionnaire();
                $questionnaire->id = $queryRow['id'];
                $questionnaire->name = $queryRow['name'];
                $questionnaires[$questionnaire->id] = $questionnaire;
            }
            return $questionnaires;
        }
    }

    class QuestionOptionDao {
        public static function getAll() {
            $sql = 'SELECT qo.id as id,
                           qo.question_id as question_id,
                           qo.position as position
                      FROM question_options qo
                  ORDER BY qo.id';
            $queryResult = Db::query($sql);
            $options = self::fetchAll($queryResult);
            return $options;
        }

        public static function getAllForQuestion($questionId) {
            $sql = 'SELECT qo.id as id,
                           qo.question_id as question_id,
                           qo.position as position
                      FROM question_options qo
                     WHERE qo.question_id = ?
                  ORDER BY qo.id';
            $queryResult = Db::prepQuery($sql, 'i', [$questionId]);
            $options = self::fetchAll($queryResult);
            return $options;
        }

        public static function get($id) {
            $sql = 'SELECT qo.id as id,
                           qo.question_id as question_id,
                           qo.position as position
                      FROM question_options qo
                     WHERE qo.id = ?';
            $queryResult = Db::prepQuery($sql, 'i', [$id]);
            $options = self::fetchAll($queryResult);
            return isset($options[$id]) ? $options[$id] : NULL;
        }

        public static function fetchAll($queryResult) {
            $options = [];
            foreach ($queryResult as $queryRow) {
                $option = new QuestionOption();
                $option->id = $queryRow['id'];
                $option->questionId = $queryRow['question_id'];
                $option->position = $queryRow['position'];
                $option->text = Tr::getQuestionOption($option->id, 'text');
                $options[$option->id] = $option;
            }
            return $options;
        }

        public static function saveAllFromPost($questionId) {
            if (isset($_POST['question_options'])) {
                // Saving Question Options
                $postElements = $_POST['question_options'];
                function question_options_compare($a, $b) {
                    return $a['position'] - $b['position'];
                }
                usort($postElements, 'question_options_compare');

                $position = 10;
                $storedOptions = QuestionOptionDao::getAllForQuestion($questionId);
                foreach ($postElements as $postElement) {
                    $option = new QuestionOption();
                    $option->questionId = $questionId;
                    $option->position = $position;
                    $option->text = $postElement['text'];

                    if (isset($postElement['id'])) {
                        // Update existing Question Option
                        $option->id = $postElement['id'];
                        self::update($option);
                    } else {
                        // Insert new Question Option
                        self::insert($option);
                        $option->id = Db::insertedId();
                    }

                    if (isset($storedOptions[$option->id])) {
                        unset($storedOptions[$option->id]);
                    }
                    $position += 10;
                }

                // Delete deleted Question Options from Database
                foreach ($storedOptions as $option) {
                    self::deleteById($option->id);
                }
            }
        }

        public static function update($option) {
            $sql = 'UPDATE question_options
                       SET question_id = ?,
                           position = ?
                     WHERE id = ?';
            $result = Db::prepStmt($sql, 'iii', [$option->questionId, $option->position, $option->id]);
            TranslationDao::saveQuestionOption($option);
            return $result;
        }

        public static function insert($option) {
            $sql = 'INSERT INTO question_options (question_id, position) VALUES (?, ?)';
            $result = Db::prepStmt($sql, 'ii', [$option->questionId, $option->position]);
            $option->id = Db::insertedId();
            TranslationDao::saveQuestionOption($option);
            return $result;
        }

        public static function deleteById($optionId) {
            $sql = 'DELETE FROM question_options WHERE id = ?';
            $result = Db::prepStmt($sql, 'i', [$optionId]);
            TranslationDao::deleteQuestionOption($optionId);
            return $result;
        }
    }

    class QuestionTypeDao {
        public static function getAll() {
            $sql = 'SELECT qt.id as id,
                           qt.code as code,
                           qt.name as name
                      FROM question_types qt
                  ORDER BY qt.id';
            $queryResult = Db::query($sql);
            $entities = self::fetchAll($queryResult);
            return $entities;
        }

        public static function getById($id) {
            $sql = 'SELECT qt.id as id,
                           qt.code as code,
                           qt.name as name
                      FROM question_types qt
                     WHERE qt.id = ?';
            $queryResult = Db::prepQuery($sql, 'i', [$id]);
            $entities = self::fetchAll($queryResult);
            $result = count($entities) > 0 ? array_values($entities)[0] : NULL;
            return $result;
        }

        public static function fetchAll($queryResult) {
            $entities = [];
            foreach ($queryResult as $queryRow) {
                $entity = new QuestionType();
                $entity->id = $queryRow['id'];
                $entity->code = $queryRow['code'];
                $entity->name = $queryRow['name'];
                $entities[$entity->id] = $entity;
            }
            return $entities;
        }
    }
?>
