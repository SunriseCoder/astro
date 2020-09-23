<?php
    if (!class_exists('Question')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/questions.php'; }
    if (!class_exists('ParticipantAnswer')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/answers.php'; }
    if (!class_exists('AnswerEvaluationMethodEntity')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/evaluation.php'; }
    if (!class_exists('Tr')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/i18n.php'; }

    interface AnswerEvaluationMethod {
        public function getName();
        public function evaluateAnswer(Question $question, ParticipantAnswer $participantAnswer, AstrologerAnswer $astrologerAnswer);
    }

    class LinearRangeEvaluationMethod implements AnswerEvaluationMethod {
        private $name;
        private $rangeScores;
        private $ignoreNotApplicable;

        function __construct($name, $rangeScores, $ignoreNotApplicable) {
            $this->name = $name;
            $this->rangeScores = $rangeScores;
            $this->ignoreNotApplicable = $ignoreNotApplicable;
        }

        public function getName() {
            return $this->name;
        }

        public function evaluateAnswer(Question $question, ParticipantAnswer $participantAnswer, AstrologerAnswer $astrologerAnswer) {
            // If Participant answered as "Not applicable" and $this->ignoreNotApplicable is set, skipping the question
            if ($this->ignoreNotApplicable && $question->options[$participantAnswer->questionOptionId]->isNotApplicable) {
                return FALSE;
            }

            $answerRange = $this->calculateAnswerRange($question, $participantAnswer, $astrologerAnswer);
            $evaluationWidth = $this->calculateEvaluationWidth($question);
            $evaluationRange = $this->rangeScores[$evaluationWidth];
            $result = empty($evaluationRange[$answerRange]) ? 0 : $evaluationRange[$answerRange];
            return $result;
        }

        /**
         * Calculating the distance between 2 AnswerOptions (less the range, more scores the Astrologer will get)
         */
        private function calculateAnswerRange(Question $question, ParticipantAnswer $participantAnswer, AstrologerAnswer $astrologerAnswer) {
            $questionOptionsMap = [];
            $counter = 0;
            foreach ($question->options as $option) {
                if ($this->ignoreNotApplicable) {
                    if (!$option->isNotApplicable) {
                        $questionOptionsMap[$option->id] = [$option->id, $counter++];
                    }
                } else {
                    $questionOptionsMap[$option->id] = [$option->id, $counter++];
                }
            }

            if (empty($questionOptionsMap[$participantAnswer->questionOptionId]) || empty($questionOptionsMap[$astrologerAnswer->questionOptionId])) {
                return 0;
            }

            $participantAnswerPosition = $questionOptionsMap[$participantAnswer->questionOptionId][1];
            $astrologerAnswerPosition = $questionOptionsMap[$astrologerAnswer->questionOptionId][1];

            $result = abs($participantAnswerPosition - $astrologerAnswerPosition);

            return $result;
        }

        /**
         * Calculation number of QuestionOptions for the Evaluation (ignoring "Not Applicable" Options if $this->ignoreNotApplicable is set)
         */
        private function calculateEvaluationWidth(Question $question) {
            $evaluationWidth = 0;
            foreach ($question->options as $option) {
                if ($this->ignoreNotApplicable) {
                    if (!$option->isNotApplicable) {
                        $evaluationWidth++;
                    }
                } else {
                    $evaluationWidth++;
                }
            }

            if (empty($this->rangeScores[$evaluationWidth])) {
                throw new Exception('Range Score for '.$evaluationWidth.' Option(s) was not set');
            }

            return $evaluationWidth;
        }
    }

    class AnswerEvaluator {
        private $evaluationMethod;
        private $totalScore;
        private $answersCount;

        function __construct(AnswerEvaluationMethod $evaluationMethod) {
            $this->evaluationMethod = $evaluationMethod;
        }

        public function reset() {
            $this->totalScore = 0;
            $this->answersCount = 0;
        }

        public function getName() {
            return $this->evaluationMethod->getName();
        }

        public function evaluateAnswer(Question $question, ParticipantAnswer $participantAnswer, AstrologerAnswer $astrologerAnswer) {
            $result = $this->evaluationMethod->evaluateAnswer($question, $participantAnswer, $astrologerAnswer);

            if ($result !== FALSE) {
                $this->totalScore += $result;
                $this->answersCount++;
            }

            return $result;
        }

        public function getAverageScore() {
            $result = $this->answersCount == 0 ? 0 : round($this->totalScore / $this->answersCount);
            return $result;
        }
    }

    class AnswerEvaluationService {
        private static $evaluators = [];
        private static $evaluatorsMap = [];

        private static function initIfNeeded() {
            if (count(self::$evaluators) > 0) {
                return;
            }

            // Definition of Evaluators
            self::$evaluators []= new AnswerEvaluator(
                new LinearRangeEvaluationMethod('Flex-Soft', [2 => [100], 3 => [100, 30], 4 => [100, 50], 5 => [100, 60, 30], 6 => [100, 60, 30]], TRUE));

            // Evaluators map by code
            foreach (self::$evaluators as $evaluator) {
                self::$evaluatorsMap[$evaluator->code] = $evaluator;
            }
        }

        public static function getAllEvaluators() {
            self::initIfNeeded();
            return self::$evaluators;
        }

        public static function getEvaluatorByCode($code) : AnswerEvaluator {
            if (isset(self::$evaluatorsMap[$code])) {
                return self::$evaluatorsMap[$code];
            }
        }

        public static function evaluateAllMissing() {
            $evaluationMethodEntities = AnswerEvaluationMethodDao::getAll();
            $astrologerAnswerGroups = AstrologerAnswerGroupDao::getAll();

            $evaluationGroupEntities = AnswerEvaluationGroupDao::getAll();
            $evaluationGroupEntitiesMap = [];
            foreach ($evaluationGroupEntities as $evaluationGroupEntity) {
                $evaluationGroupEntitiesMap[$evaluationGroupEntity->methodId][$evaluationGroupEntity->astrologerAnswerGroupId] = $evaluationGroupEntity;
            }

            foreach ($astrologerAnswerGroups as $astrologerAnswerGroup) {
                foreach ($evaluationMethodEntities as $evaluationMethodEntity) {
                    if (empty($evaluationGroupEntitiesMap[$evaluationMethodEntity->id][$astrologerAnswerGroup->id])) {
                        $result = self::evaluateAnswerGroup($astrologerAnswerGroup->id, $evaluationMethodEntity);
                        if ($result) {
                            return $result;
                        }
                    }
                }
            }
        }

        private static function evaluateAnswerGroup($astrologerAnswerGroupId, AnswerEvaluationMethodEntity $evaluationMethodEntity) {
            $evaluator = self::getEvaluatorByCode($evaluationMethodEntity->code);
            if (empty($evaluator)) {
                return Tr::format('evaluationService.error.evaluatorByCodeNotFound', [$evaluationMethodEntity->code],
                    'Error: Astrologer answers evaluator was not found by code "{0}"');
            }
            $evaluator->reset();

            $astrologerAnswerGroup = AstrologerAnswerGroupDao::getWithAllAnswers($astrologerAnswerGroupId);
            $participantAnswerGroup = ParticipantAnswerGroupDao::getWithAllAnswers($astrologerAnswerGroup->participantAnswerGroupId);
            $questions = QuestionDao::getAllForQuestionnaire($participantAnswerGroup->questionnaireId);

            Db::beginTransaction();

            // Insert Evaluation Group
            $evaluationGroup = new AnswerEvaluationGroupEntity();
            $evaluationGroup->method = $evaluationMethodEntity;
            $evaluationGroup->astrologerAnswerGroup = $astrologerAnswerGroup;
            $evaluationGroup->score = 0;
            AnswerEvaluationGroupDao::insert($evaluationGroup);
            $evaluationGroup->id = Db::insertedId();

            // TODO Insert Evaluation Details
            foreach ($questions as $question) {
                $score = $evaluator->evaluateAnswer($question, $participantAnswer, $astrologerAnswer);
                if ($score !== FALSE) {
                    $evaluationDetails = new AnswerEvaluationDetailsEntity();
                    $evaluationDetails->evaluationGroup = $evaluationGroup;
                    $evaluationDetails->score = $score;
                    AnswerEvaluationDetailsDao::insert($evaluationDetails);
                }
            }

            // TODO Update Evaluation Group Score
            $evaluationGroup->score = $evaluator->getAverageScore();
            AnswerEvaluationGroupDao::update($evaluationGroup);
            Db::commit();
        }
    }
?>

