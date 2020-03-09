<?php
    if (!class_exists('LoginDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php'; }
    LoginDao::checkPermissionsAndRedirect([Permission::QuestionsEdit], './');

    include $_SERVER["DOCUMENT_ROOT"].'/dao/questions.php';

    $browser_title = 'Chaitanya Academy - Questions';
    $page_title = 'Question - Edit';
    $js_includes = ['js/question_edit.js'];
    $body_content = '';

    // Save Question after Edit
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['question_id'])) {
            $error = QuestionDao::updateFromPost();
        } else {
            $error = QuestionDao::insertFromPost();
        }

        if (isset($error)) {
            $body_content .= '<font color="red">Error: '.$error.'</font><br />';
        } else {
            $body_content .= '<font color="green">Saved Successfully</font><br />';
        }
    }

    if (isset($_GET['id'])) {
        $questionId = $_GET['id'];
        $question = QuestionDao::getById($questionId);
    }
    if (isset($questionId) && !isset($question)) {
        $body_content .= 'Question with ID: '.$questionId.' is not found';
    } else {
        $body_content .= '<form action="" method="post">';

        $body_content .= '<div>Question:</div>';
        $body_content .= '<table class="admin-table">';
        $body_content .= '<tr>
                <th>Field</th>
                <th>Value</th>
              </tr>';

        $body_content .= '<tr>';
        $body_content .= '<td>ID</td>';
        $body_content .= '<td>';
        if (isset($question)) {
            $body_content .= '<input type="hidden" name="question_id" value="'.$question->id.'" />'.$question->id;
        } else {
            $body_content .= 'New Question';
        }
        $body_content .= '</td>';
        $body_content .= '</tr>';

        // Question Number
        $questionNumber = isset($question) ? $question->number : '';
        $body_content .= '<tr><td>Question Number</td><td><input type="text" name="question_number" size="50" value="'.$questionNumber.'" /></td></tr>';

        // Question Text
        $questionText = isset($question) ? $question->text : '';
        $body_content .= '<tr><td>Question Text</td><td><input type="text" name="question_text" size="50" value="'.$questionText.'" /></td></tr>';

        // Question Markup
        $questionMarkup = isset($question) ? $question->markup : '';
        $body_content .= '<tr><td>Question Markup</td><td><textarea name="question_markup" rows="10" cols="50">'.$questionMarkup.'</textarea></td></tr>';

        // Question Position
        $questionPosition = isset($question) ? $question->position : '';
        $body_content .= '<tr><td>Position in Questionnaire</td><td><input type="text" name="question_position" size="50" value="'.$questionPosition.'" /></td></tr>';

        // Questionnaire Field
        $body_content .= '<tr><td>Questionnaire</td><td><select name="questionnaire_id">';
        $questionnaires = QuestionnaireDao::getAll();
        $questionnaireId = isset($question) ? $question->questionnaireId : (isset($_GET['questionnaire_id']) ? $_GET['questionnaire_id'] : '');
        foreach ($questionnaires as $questionnaire) {
            $body_content .= '<option value="'.$questionnaire->id.'"';
            if ($questionnaire->id == $questionnaireId) {
                $body_content .= ' selected="selected"';
            }
            $body_content .= '>'.$questionnaire->name.'</option>';
        }
        $body_content .= '</select></td></tr>';

        // Question Type
        $body_content .= '<tr><td>Question Type</td><td>';
        $body_content .= '<select id="question_type_select" name="question_type_id" onchange="questionTypeChanged()">';
        $questionTypes = QuestionTypeDao::getAll();
        foreach ($questionTypes as $questionType) {
            $body_content .= '<option question_type_code="'.$questionType->code.'" value="'.$questionType->id.'"';
            if (isset($question) && $question->typeId == $questionType->id) {
                $body_content .= ' selected="selected"';
            }
            $body_content .= '>'.$questionType->name.'</option>';
        }
        $body_content .= '</select></td></tr>';
        $body_content .= '</table><br />';

        $body_content .= '<div>Answer options:</div>';
        $body_content .= '<table id="question_options_table" class="admin-table">';
        $body_content .= '<tr>
                <th>ID</th>
                <th>Text</th>
                <th>Position</th>
                <th>Actions</th>
              </tr>';

        // Question Options
        if (isset($question)) {
            $questionType = $questionTypes[$question->typeId];
            if (isset($questionType) && $questionType->is(QuestionType::SingleChoice)) {
                // QuestionsOptions List
                $questionOptions = QuestionOptionDao::getAllForQuestion($question->id);
                $questionOptionsCounter = 0;
                foreach ($questionOptions as $questionOption) {
                    $body_content .= '<tr row_number="'.$questionOptionsCounter.'">';

                    // Question Option ID
                    $questionOptionId = $questionOption->id;
                    $questionOptionIdName = 'question_options['.$questionOptionsCounter.'][id]';
                    $body_content .= '<td><input type="hidden" name="'.$questionOptionIdName.'" value="'.$questionOptionId.'" />'.$questionOptionId.'</td>';

                    // Question Option Text
                    $questionOptionText = $questionOption->text;
                    $questionOptionTextName = 'question_options['.$questionOptionsCounter.'][text]';
                    $body_content .= '<td><input type="text" name="'.$questionOptionTextName.'" value="'.$questionOptionText.'" size="30" /></td>';

                    // Question Option Position
                    $questionOptionPosition = $questionOption->position;
                    $questionOptionPositionName = 'question_options['.$questionOptionsCounter.'][position]';
                    $body_content .= '<td><input type="text" name="'.$questionOptionPositionName.'" value="'.$questionOptionPosition.'" size="4" /></td>';

                    // Question Option Actions
                    $body_content .= '<td><input type="button" onclick="deleteAnswerOption('.$questionOptionsCounter.');" value="Delete" /></td>';
                    $body_content .= '</tr>';
                    $questionOptionsCounter++;
                }
            }
        }

        $body_content .= '<tr><td colspan="4"><input type="button" onclick="addAnswerOption();" value="Add Answer Option" /></td></tr>';
        $body_content .= '</table><br />';
        $body_content .= '<input type="submit" value="Save" />';
        $body_content .= '</form>';
    }

    include $_SERVER["DOCUMENT_ROOT"].'/admin/templates/page.php';
