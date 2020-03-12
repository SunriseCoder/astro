<?php
    if (!class_exists('LoginDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php'; }
    LoginDao::checkPermissionsAndRedirect(Permission::QuestionsView, './');

    if (!class_exists('Question')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/questions.php'; }

    $browser_title = 'Chaitanya Academy - Questions';
    $page_title = 'Questions - Administration';
    $body_content = '';

    $questionnaires = QuestionnaireDao::getAll();
    $questions = QuestionDao::getAll();
    if (count($questions) > 0) {
        $body_content .= '<table class="admin-table">';
        $body_content .= '<tr>
                <th>ID</th>
                <th>Number</th>
                <th>Text</th>
                <th>Type</th>
                <th>Options</th>
                <th>Position</th>
                <th>Questionnaire</th>
                <th>Actions</th>
              </tr>';
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
            $body_content .= '<td>'.$questionnaires[$question->questionnaireId]->name.'</td>';
            $body_content .= '<td>';
            if (LoginDao::checkPermissions(Permission::QuestionsEdit)) {
                $body_content .= '<a href="question_edit.php?id='.$question->id.'">Edit</a>';
            }
            $body_content .= '</td>';
            $body_content .= '</tr>';
        }
        $body_content .= '</table>';
    } else {
        $body_content .= '0 Questions';
    }

    include $_SERVER["DOCUMENT_ROOT"].'/admin/templates/page.php';
