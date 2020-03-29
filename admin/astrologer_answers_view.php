<?php
    if (!class_exists('LoginDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php'; }
    LoginDao::checkPermissionsAndRedirect(Permission::AnswersView, './');

    if (!isset($_GET['id']) || !preg_match('/^[0-9]+$/', $_GET['id'])) {
        Utils::redirect('/admin/astrologer_answers_list.php');
    }

    if (!class_exists('QuestionDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/questions.php'; }
    if (!class_exists('QuestionRender')) { include $_SERVER["DOCUMENT_ROOT"].'/render/questions.php'; }
    if (!class_exists('ParticipantAnswerGroupDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/answers.php'; }
    if (!class_exists('HTMLRender')) { include $_SERVER["DOCUMENT_ROOT"].'/render/html.php'; }

    $browser_title = 'Chaitanya Academy - Astrologer Answers';
    $page_title = 'Astrologer Answers View';
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

            $questions = QuestionDao::getAllForQuestionnaire($participantAnswerGroup->questionnaireId);
            if (count($questions) > 0 ) {
                $tableModel = new TableModel();
                $tableModel->title = 'Astrologer Answer Group: '.$astrologerAnswerGroupId;

                // Table Headers
                $tableModel->header []= [['colspan' => 3, 'value' => 'Question'], ['colspan' => 3, 'value' => 'Participant Answer'], ['colspan' => 3, 'value' => 'Astrologer Answer']];
                $tableModel->header []= ['ID', 'Text', 'Type', 'ID', 'Option', 'Text', 'ID', 'Option', 'Text'];

                // Table Content
                foreach ($questions as $question) {
                    // Question
                    $dataRow = [$question->id];
                    $dataRow []= (empty($question->number) ? '' : $question->number.') ').$question->text;
                    $dataRow []= $question->type->code;

                    // Participant's Answers
                    if (!empty($participantAnswersByQuestionIdMap[$question->id])) {
                        $answer = $participantAnswersByQuestionIdMap[$question->id];
                        $dataRow []= $answer->id;
                        $dataRow []= isset($answer->questionOptionId) ? $question->options[$answer->questionOptionId]->text : '';
                        $dataRow [] = empty($answer->value) ? '' :
                        $question->type->is(QuestionType::Complex) ? AnswerRender::renderComplexAnswer($question, $answer) : $answer->value;
                    } else {
                        $dataRow []= ['align' => 'center', 'colspan' => 3, 'value' => '<font color="red"><b>No Answer</b></font>'];
                    }

                    // Astrologer's Answers
                    if (!empty($astrologerAnswersByQuestionIdMap[$question->id])) {
                        $answer = $astrologerAnswersByQuestionIdMap[$question->id];
                        $dataRow []= $answer->id;
                        $dataRow []= isset($answer->questionOptionId) ? $question->options[$answer->questionOptionId]->text : '';
                        $dataRow [] = empty($answer->value) ? '' :
                        $question->type->is(QuestionType::Complex) ? AnswerRender::renderComplexAnswer($question, $answer) : $answer->value;
                    } else if ($question->secret) {
                        $dataRow []= ['align' => 'center', 'colspan' => 3, 'value' => '<font color="red"><b>No Answer</b></font>'];
                    } else {
                        $dataRow []= ['colspan' => 3];
                    }

                    $tableModel->data []= $dataRow;
                }
                $body_content .= HTMLRender::renderTable($tableModel, 'admin-table');
            } else {
                $body_content .= 'No questions found for the Astrologer Answer Group with ID: '.$astrologerAnswerGroupId;
            }
        }
    }

    include $_SERVER["DOCUMENT_ROOT"].'/admin/templates/page.php';
?>
