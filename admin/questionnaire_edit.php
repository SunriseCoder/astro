<?php
    if (!class_exists('LoginDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php'; }
    LoginDao::checkPermissionsAndRedirect(Permission::QuestionsView, './');

    if (!class_exists('Question')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/questions.php'; }

    $browser_title = 'Chaitanya Academy - Questionnaires';
    $page_title = 'Questionnaire - Edit';
    $body_content = '';

    // Parsing Questionnaire by given ID
    if (isset($_GET['id'])) {
        $questionnaireId = $_GET['id'];
        $questionnaire = QuestionnaireDao::getById($questionnaireId);
        if (isset($questionnaire)) {
            $body_content .= '<div>'.$questionnaire->name.':</div>';

            // Parsing Questions for the Questionnaire
            $questions = QuestionDao::getAllForQuestionnaire($questionnaire->id);
            if (count($questions) > 0) {
                $body_content .= '<table class="admin-table">';
                $body_content .= '<tr>
                        <th>ID</th>
                        <th>Number</th>
                        <th>Text</th>
                        <th>Type</th>
                        <th>Options</th>
                        <th>Position</th>
                        <th>Actions</th>
                      </tr>';

                // Questions List
                foreach ($questions as $question) {
                    $body_content .= '<tr>';
                    $body_content .= '<td>'.$question->id.'</td>';
                    $body_content .= '<td>'.$question->number.'</td>';
                    $body_content .= '<td>'.$question->text.'</td>';
                    $body_content .= '<td>'.$question->type->name.'</td>';
                    $body_content .= '<td>';
                    foreach ($question->options as $questionOption) {
                        $body_content .= $questionOption->text.'<br />';
                    }
                    $body_content .= '</td>';
                    $body_content .= '<td>'.$question->position.'</td>';
                    $body_content .= '<td><a href="question_edit.php?id='.$question->id.'">Edit</a></td>';
                    $body_content .= '</tr>';
                }
                $body_content .= '</table>';
            }
            $body_content .= count($questions).' question(s)<br />';
            $body_content .= '<a href="question_edit.php?questionnaire_id='.$questionnaire->id.'">Add New Question</a>';
        } else {
            $body_content .= 'Questionnaire with ID '.$questionnaire->id.' is not found';
        }
    } else {
        $body_content .= 'Questionnaire ID is not set';
    }

    include $_SERVER["DOCUMENT_ROOT"].'/admin/templates/page.php';
