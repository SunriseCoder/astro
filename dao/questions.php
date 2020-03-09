<?php
    if (!class_exists('Db')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/db.php'; }
    if (!class_exists('Tr')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/i18n.php'; }

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
        public $descendantsCount;
    }

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

        public static function hasCurrentUserAlreadyAnswered() {
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
            $sql = 'SELECT ast.*,
                           (SELECT COUNT(1)
                              FROM answer_sessions
                             WHERE origin_id = ast.id) as descendants_count
                      FROM answer_sessions ast
                     WHERE ast.id = ?';
            $sessionsResult = Db::prepQuery($sql, 'i', [$id]);
            $answerSessions = self::fetchSessions($sessionsResult);
            $result = isset($answerSessions[$id]) ? $answerSessions[$id] : NULL;
            return $result;
        }

        public static function getAllOriginsWithNonSecretAnswers() {
            $sql = 'SELECT ast.*,
                           COALESCE(d.descendants_count, 0) as descendants_count
                      FROM answer_sessions ast
                 LEFT JOIN (SELECT origin_id as id,
                                   count(1) as descendants_count
                              FROM answer_sessions
                             WHERE origin_id IS NOT NULL
                          GROUP BY origin_id) d on d.id = ast.id
                     WHERE ast.origin_id IS NULL
                  ORDER BY ast.id';
            $sessionsResult = Db::query($sql);
            $answerSessions = self::fetchSessions($sessionsResult);

            $answers = AnswerDao::getAllNonSecret();
            foreach ($answers as $answer) {
                if (isset($answerSessions[$answer->sessionId])) {
                    $answerSessions[$answer->sessionId]->answers[$answer->id] = $answer;
                }
            }

            return $answerSessions;
        }

        public static function getWithAllAnswers($answerSessionId) {
            // Loading Answer Session itself
            $sql = 'SELECT ast.*,
                           (SELECT COUNT(1)
                              FROM answer_sessions
                             WHERE origin_id = ast.id) as descendants_count
                      FROM answer_sessions ast
                     WHERE ast.id = ?';
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
            $sql = 'SELECT ast.*,
                           (SELECT COUNT(1)
                              FROM answer_sessions
                             WHERE origin_id = ast.id) as descendants_count
                      FROM answer_sessions ast
                     WHERE ast.id = ?';
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
                $session->descendantsCount = $queryRow['descendants_count'];

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
                    return Tr::format('error.answerSession.originNotFound', [$originId], 'Original AnswerSession with ID: {0} was not found');
                }

                // Checking that the Astrologer trying to guess a Participant's answers, not the Astrologer's answers
                if (isset($answerSession->originId)) {
                    return Tr::format('error.answerSession.originIsNotOrigin', [$originId],
                        'Original AnswerSession with ID: {0} is not Original, but an Astrologer\'s answers');
                }

                // Checking that the Astrologer doesn't trying to solve his own answers
                $currentUser = LoginDao::getCurrentUser();
                if ($answerSession->userId == $currentUser->id) {
                    return Tr::trs('error.astrologer.guessOwnAnswers', 'You are trying to solve your own answers');
                }

                // Checking that the Astrologer didn't already solve this Answer Set
                $alreadyAnswered = self::hasCurrentAstrologerAnsweredAlready($originId);
                if ($alreadyAnswered) {
                    return Tr::format('error.answerSession.astrologerAlreadyGuessed', [$originId], 'You have already solved Answer Session with ID: {0}');
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
                        Logger::warning(Tr::format('warning.answer.skipEmptyValue', [$key], 'Skipping Answer "{0}" due to empty Value'));
                        continue;
                    }

                    // Basic Questions like: 'answer-15'
                    if (preg_match('/^answer-([0-9]+)$/', $key, $matches)) {
                        $answer = new Answer();
                        $answer->session = $answerSession;

                        $questionId = $matches[1];
                        $question = $questions[$questionId];
                        if (!$question) {
                            Logger::warning(Tr::format('warning.answer.questionNotFound', [$key, $value, $questionId],
                                'Skipping Answer "{0}" -> "{1}" due to Question with ID {2} was not found'));
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
                                    Logger::warning(Tr::format('warning.answer.questionOptionNotFound', [$key, $value, $questionOptionId],
                                        'Skipping Answer "{0}" -> "{1}" due to QuestionOption with ID {2} was not found'));
                                    continue;
                                }

                                if ($questionOption->questionId != $question->id) {
                                    Logger::warning(Tr::format('warning.answer.questionOptionWrongQuestion', [$key, $value, $questionOptionId, $questionId],
                                        'Skipping Answer "{0}" -> "{1}" due to QuestionOption with ID {2} does not belongs to the Question with ID {3}'));
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
                                Logger::warning(Tr::format('warning.answer.unknownQuestionType', [$key, $value, $question->type->code],
                                    'Skipping Answer "{0}" -> "{1}" due to unknown QuestionType "{2}"'));
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
                            Logger::warning(Tr::format('warning.answer.questionNotFound', [$key, $value, $questionId],
                                'Skipping Answer "{0}" -> "{1}" due to Question with ID {2} was not found'));
                            continue;
                        }

                        if ($question->type->code != QuestionType::Complex) {
                            Logger::warning(Tr::format('warning.answer.questionTypeIsNotComplex', [$key, $value, $questionId],
                                'Skipping Answer "{0}" -> "{1}" due to the Type of Question with ID {2} is not "Complex"'));
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
            $session = self::get($id);
            if (empty($session)) {
                return Tr::format('error.answerSession.notFound', [$id], 'AnswerSession with ID: {0} was not found');
            }
            if ($session->descendantsCount > 0) {
                return Tr::format('error.answerSession.delete.hasDescendants', [$id, $session->descendantsCount],
                    'AnswerSession with ID: {0} cannot be deleted, because {1} Astrologer(s) made their calculations on it');
            }

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
                           q.position as question_position,
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

        public static function getAllForAnswerSession($answerSessionId) {
            $sql = 'SELECT q.id as question_id,
                           q.number as question_number,
                           q.position as question_position,
                           qn.id as questionnaire_id,
                           qt.id as question_type_id,
                           qt.code as question_type_code,
                           qt.name as question_type_name,
                           qo.id as question_option_id
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
                           q.position as question_position,
                           qn.id as questionnaire_id,
                           qt.id as question_type_id,
                           qt.code as question_type_code,
                           qt.name as question_type_name,
                           qo.id as question_option_id
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

        public static function getById($id) {
            $sql = 'SELECT q.id as question_id,
                           q.number as question_number,
                           q.position as question_position,
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
