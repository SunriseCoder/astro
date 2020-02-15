<?php
    if (!class_exists('Db')) {
        include $_SERVER["DOCUMENT_ROOT"].'/utils/db.php';
    }

    class Answer {
        public $id;
        public $session;
        public $question;
        public $questionOption;
        public $value;
    }

    class AnswerSession {
        public $id;
        public $user;
        public $questionnaire;
        public $ipAddress;
        public $date;
    }

    class Question {
        public $id;
        public $type;
        public $options;
        public $number;
        public $text;
    }

    class Questionnaire {

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
    }

    class AnswerDao {
        public static function insert(Answer $answer) {
            $sql = 'INSERT INTO answers (session_id, question_id, question_option_id, value) VALUES (?, ?, ?, ?)';
            $questionOptionId = $answer->questionOption ? $answer->questionOption->id : NULL;
            Db::prepStmt($sql, 'iiis', [$answer->session->id, $answer->question->id, $questionOptionId, $answer->value]);
            $answerId = Db::insertedId();
            return $answerId;
        }
    }

    class AnswerSessionDao {
        public static function hasAlreadyAnswered() {
            $sql = 'SELECT count(1) as c FROM answer_sessions WHERE user_id = ?';
            $user = LoginDao::getCurrentUser();
            if (!$user) {
                return FALSE;
            }

            $queryResult = Db::prepQuery($sql, 'i', [$user->id]);
            $result = $queryResult[0]['c'] > 0;
            return $result;
        }

        /**
         * Saves Participant Answers to the Database
         *
         * @return NULL on success or Array of Errors (strings)
         */
        public static function saveAnswers() {
            try {
                Db::autocommit(FALSE);
                Db::beginTransaction(0, 'SaveAnswers');

                // Save AnswerSession
                $answerSession = new AnswerSession();
                $answerSession->user = LoginDao::getCurrentUser();
                $answerSession->ipAddress = LoginDao::clientIP();
                $answerSession->date = DateTimeUtils::toDatabase(DateTimeUtils::now());
                $answerSession->id = self::insertSession($answerSession);

                // Save each Answer
                $matches = [];
                $complexAnswers = [];
                foreach ($_POST as $key => $value) {
                    // Don't save empty Answers
                    if (empty($value)) {
                        continue;
                    }

                    // Basic Questions like: 'answer-15'
                    if (preg_match('/^answer-([0-9]+)$/', $key, $matches)) {
                        $answer = new Answer();
                        $answer->session = $answerSession;

                        $question = QuestionDao::get($matches[1])[0];
                        $answer->question = $question;

                        switch ($question->type->code) {
                            case QuestionType::MultipleChoice:
                            case QuestionType::SingleChoice:
                                $questionOptionId = $value;
                                $questionOption = QuestionOptionDao::get($questionOptionId);
                                // Checking that we have existing QuestionOption and it belongs to the correct Question
                                if (!$questionOption || $questionOption->questionId != $question->id) {
                                    continue;
                                }
                                $answer->questionOption = $questionOption;
                                break;
                            case QuestionType::TextLine:
                            case QuestionType::TextArea:
                            case QuestionType::DateAndTime:
                            case QuestionType::Date:
                            case QuestionType::Time:
                            case QuestionType::Location:
                            case QuestionType::Complex:
                                $answer->value = $value;
                                brak;
                            default:
                                continue;
                        }

                        AnswerDao::insert($answer);
                    }

                    // Complex Questions like: 'answer-15-3'
                    if (preg_match('/^answer-([0-9]+)-([0-9]+)$/', $key, $matches)) {
                        if ($question->type->code != QuestionType::Complex) {
                            continue;
                        }

                        // Checking that the Question for the Answer exists
                        $question = QuestionDao::get($matches[1])[0];
                        if (!$question) {
                            continue;
                        }

                        // Creating new Answer if not in the Array yet
                        $answer = $complexAnswers[$matches[1]];
                        if (!$answer) {
                            $answer = new Answer();
                            $answer->array = FALSE;
                            $answer->question = $question;
                            $answer->session = $answerSession;
                            $answer->value = [];
                        }

                        // Checking that SubQuestion of Complex Question is exists
                        $subQuestion = QuestionDao::get($matches[2])[0];
                        if (!$subQuestion) {
                            continue;
                        }

                        // Checking that Complex Question contains this SubQuestion
                        $metadata = Json::decode($question->text);
                        if (!in_array($subQuestion->id, $metadata->subQuestions)) {
                            continue;
                        }

                        // Checking is SubQuestion Choose-type or Input Value type
                        switch ($question->type->code) {
                            case QuestionType::MultipleChoice:
                            case QuestionType::SingleChoice:
                                $questionOptionId = $value;
                                $questionOption = QuestionOptionDao::get($questionOptionId);
                                // Checking that we have existing QuestionOption and it belongs to the correct Question
                                if (!$questionOption || $questionOption->question->id != $subQuestion->id) {
                                    continue;
                                }
                                $answer->value[$subQuestion->id] = $questionOption->id;
                                break;
                            case QuestionType::TextLine:
                            case QuestionType::TextArea:
                            case QuestionType::DateAndTime:
                            case QuestionType::Date:
                            case QuestionType::Time:
                            case QuestionType::Location:
                            case QuestionType::Complex:
                                $answer->value[$subQuestion->id] = $value;
                                brak;
                            default:
                                continue;
                        }
                    }

                    // Complex Questions like: 'answer-15-3'
                    if (preg_match('/^answer-([0-9]+)-([0-9]+)-([0-9A-Za-z]+)$/', $key, $matches)) {
                        // Checking that the Question for the Answer exists
                        $questionId = $matches[1];
                        $question = QuestionDao::get($questionId)[0];
                        if (!$question) {
                            continue;
                        }

                        if ($question->type->code != QuestionType::Complex) {
                            continue;
                        }

                        // Creating new Answer if not in the Array yet
                        $answer = $complexAnswers[$questionId];
                        if (!$answer) {
                            $answer = new Answer();
                            $answer->array = TRUE;
                            $answer->question = $question;
                            $answer->session = $answerSession;
                            $answer->value = [];

                            $complexAnswers[$questionId] = $answer;
                        }

                        $entryNumber = $matches[2];
                        $entry = $answer->value[$entryNumber];
                        if (!$entry) {
                            $entry = [];
                        }

                        $subQuestionName = $matches[3];
                        $entry[$subQuestionName] = $value;
                        $answer->value[$entryNumber] = $entry;
                    }
                }

                foreach ($complexAnswers as $complexAnswer) {
                    $value = $complexAnswer->value;
                    if ($value && is_array($value)) {
                        $complexAnswer->value = Json::encode($value);
                    }
                    AnswerDao::insert($complexAnswer);
                }

                Db::commit();
            } catch (Exception $e) {
                Db::rollback();
                throw $e;
            }
        }

        public static function delete($id) {
            try {
                Db::autocommit(FALSE);
                Db::beginTransaction(0, 'DeleteAnswerSession');

                $sql = 'DELETE FROM answers WHERE session_id = ?';
                Db::prepStmt($sql, 'i', [$id]);

                $sql = 'DELETE FROM answer_sessions WHERE id = ?';
                Db::prepStmt($sql, 'i', [$id]);

                Db::commit();
                return TRUE;
            } catch (Exception $e) {
                Db::printError();
                Db::rollback();
                return FALSE;
            }
        }

        private static function insertSession(AnswerSession $answerSession) {
            $sql = 'INSERT INTO answer_sessions (user_id, questionnaire_id, ip_address, date)
                         VALUES (?, (SELECT value FROM settings WHERE code = \'DEFAULT_QUESTIONNAIRE\'), ?, ?)';
            Db::prepStmt($sql, 'iss', [$answerSession->user->id, $answerSession->ipAddress, $answerSession->date]);
            $sessionId = Db::insertedId();
            return $sessionId;
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
            $questions = self::fetchQuestions($queryResult);
            return $questions;
        }

        public static function get($id) {
            $sql = 'SELECT q.id as question_id,
                           q.number as question_number,
                           q.text as question_text,
                           qt.id as question_type_id,
                           qt.code as question_type_code,
                           qo.id as question_option_id,
                           qo.text as question_option_text
                      FROM questions q
                 LEFT JOIN question_types qt on qt.id = q.question_type_id
                 LEFT JOIN question_options qo on qo.question_id = q.id
                     WHERE q.id = ?
                  ORDER BY q.position, q.id, qo.position, qo.id ASC';
            $queryResult = Db::prepQuery($sql, 'i', [$id]);
            $questions = self::fetchQuestions($queryResult);
            return $questions;
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
                    $question->number = $queryRow['question_number'];
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

    class QuestionnaireDao {
        public static function countQuestions($id) {
            $sql = 'SELECT count(1) as c FROM questions WHERE questionnaire_id = ?';
            $queryResult = Db::prepQuery($sql, 'i', [$id]);
            $result = $queryResult[0]['c'];
            return $result;
        }
    }

    class QuestionOptionDao {
        public static function get($id) {
            $sql = 'SELECT qo.id as id,
                           qo.question_id as question_id,
                           qo.position as position,
                           qo.text as text
                      FROM question_options qo
                     WHERE qo.id = ?';
            $queryResult = Db::prepQuery($sql, 'i', [$id]);
            if (count($queryResult) == 0) {
                return NULL;
            }

            $questionOptionResult = $queryResult[0];
            $questionOption = new QuestionOption();
            $questionOption->id = $questionOptionResult['id'];
            $questionOption->questionId = $questionOptionResult['question_id'];
            $questionOption->position = $questionOptionResult['position'];
            $questionOption->text = $questionOptionResult['text'];

            return $questionOption;
        }
    }
?>
