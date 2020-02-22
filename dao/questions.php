<?php
    if (!class_exists('Db')) {
        include $_SERVER["DOCUMENT_ROOT"].'/utils/db.php';
    }

    class Answer {
        public $id;
        public $sessionId;
        public $session;
        public $questionId;
        public $question;
        public $questionOptionId;
        public $questionOption;
        public $value;
    }

    class AnswerSession {
        public $id;
        public $originId;
        public $origin;
        public $userId;
        public $user;
        public $questionnaireId;
        public $questionnaire;
        public $ipAddress;
        public $date;
        public $answers = [];
    }

    class Question {
        public $id;
        public $questionnaireId;
        public $questionnaire;
        public $type;
        public $options = [];
        public $number;
        public $text;
        public $markup;
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

        public function is($type) {
            $result = $this->code == $type;
            return $result;
        }
    }

    class AnswerDao {
        public static function getAllNonSecret() {
            $sql = 'SELECT a.*
                      FROM answers a
                      JOIN questions q on q.id = a.question_id
                     WHERE NOT q.secret';
            $queryResult = Db::query($sql);
            $answers = self::fetchAll($queryResult);
            return $answers;
        }

        public static function getAllBySessionId($answerSessionId) {
            $sql = 'SELECT a.*
                      FROM answers a
                      JOIN questions q on q.id = a.question_id
                     WHERE a.session_id = ?';
            $queryResult = Db::prepQuery($sql, 'i', [$answerSessionId]);
            $answers = self::fetchAll($queryResult);
            return $answers;
        }

        public static function getNonSecretBySessionId($answerSessionId) {
            $sql = 'SELECT a.*
                      FROM answers a
                      JOIN questions q on q.id = a.question_id
                     WHERE NOT q.secret
                       AND a.session_id = ?';
            $queryResult = Db::prepQuery($sql, 'i', [$answerSessionId]);
            $answers = self::fetchAll($queryResult);
            return $answers;
        }

        public static function fetchAll($queryResult) {
            $answers = [];
            foreach ($queryResult as $queryRow) {
                $answer = new Answer();
                $answer->id = $queryRow['id'];
                $answer->sessionId = $queryRow['session_id'];
                $answer->questionId = $queryRow['question_id'];
                $answer->questionOptionId = $queryRow['question_option_id'];
                $answer->value = $queryRow['value'];

                $answers[$answer->id] = $answer;
            }
            return $answers;
        }

        public static function insert(Answer $answer) {
            $sql = 'INSERT INTO answers (session_id, question_id, question_option_id, value) VALUES (?, ?, ?, ?)';
            $questionOptionId = $answer->questionOption ? $answer->questionOption->id : NULL;
            Db::prepStmt($sql, 'iiis', [$answer->session->id, $answer->question->id, $questionOptionId, $answer->value]);
            $answerId = Db::insertedId();
            return $answerId;
        }
    }

    class AnswerSessionDao {
        public static function getGuessedIdsForCurrentUser() {
            $currentUser = LoginDao::getCurrentUser();
            $sql = 'SELECT origin_id from answer_sessions WHERE origin_id IS NOT NULL AND user_id = ?';
            $queryResult = Db::prepQuery($sql, 'i', [$currentUser->id]);
            $result = [];
            foreach ($queryResult as $queryRow) {
                $result[$queryRow['origin_id']] = TRUE;
            }
            return $result;
        }

        public static function hasAlreadyAnswered() {
            $sql = 'SELECT count(1) as c FROM answer_sessions WHERE origin_id IS NULL AND user_id = ?';
            $user = LoginDao::getCurrentUser();
            if (!$user) {
                return FALSE;
            }

            $queryResult = Db::prepQuery($sql, 'i', [$user->id]);
            $result = $queryResult[0]['c'] > 0;
            return $result;
        }

        public static function hasCurrentAstrologerAnsweredAlready($originId) {
            $currentUser = LoginDao::getCurrentUser();
            $sql = 'SELECT 1 FROM answer_sessions ast WHERE ast.origin_id = ? AND ast.user_id = ?';
            $queryResult = Db::prepQuery($sql, 'ii', [$originId, $currentUser->id]);
            $result = count($queryResult) > 0;
            return $result;
        }

        public static function get($id) {
            $sql = 'SELECT * FROM answer_sessions WHERE id = ?';
            $sessionsResult = Db::prepQuery($sql, 'i', [$id]);
            $answerSessions = self::fetchSessions($sessionsResult);
            $result = isset($answerSessions[$id]) ? $answerSessions[$id] : NULL;
            return $result;
        }

        public static function getAllOriginsWithNonSecretAnswers() {
            $sql = 'SELECT * FROM answer_sessions WHERE origin_id IS NULL ORDER BY id';
            $sessionsResult = Db::query($sql);
            $answerSessions = self::fetchSessions($sessionsResult);

            $answers = AnswerDao::getAllNonSecret();
            foreach ($answers as $answer) {
                $answerSessions[$answer->sessionId]->answers[$answer->id] = $answer;
            }

            return $answerSessions;
        }

        public static function getWithAllAnswers($answerSessionId) {
            // Loading Answer Session itself
            $sql = 'SELECT * FROM answer_sessions WHERE id = ?';
            $sessionsResult = Db::prepQuery($sql, 'i', [$answerSessionId]);
            $answerSessions = self::fetchSessions($sessionsResult);

            // Origin Session if set
            if (count($answerSessions) > 0 && isset($answerSessions[$answerSessionId]->originId)) {
                $answerSession = $answerSessions[$answerSessionId];
                $origins = self::getWithAllAnswers($answerSession->originId);
                $answerSession->origin = $origins[$answerSession->originId];
            }

            $answers = AnswerDao::getAllBySessionId($answerSessionId);
            foreach ($answers as $answer) {
                $answerSessions[$answer->sessionId]->answers[$answer->id] = $answer;
            }

            return $answerSessions;
        }

        public static function getWithNonSecretAnswers($answerSessionId) {
            $sql = 'SELECT * FROM answer_sessions WHERE id = ?';
            $sessionsResult = Db::prepQuery($sql, 'i', [$answerSessionId]);
            $answerSessions = self::fetchSessions($sessionsResult);

            $answers = AnswerDao::getNonSecretBySessionId($answerSessionId);
            foreach ($answers as $answer) {
                $answerSessions[$answer->sessionId]->answers[$answer->id] = $answer;
            }

            return $answerSessions;
        }

        public static function fetchSessions($queryResult) {
            $sessions = [];
            foreach ($queryResult as $queryRow) {
                $session = new AnswerSession();
                $session->id = $queryRow['id'];
                if (isset($queryRow['origin_id'])) {
                    $session->originId = $queryRow['origin_id'];
                }
                $session->userId = $queryRow['user_id'];
                $session->questionnaireId = $queryRow['questionnaire_id'];
                $session->ipAddress = $queryRow['ip_address'];
                $session->date = DateTimeUtils::fromDatabase($queryRow['date']);

                $sessions[$session->id] = $session;
            }

            return $sessions;
        }

        /**
         * Saves Participant Answers to the Database
         *
         * @return NULL on success or Array of Errors (strings)
         */
        public static function saveAnswers() {
            // Checking is it a Paricipant's answers or an Astrologer's if originId is set
            if (isset($_POST['id'])) {
                $originId = $_POST['id'];
                $answerSession = self::get($originId);

                // Checking that Origin Session does exists
                if (!isset($answerSession)) {
                    return '<font color="red">Original AnswerSession does not exists</font><br />';
                }

                // Checking that the Astrologer trying to guess a Participant's answers, not the Astrologer's answers
                if (isset($answerSession->originId)) {
                    return '<font color="red">Original AnswerSession is not Original, but an Astrologer\'s answers</font><br />';
                }

                // Checking that the Astrologer doesn't trying to solve his own answers
                $currentUser = LoginDao::getCurrentUser();
                if ($answerSession->userId == $currentUser->id) {
                    return '<font color="red">You are trying to solve your own answers</font><br />';
                }

                // Checking that the Astrologer didn't already solve this Answer Set
                $alreadyAnswered = self::hasCurrentAstrologerAnsweredAlready($originId);
                if ($alreadyAnswered) {
                    return '<font color="red">You have already solve Answer Session with ID: '.$originId.'</font><br />';
                }
            }

            try {
                Db::autocommit(FALSE);
                Db::beginTransaction(0, 'SaveAnswers');

                // Save AnswerSession
                $answerSession = new AnswerSession();
                if (isset($originId)) {
                    $answerSession->originId = $originId;
                }
                $answerSession->user = LoginDao::getCurrentUser();
                $answerSession->ipAddress = LoginDao::clientIP();
                $answerSession->date = DateTimeUtils::toDatabase(DateTimeUtils::now());
                $answerSession->id = self::insertSession($answerSession);

                // If this is the Answers of an Astrologer, copying all Non-Secret Answers
                if (isset($originId)) {
                    $sql = 'INSERT INTO answers (session_id, question_id, question_option_id, value)
                                 SELECT ?, a.question_id, a.question_option_id, a.value FROM answers a
                              LEFT JOIN questions q on q.id = a.question_id
                                  WHERE a.session_id = ? AND NOT q.secret';
                    Db::prepStmt($sql, 'ii', [$answerSession->id, $originId]);
                }

                // Save each Answer
                $matches = [];
                $complexAnswers = [];
                $questions = QuestionDao::getAll();
                $questionOptions = QuestionOptionDao::getAll();
                foreach ($_POST as $key => $value) {
                    // Don't save empty Answers
                    if (empty($value)) {
                        Logger::warning('Skipping Answer "'.$key.'" due to empty Value');
                        continue;
                    }

                    // Basic Questions like: 'answer-15'
                    if (preg_match('/^answer-([0-9]+)$/', $key, $matches)) {
                        $answer = new Answer();
                        $answer->session = $answerSession;

                        $questionId = $matches[1];
                        $question = $questions[$questionId];
                        if (!$question) {
                            Logger::warning('Skipping Answer "'.$key.'" -> "'.$value.'" due to Question with ID '.$questionId.' was not found');
                            continue;
                        }
                        $answer->question = $question;

                        switch ($question->type->code) {
                            case QuestionType::MultipleChoice:
                            case QuestionType::SingleChoice:
                                $questionOptionId = $value;
                                $questionOption = $questionOptions[$questionOptionId];
                                // Checking that we have existing QuestionOption and it belongs to the correct Question
                                if (!$questionOption) {
                                    Logger::warning('Skipping Answer "'.$key.'" -> "'.$value.'" due to QuestionOption with ID '.$questionOptionId.' does not exists');
                                    continue;
                                }

                                if ($questionOption->questionId != $question->id) {
                                    Logger::warning('Skipping Answer "'.$key.'" -> "'.$value.'" due to QuestionOption with ID '.$questionOptionId.
                                        'does not belongs to Question with ID '.$questionId);
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
                                break;
                            default:
                                Logger::warning('Skipping Answer "'.$key.'" -> "'.$value.'" due to unknown QuestionType "'.$question->type->code.'"');
                                continue;
                        }

                        AnswerDao::insert($answer);
                    }

                    // Complex Questions like: 'answer-15-3'
                    if (preg_match('/^answer-([0-9]+)-([0-9]+)-([0-9A-Za-z]+)$/', $key, $matches)) {
                        // Checking that the Question for the Answer exists
                        $questionId = $matches[1];
                        $question = $questions[$questionId];
                        if (!$question) {
                            Logger::warning('Skipping Answer "'.$key.'" -> "'.$value.'" due to Question with ID '.$questionId.' does not exists');
                            continue;
                        }

                        if ($question->type->code != QuestionType::Complex) {
                            Logger::warning('Skipping Answer "'.$key.'" -> "'.$value.'" due to Question with ID '.$questionId.' has Type which is not Complex');
                            continue;
                        }

                        // Creating new Answer if not in the Array yet
                        if (isset($complexAnswers[$questionId])) {
                            $answer = $complexAnswers[$questionId];
                        } else {
                            $answer = new Answer();
                            $answer->array = TRUE;
                            $answer->question = $question;
                            $answer->session = $answerSession;
                            $answer->value = [];

                            $complexAnswers[$questionId] = $answer;
                        }

                        $entryNumber = $matches[2];
                        if (isset($answer->value[$entryNumber])) {
                            $entry = $answer->value[$entryNumber];
                        } else {
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
                Logger::error($e->getMessage());
                Logger::showTrace($e->getTraceAsString());
                Db::rollback();
                return FALSE;
            }
        }

        private static function insertSession(AnswerSession $answerSession) {
            $sql = 'INSERT INTO answer_sessions (origin_id, user_id, questionnaire_id, ip_address, date)
                         VALUES (?, ?, (SELECT value FROM settings WHERE code = \'DEFAULT_QUESTIONNAIRE\'), ?, ?)';
            Db::prepStmt($sql, 'iiss', [$answerSession->originId, $answerSession->user->id, $answerSession->ipAddress, $answerSession->date]);
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
                           q.markup as question_markup,
                           qn.id as questionnaire_id,
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

        public static function getAll() {
            $sql = 'SELECT q.id as question_id,
                           q.number as question_number,
                           q.text as question_text,
                           q.markup as question_markup,
                           qn.id as questionnaire_id,
                           qt.id as question_type_id,
                           qt.code as question_type_code,
                           qo.id as question_option_id,
                           qo.text as question_option_text
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
                           q.text as question_text,
                           q.markup as question_markup,
                           qn.id as questionnaire_id,
                           qt.id as question_type_id,
                           qt.code as question_type_code,
                           qo.id as question_option_id,
                           qo.text as question_option_text
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

        public static function getAllForAnswerSession($answerSessionId) {
            $sql = 'SELECT q.id as question_id,
                           q.number as question_number,
                           q.text as question_text,
                           q.markup as question_markup,
                           qn.id as questionnaire_id,
                           qt.id as question_type_id,
                           qt.code as question_type_code,
                           qo.id as question_option_id,
                           qo.text as question_option_text
                      FROM answer_sessions ast
                      JOIN questionnaires qn on qn.id = ast.questionnaire_id
                      JOIN questions q on q.questionnaire_id
                 LEFT JOIN question_types qt on qt.id = q.question_type_id
                 LEFT JOIN question_options qo on qo.question_id = q.id
                     WHERE ast.id = ?
                  ORDER BY q.position, q.id, qo.position, qo.id ASC';
            $queryResult = Db::prepQuery($sql, 'i', [$answerSessionId]);
            $questions = self::fetchQuestions($queryResult);
            return $questions;
        }

        public static function getForAnswerSession($answerSessionId, $secret) {
            $sql = 'SELECT q.id as question_id,
                           q.number as question_number,
                           q.text as question_text,
                           q.markup as question_markup,
                           qn.id as questionnaire_id,
                           qt.id as question_type_id,
                           qt.code as question_type_code,
                           qo.id as question_option_id,
                           qo.text as question_option_text
                      FROM answer_sessions ast
                      JOIN questionnaires qn on qn.id = ast.questionnaire_id
                      JOIN questions q on q.questionnaire_id
                 LEFT JOIN question_types qt on qt.id = q.question_type_id
                 LEFT JOIN question_options qo on qo.question_id = q.id
                     WHERE q.secret = ?
                       AND ast.id = ?
                  ORDER BY q.position, q.id, qo.position, qo.id ASC';
            $queryResult = Db::prepQuery($sql, 'ii', [intval($secret), $answerSessionId]);
            $questions = self::fetchQuestions($queryResult);
            return $questions;
        }

        public static function get($id) {
            $sql = 'SELECT q.id as question_id,
                           q.number as question_number,
                           q.text as question_text,
                           q.markup as question_markup,
                           qn.id as questionnaire_id,
                           qt.id as question_type_id,
                           qt.code as question_type_code,
                           qo.id as question_option_id,
                           qo.text as question_option_text
                      FROM questions q
                 LEFT JOIN questionnaires qn on qn.id = q.questionnaire_id
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
                    $question->questionnaireId = $queryRow['questionnaire_id'];
                    $question->number = $queryRow['question_number'];
                    $question->text = $queryRow['question_text'];
                    $question->markup = $queryRow['question_markup'];

                    // If the QuestionType is not already in the Map, creating and putting
                    if (isset($questionTypes[$queryRow['question_type_id']])) {
                        $questionType = $questionTypes[$queryRow['question_type_id']];
                    } else {
                        $questionType = new QuestionType();

                        $questionType->id = $queryRow['question_type_id'];
                        $questionType->code = $queryRow['question_type_code'];

                        $questionTypes[$questionType->id] = $questionType;
                    }

                    $question->type = $questionType;

                    $questions[$question->id]= $question;
                }

                // If the Question has QuestionOptions, creating and setting
                if ($queryRow['question_option_id'] != NULL) {
                    $questionOption = new QuestionOption();

                    $questionOption->id = $queryRow['question_option_id'];
                    $questionOption->text = $queryRow['question_option_text'];

                    $question->options[$questionOption->id] = $questionOption;
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

        public static function getAll() {
            $sql = 'SELECT * FROM questionnaires WHERE is_active ORDER BY id';
            $queryResult = Db::query($sql);
            $questionnaires = self::fetchAll($queryResult);
            return $questionnaires;
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
                           qo.position as position,
                           qo.text as text
                      FROM question_options qo
                  ORDER BY qo.id';
            $queryResult = Db::query($sql);
            $options = self::fetchAll($queryResult);
            return $options;
        }

        public static function get($id) {
            $sql = 'SELECT qo.id as id,
                           qo.question_id as question_id,
                           qo.position as position,
                           qo.text as text
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
                $option->text = $queryRow['text'];
                $options[$option->id] = $option;
            }
            return $options;
        }
    }
?>
