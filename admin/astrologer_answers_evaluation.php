<?php
    if (!class_exists('LoginDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php'; }
    LoginDao::checkPermissionsAndRedirect(Permission::AnswersView, './');

    if (!isset($_GET['id']) || !preg_match('/^[0-9]+$/', $_GET['id'])) {
        Utils::redirect('/admin/astrologer_answers_list.php');
    }

    if (!class_exists('QuestionDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/questions.php'; }
    if (!class_exists('QuestionRender')) { include $_SERVER["DOCUMENT_ROOT"].'/render/questions.php'; }
    if (!class_exists('ParticipantAnswerGroupDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/answers.php'; }
    if (!class_exists('AnswerEvaluationService')) { include $_SERVER["DOCUMENT_ROOT"].'/services/evaluation.php'; }
    if (!class_exists('HTMLRender')) { include $_SERVER["DOCUMENT_ROOT"].'/render/html.php'; }

    $browser_title = 'Chaitanya Academy - Astrologer Answers';
    $page_title = 'Astrologer Answers Evaluation';
    $css_includes = ['/css/questions.css'];
    $body_content = '';

    // Astrologer Answer Group
    $astrologerAnswerGroupId = $_GET['id'];
    $astrologerAnswerGroups = AstrologerAnswerGroupDao::getWithAllAnswers($astrologerAnswerGroupId);
    if (empty($astrologerAnswerGroups[$astrologerAnswerGroupId])) {
        $body_content .= 'Astrologer Answer Group with ID: '.$astrologerAnswerGroupId.' was not found';
    } else {
        $astrologerAnswerGroup = $astrologerAnswerGroups[$astrologerAnswerGroupId];

        // Participant Answer Group
        $participantAnswerGroupId = $astrologerAnswerGroup->participantAnswerGroupId;
        $participantAnswerGroups = ParticipantAnswerGroupDao::getWithAllAnswers($participantAnswerGroupId);
        if (empty($participantAnswerGroups[$participantAnswerGroupId])) {
            $body_content .= 'Participant Answer Group with ID: '.$participantAnswerGroupId.' was not found';
        } else {
            $participantAnswerGroup = $participantAnswerGroups[$participantAnswerGroupId];

            // Prepare Answer Maps
            $participantAnswersByQuestionIdMap = [];
            foreach ($participantAnswerGroup->answers as $answer) {
                $participantAnswersByQuestionIdMap[$answer->questionId] = $answer;
            }
            $astrologerAnswersByQuestionIdMap = [];
            foreach ($astrologerAnswerGroup->answers as $answer) {
                $astrologerAnswersByQuestionIdMap[$answer->questionId] = $answer;
            }

            $allQuestions = QuestionDao::getAllForQuestionnaire($participantAnswerGroup->questionnaireId);
            $questions = [];
            foreach ($allQuestions as $question) {
                if ($question->secret) {
                    $questions []= $question;
                }
            }
            if (count($questions) > 0 ) {
                $evaluators = AnswerEvaluationService::getAllEvaluators();
                $tableModel = new TableModel();
                $tableModel->title = 'Astrologer Answer Group: '.$astrologerAnswerGroupId;

                // Table Headers
                $tableModel->header []= [
                    ['colspan' => 2, 'value' => 'Question'],
                    ['colspan' => 3, 'value' => 'Participant Answer'],
                    ['colspan' => 3, 'value' => 'Astrologer Answer'],
                    ['colspan' => count($evaluators), 'value' => 'Evaluations']];
                $tableModel->header []= ['ID', 'Text', 'ID', 'Option', 'Text', 'ID', 'Option', 'Text'];
                foreach ($evaluators as $evaluator) {
                    $tableModel->header[count($tableModel->header) - 1] []= $evaluator->getName();
                }

                // Table Content
                foreach ($questions as $question) {
                    // Question
                    $dataRow = [$question->id];
                    $dataRow []= (empty($question->number) ? '' : $question->number.') ').$question->text;

                    // Participant's Answers
                    if (!empty($participantAnswersByQuestionIdMap[$question->id])) {
                        $participantAnswer = $participantAnswersByQuestionIdMap[$question->id];
                        $dataRow []= $participantAnswer->id;
                        $dataRow []= isset($participantAnswer->questionOptionId) ? renderOptionsCell($question, $participantAnswer->questionOptionId) : '';
                        $dataRow [] = empty($participantAnswer->value) ? '' :
                        $question->type->is(QuestionType::Complex) ? AnswerRender::renderComplexAnswer($question, $participantAnswer) : $participantAnswer->value;
                    } else {
                        $dataRow []= ['align' => 'center', 'colspan' => 3, 'value' => '<font color="red"><b>No Answer</b></font>'];
                    }

                    // Astrologer's Answers
                    if (!empty($astrologerAnswersByQuestionIdMap[$question->id])) {
                        $astrologerAnswer = $astrologerAnswersByQuestionIdMap[$question->id];
                        $dataRow []= $astrologerAnswer->id;
                        $dataRow []= isset($astrologerAnswer->questionOptionId) ? renderOptionsCell($question, $astrologerAnswer->questionOptionId) : '';
                        $dataRow [] = empty($astrologerAnswer->value) ? '' :
                        $question->type->is(QuestionType::Complex) ? AnswerRender::renderComplexAnswer($question, $astrologerAnswer) : $astrologerAnswer->value;
                    } else if ($question->secret) {
                        $dataRow []= ['align' => 'center', 'colspan' => 3, 'value' => '<font color="red"><b>No Answer</b></font>'];
                    } else {
                        $dataRow []= ['colspan' => 3];
                    }

                    foreach ($evaluators as $evaluator) {
                        $dataRow []= $evaluator->evaluateAnswer($question, $participantAnswer, $astrologerAnswer);
                    }

                    $tableModel->data []= $dataRow;
                }

                // Total Score Row
                $columnCount = count($tableModel->data[count($tableModel->data) - 1]);
                $dataRow = [['colspan' => $columnCount - count($evaluators), 'value' => 'Total Score']];
                foreach ($evaluators as $evaluator) {
                    $dataRow []= $evaluator->getAverageScore();
                }
                $tableModel->data []= $dataRow;

                // Rendering Table
                $body_content .= HTMLRender::renderTable($tableModel, 'admin-table');
            } else {
                $body_content .= 'No questions found for the Astrologer Answer Group with ID: '.$astrologerAnswerGroupId;
            }
        }
    }

    function renderOptionsCell($question, $optionId) {
        $content = '';
        foreach ($question->options as $option) {
            $content .= $option->id == $optionId ? '<font color="red">>>> '.$option->text.' <<<</font>' : $option->text;

            if ($option->isNotApplicable) {
                $content .= ' <font color="red">(n/a)</font>';
            }

            $content .= '<br />';
        }
        return $content;
    }

    include $_SERVER["DOCUMENT_ROOT"].'/admin/templates/page.php';
?>
