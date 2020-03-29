<?php
    if (!class_exists('Db')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/db.php'; }
    if (!class_exists('Tr')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/i18n.php'; }
    if (!class_exists('Question')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/questions.php'; }

    class ParticipantAnswer {
        public $id;
        public $groupId;
        public $group;
        public $questionId;
        public $question;
        public $questionOptionId;
        public $questionOption;
        public $value;
    }

    class AstrologerAnswer {
        public $id;
        public $groupId;
        public $group;
        public $participantAnswerId;
        public $participantAnswer;
        public $questionId;
        public $question;
        public $questionOptionId;
        public $questionOption;
        public $value;
    }

    class ParticipantAnswerGroup {
        public $id;
        public $userId;
        public $user;
        public $questionnaireId;
        public $questionnaire;
        public $ipAddress;
        public $date;
        public $answers = [];
        public $answersCount;
        public $astrologyAnswerGroupCount;
    }

    class AstrologerAnswerGroup {
        public $id;
        public $participantAnswerGroupId;
        public $participantAnswerGroup;
        public $userId;
        public $user;
        public $questionnaireId;
        public $questionnaire;
        public $ipAddress;
        public $date;
        public $answers = [];
        public $answersCount;
    }

    class ParticipantAnswerDao {
        public static function getAllNonSecret() {
            $sql = 'SELECT a.*
                      FROM participant_answers a
                      JOIN questions q on q.id = a.question_id
                     WHERE NOT q.secret';
            $queryResult = Db::query($sql);
            $answers = self::fetchAll($queryResult);
            return $answers;
        }

        public static function getAllByGroupId($groupId) {
            $sql = 'SELECT a.*
                      FROM participant_answers a
                      JOIN questions q on q.id = a.question_id
                     WHERE a.group_id = ?';
            $queryResult = Db::prepQuery($sql, 'i', [$groupId]);
            $answers = self::fetchAll($queryResult);
            return $answers;
        }

        public static function getNonSecretByGroupId($groupId) {
            $sql = 'SELECT a.*
                      FROM participant_answers a
                      JOIN questions q on q.id = a.question_id
                     WHERE NOT q.secret
                       AND a.group_id = ?';
            $queryResult = Db::prepQuery($sql, 'i', [$groupId]);
            $answers = self::fetchAll($queryResult);
            return $answers;
        }

        public static function fetchAll($queryResult) {
            $answers = [];
            foreach ($queryResult as $queryRow) {
                $answer = new ParticipantAnswer();
                $answer->id = $queryRow['id'];
                $answer->groupId = $queryRow['group_id'];
                $answer->questionId = $queryRow['question_id'];
                $answer->questionOptionId = $queryRow['question_option_id'];
                $answer->value = $queryRow['value'];

                $answers[$answer->id] = $answer;
            }
            return $answers;
        }

        public static function insert(ParticipantAnswer $answer) {
            $sql = 'INSERT INTO participant_answers (group_id, question_id, question_option_id, value) VALUES (?, ?, ?, ?)';
            $questionOptionId = $answer->questionOption ? $answer->questionOption->id : NULL;
            Db::prepStmt($sql, 'iiis', [$answer->group->id, $answer->question->id, $questionOptionId, $answer->value]);
            $answerId = Db::insertedId();
            return $answerId;
        }
    }

    class AstrologerAnswerDao {
        public static function getAllByGroupId($groupId) {
            $sql = 'SELECT a.*
                      FROM astrologer_answers a
                      JOIN questions q on q.id = a.question_id
                     WHERE a.group_id = ?';
            $queryResult = Db::prepQuery($sql, 'i', [$groupId]);
            $answers = self::fetchAll($queryResult);
            return $answers;
        }

        public static function fetchAll($queryResult) {
            $answers = [];
            foreach ($queryResult as $queryRow) {
                $answer = new AstrologerAnswer();
                $answer->id = $queryRow['id'];
                $answer->groupId = $queryRow['group_id'];
                $answer->questionId = $queryRow['question_id'];
                $answer->questionOptionId = $queryRow['question_option_id'];
                $answer->value = $queryRow['value'];

                $answers[$answer->id] = $answer;
            }
            return $answers;
        }

        public static function insert(AstrologerAnswer $answer) {
            $sql = 'INSERT INTO astrologer_answers (group_id, question_id, question_option_id, value) VALUES (?, ?, ?, ?)';
            $questionOptionId = $answer->questionOption ? $answer->questionOption->id : NULL;
            Db::prepStmt($sql, 'iiis', [$answer->group->id, $answer->question->id, $questionOptionId, $answer->value]);
            $answerId = Db::insertedId();
            return $answerId;
        }
    }

    class ParticipantAnswerGroupDao {
        public static function hasCurrentUserAlreadyAnswered() {
            $sql = 'SELECT count(1) AS c FROM participant_answer_groups WHERE user_id = ?';
            $user = LoginDao::getCurrentUser();
            if (!$user) {
                return FALSE;
            }

            $queryResult = Db::prepQuery($sql, 'i', [$user->id]);
            $result = $queryResult[0]['c'] > 0;
            return $result;
        }

        public static function getAllWithNonSecretAnswers() {
            $sql = 'SELECT pag.*,
                           COALESCE(aac.astrologer_answer_count, 0) as astrologer_answer_count
                      FROM participant_answer_groups pag
                 LEFT JOIN (SELECT participant_answer_group_id as id,
                                   count(1) as astrologer_answer_count
                              FROM astrologer_answer_groups
                          GROUP BY participant_answer_group_id) aac on aac.id = pag.id
                  ORDER BY pag.id';
            $groupsResult = Db::query($sql);
            $answerGroups = self::fetchAll($groupsResult);

            $answers = ParticipantAnswerDao::getAllNonSecret();
            foreach ($answers as $answer) {
                if (isset($answerGroups[$answer->groupId])) {
                    $answerGroups[$answer->groupId]->answers[$answer->id] = $answer;
                }
            }

            return $answerGroups;
        }

        public static function get($id) {
            $sql = 'SELECT pag.*,
                           (SELECT COUNT(1)
                              FROM astrologer_answer_groups
                             WHERE participant_answer_group_id = pag.id) as astrologer_answer_count
                      FROM participant_answer_groups pag
                     WHERE pag.id = ?';
            $queryResult = Db::prepQuery($sql, 'i', [$id]);
            $result = self::fetchAll($queryResult);
            return $result;
        }

        public static function getWithAllAnswers($id) {
            $answerGroups = self::get($id);
            $answers = ParticipantAnswerDao::getAllByGroupId($id);
            self::bindAnswers($answerGroups, $answers);
            return $answerGroups;
        }

        public static function getWithNonSecretAnswers($id) {
            $answerGroups = self::get($id);
            $answers = ParticipantAnswerDao::getNonSecretByGroupId($id);
            self::bindAnswers($answerGroups, $answers);
            return $answerGroups;
        }

        public static function fetchAll($queryResult) {
            $groups = [];
            foreach ($queryResult as $queryRow) {
                $group = new ParticipantAnswerGroup();
                $group->id = $queryRow['id'];
                $group->userId = $queryRow['user_id'];
                $group->questionnaireId = $queryRow['questionnaire_id'];
                $group->ipAddress = $queryRow['ip_address'];
                $group->date = DateTimeUtils::fromDatabase($queryRow['date']);
                if (!empty($queryRow['astrologer_answer_count'])) {
                    $group->astrologyAnswerGroupCount = $queryRow['astrologer_answer_count'];
                }

                $groups[$group->id] = $group;
            }

            return $groups;
        }

        public static function bindAnswers($answerGroups, $answers) {
            foreach ($answers as $answer) {
                if (!empty($answerGroups[$answer->groupId])) {
                    $answerGroups[$answer->groupId]->answers[$answer->id] = $answer;
                }
            }
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

                // Save AnswerGroup
                $answerGroup = new ParticipantAnswerGroup();
                $answerGroup->user = LoginDao::getCurrentUser();
                $answerGroup->ipAddress = LoginDao::clientIP();
                $answerGroup->date = DateTimeUtils::toDatabase(DateTimeUtils::now());
                $answerGroup->id = self::insert($answerGroup);

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
                        $answer = new ParticipantAnswer();
                        $answer->group = $answerGroup;

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

                        ParticipantAnswerDao::insert($answer);
                    }

                    // Complex Questions like: 'answer-15-3-q1'
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
                            $answer = new ParticipantAnswer();
                            $answer->question = $question;
                            $answer->group = $answerGroup;
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
                    ParticipantAnswerDao::insert($complexAnswer);
                }

                Db::commit();
            } catch (Exception $e) {
                Db::rollback();
                throw $e;
            }
        }

        private static function insert(ParticipantAnswerGroup $group) {
            $sql = 'INSERT INTO participant_answer_groups (user_id, questionnaire_id, ip_address, date)
                         VALUES (?, (SELECT value FROM settings WHERE code = \'DEFAULT_QUESTIONNAIRE\'), ?, ?)';
            Db::prepStmt($sql, 'iss', [$group->user->id, $group->ipAddress, $group->date]);
            $groupId = Db::insertedId();
            return $groupId;
        }

        public static function delete($id) {
            $group = self::get($id);
            if (empty($group)) {
                return Tr::format('error.participantAnswers.answerGroupNotFound', [$id], 'ParticipantAnswerGroup with ID: {0} was not found');
            }

            $hasAstrologerAnswerGroups = AstrologerAnswerGroupDAO::hasAstrologerAnswerGroupsForParticipantAnswerGroup($id);
            if ($hasAstrologerAnswerGroups) {
                return Tr::format('error.participantAnswers.delete.hasAstrologerAnswers', [$id],
                    'ParticipantAnswerGroup with ID: {0} cannot be deleted, because some Astrologers have given their answers for it');
            }

            try {
                Db::autocommit(FALSE);
                Db::beginTransaction(0, 'DeleteParticipantAnswerGroup');

                $sql = 'DELETE FROM participant_answers WHERE group_id = ?';
                Db::prepStmt($sql, 'i', [$id]);

                $sql = 'DELETE FROM participant_answer_groups WHERE id = ?';
                Db::prepStmt($sql, 'i', [$id]);

                Db::commit();
            } catch (Exception $e) {
                $exceptionMessage = $e->getMessage();
                Logger::error($exceptionMessage);
                Logger::showTrace($e->getTraceAsString());
                Db::rollback();
                return Tr::format('error.participantAnswers.delete.sqlException', [$id, $exceptionMessage],
                    'Database error due to delete ParticipantAnswerGroup with ID: {0} is: {1}');
            }
        }
    }

    class AstrologerAnswerGroupDao {
        public static function getAnsweredIdsForCurrentUser() {
            $currentUser = LoginDao::getCurrentUser();
            $sql = 'SELECT participant_answer_group_id FROM astrologer_answer_groups WHERE user_id = ?';
            $queryResult = Db::prepQuery($sql, 'i', [$currentUser->id]);
            $result = [];
            foreach ($queryResult as $queryRow) {
                $result[$queryRow['participant_answer_group_id']] = TRUE;
            }
            return $result;
        }

        public static function hasAstrologerAnswerGroupsForParticipantAnswerGroup($participantAnswerGroupId) {
            $sql = 'SELECT COUNT(1) AS c FROM astrologer_answer_groups WHERE participant_answer_group_id = ?';
            $queryResult = Db::prepQuery($sql, 'i', [$participantAnswerGroupId]);
            $result = $queryResult[0]['c'] > 0;
            return $result;
        }

        public static function hasCurrentAstrologerAnsweredAlready($participantAnswerGroupId) {
            $currentUser = LoginDao::getCurrentUser();
            $sql = 'SELECT COUNT(1) AS c FROM astrologer_answer_groups WHERE participant_answer_group_id = ? AND user_id = ?';
            $queryResult = Db::prepQuery($sql, 'ii', [$participantAnswerGroupId, $currentUser->id]);
            $result = $queryResult[0]['c'] > 0;
            return $result;
        }

        public static function get($id) {
            $sql = 'SELECT * FROM astrologer_answer_groups WHERE id = ?';
            $queryResult = Db::prepQuery($sql, 'i', [$id]);
            $result = self::fetchAll($queryResult);
            return $result;
        }

        public static function getWithAllAnswers($id) {
            $answerGroups = self::get($id);
            $answers = AstrologerAnswerDao::getAllByGroupId($id);
            self::bindAnswers($answerGroups, $answers);
            return $answerGroups;
        }

        public static function getWithAllAnswersAndParticipantAnswers($id) {
            $answerGroups = self::getWithAllAnswers($id);

            if (count($answerGroups[$id]) > 0) {
                $astrologerAnswerGroup = $answerGroups[0];
                $astrologerAnswerGroup->participantAnswerGroup = ParticipantAnswerGroupDAO::getWithAllAnswers($astrologerAnswerGroup->participantAnswerGroupId);
            }

            return $answerGroups;
        }

        public static function fetchAll($queryResult) {
            $groups = [];
            foreach ($queryResult as $queryRow) {
                $group = new AstrologerAnswerGroup();
                $group->id = $queryRow['id'];
                if (!empty($queryRow['participant_answer_group_id'])) {
                    $group->participantAnswerGroupId = $queryRow['participant_answer_group_id'];
                }
                $group->userId = $queryRow['user_id'];
                $group->questionnaireId = $queryRow['questionnaire_id'];
                $group->ipAddress = $queryRow['ip_address'];
                $group->date = DateTimeUtils::fromDatabase($queryRow['date']);

                $groups[$group->id] = $group;
            }

            return $groups;
        }

        public static function bindAnswers($answerGroups, $answers) {
            foreach ($answers as $answer) {
                if (!empty($answerGroups[$answer->groupId])) {
                    $answerGroups[$answer->groupId]->answers[$answer->id] = $answer;
                }
            }
        }

        /**
         * Saves Astrologer Answers to the Database
         *
         * @return NULL on success or Array of Errors (strings)
         */
        public static function saveAnswers() {
            // Checking is it a Paricipant's answers or an Astrologer's if originId is set
            if (empty($_POST['id'])) {
                return Tr::tr('error.astrologerAnswers.participantAnswersGroupIdEmpty', 'ParticipantAnswerGroupID is empty');
            }

            $participantAnswerGroupId = $_POST['id'];
            $participantAnswerGroups = ParticipantAnswerGroupDao::get($participantAnswerGroupId);
            // Checking that ParticipantAnswerGroup does exists
            if (count($participantAnswerGroups) == 0) {
                return Tr::format('error.astrologerAnswers.participantAnswersGroupNotFound', [$participantAnswerGroupId],
                    'ParticipantAnswerGroup with ID: {0} was not found');
            }

            // Checking that the Astrologer doesn't trying to solve his own answers
            $currentUser = LoginDao::getCurrentUser();
            $participantAnswerGroup = $participantAnswerGroups[$participantAnswerGroupId];
            if ($participantAnswerGroup->userId == $currentUser->id) {
                return Tr::trs('error.astrologer.guessOwnAnswers', 'You are trying to solve your own answers');
            }

            // Checking that the Astrologer didn't already solve this Answer Set
            $alreadyAnswered = self::hasCurrentAstrologerAnsweredAlready($participantAnswerGroupId);
            if ($alreadyAnswered) {
                return Tr::format('error.astrologerAnswers.astrologerAlreadyAnswered', [$participantAnswerGroupId],
                    'You have already answered Participant Answers Group ID: {0}');
            }

            try {
                Db::autocommit(FALSE);
                Db::beginTransaction(0, 'SaveAnswers');

                // Save AnswerGroup
                $answerGroup = new AstrologerAnswerGroup();
                $answerGroup->participantAnswerGroupId = $participantAnswerGroupId;
                $answerGroup->user = LoginDao::getCurrentUser();
                $answerGroup->ipAddress = LoginDao::clientIP();
                $answerGroup->date = DateTimeUtils::toDatabase(DateTimeUtils::now());
                $answerGroup->id = self::insert($answerGroup);

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
                        $answer = new AstrologerAnswer();
                        $answer->group = $answerGroup;

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

                        AstrologerAnswerDao::insert($answer);
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
                            $answer = new AstrologerAnswer();
                            $answer->question = $question;
                            $answer->group = $answerGroup;
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
                    AstrologerAnswerDao::insert($complexAnswer);
                }

                Db::commit();
            } catch (Exception $e) {
                Db::rollback();
                throw $e;
            }
        }

        private static function insert(AstrologerAnswerGroup $group) {
            $sql = 'INSERT INTO astrologer_answer_groups (participant_answer_group_id, user_id, questionnaire_id, ip_address, date)
                         VALUES (?, ?, (SELECT value FROM settings WHERE code = \'DEFAULT_QUESTIONNAIRE\'), ?, ?)';
            Db::prepStmt($sql, 'iiss', [$group->participantAnswerGroupId, $group->user->id, $group->ipAddress, $group->date]);
            $groupId = Db::insertedId();
            return $groupId;
        }

        public static function delete($id) {
            $group = self::get($id);
            if (empty($group)) {
                return Tr::format('error.astrologerAnswers.answerGroupNotFound', [$id], 'AstrologerAnswerGroup with ID: {0} was not found');
            }

            try {
                Db::autocommit(FALSE);
                Db::beginTransaction(0, 'DeleteAstrologerAnswerGroup');

                $sql = 'DELETE FROM astrologer_answers WHERE group_id = ?';
                Db::prepStmt($sql, 'i', [$id]);

                $sql = 'DELETE FROM astrologer_answer_groups WHERE id = ?';
                Db::prepStmt($sql, 'i', [$id]);

                Db::commit();
            } catch (Exception $e) {
                $exceptionMessage = $e->getMessage();
                Logger::error($exceptionMessage);
                Logger::showTrace($e->getTraceAsString());
                Db::rollback();
                return Tr::format('error.astrologerAnswers.delete.sqlException', [$id, $exceptionMessage],
                    'Database error due to delete AstrologerAnswerGroup with ID: {0} is: {1}');
            }
        }
    }
?>
