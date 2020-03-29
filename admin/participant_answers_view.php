<?php
    if (!class_exists('LoginDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php'; }
    LoginDao::checkPermissionsAndRedirect(Permission::AnswersView, './');

    if (!isset($_GET['group_id']) || !preg_match('/^[0-9]+$/', $_GET['group_id'])) {
        Utils::redirect('/admin/participant_answers_list.php');
    }

    if (!class_exists('QuestionDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/questions.php'; }
    if (!class_exists('ParticipantAnswerGroupDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/answers.php'; }
    if (!class_exists('QuestionRender')) { include $_SERVER["DOCUMENT_ROOT"].'/render/questions.php'; }
    if (!class_exists('HTMLRender')) { include $_SERVER["DOCUMENT_ROOT"].'/render/html.php'; }

    $browser_title = 'Chaitanya Academy - Participant Answers';
    $page_title = 'Participant Answers View';
    $css_includes = ['/css/questions.css'];
    $body_content = '';

    // Delete Answer Group
    $astrologerAnswerGroupId = $_GET['group_id'];
    $astrologerAnswerGroups = ParticipantAnswerGroupDao::getWithAllAnswers($astrologerAnswerGroupId);

    if (count($astrologerAnswerGroups) > 0) {
        $answerGroup = $astrologerAnswerGroups[$astrologerAnswerGroupId];

        $answersByQuestions = [];
        foreach ($answerGroup->answers as $answer) {
            $answersByQuestions[$answer->questionId] = $answer;
        }

        $questions = QuestionDao::getAllForQuestionnaire($answerGroup->questionnaireId);
        if (count($questions) > 0 ) {
            $tableModel = new TableModel();
            $tableModel->title = 'Participant Answer Group: '.$astrologerAnswerGroupId;

            // Table Header
            $tableModel->header = [
                [['colspan' => 3, 'value' => 'Question'], ['colspan' => 3, 'value' => 'Answer']],
                ['ID', 'Text', 'Type', 'ID', 'Option', 'Text']
            ];

            // Table Content
            foreach ($questions as $question) {
                $dataRow = [];

                // Question Data
                $dataRow []= $question->id;
                $questionTextCellValue = '';
                if (!empty($question->number)) {
                    $questionTextCellValue .= $question->number.') ';
                }
                $questionTextCellValue .= $question->text;
                $dataRow []= $questionTextCellValue;
                $dataRow []= $question->type->code;

                // Answer Data
                if (!empty($answersByQuestions[$question->id])) {
                    $answer = $answersByQuestions[$question->id];
                    $dataRow []= $answer->id;
                    $dataRow []= isset($answer->questionOptionId) ? $question->options[$answer->questionOptionId]->text : '';
                    $dataRow [] = empty($answer->value) ? '' :
                        $question->type->is(QuestionType::Complex) ? AnswerRender::renderComplexAnswer($question, $answer) : $answer->value;
                } else {
                    $dataRow []= ['align' => 'center', 'colspan' => 3, 'value' => '<font color="red"><b>No Answer</b></font>'];
                }
                $tableModel->data []= $dataRow;
            }
            $body_content .= HTMLRender::renderTable($tableModel, 'admin-table');
        } else {
            $body_content .= 'No questions found for Participant Answer Group: '.$astrologerAnswerGroupId;
        }
    } else {
        $body_content .= 'Participant Answer Group with ID: '.$astrologerAnswerGroupId.' was not found';
    }

    include $_SERVER["DOCUMENT_ROOT"].'/admin/templates/page.php';
?>
