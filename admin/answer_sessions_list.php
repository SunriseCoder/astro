<?php
    if (!class_exists('LoginDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php'; }
    LoginDao::checkPermissionsAndRedirect([Permission::AnswerSessionsView], './');

    if (isset($_GET['action']) && $_GET['action'] == 'delete') {
        LoginDao::checkPermissionsAndRedirect([Permission::AnswerSessionsDelete], './');
    }

    include $_SERVER["DOCUMENT_ROOT"].'/dao/questions.php';

    $browser_title = 'Chaitanya Academy - Answer Sessions';
    $page_title = 'Answer Sessions - Administration';
    $body_content = '';

    // Delete Answer Session
    if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
        // Delete Answer Session
        $id = $_GET['id'];
        $result = AnswerSessionDao::delete($id);
        if ($result) {
            $body_content .= '<font color="green">Answer Session was successfully deleted.</font>';
        } else {
            $body_content .= '<font color="red">Answer Session was not deleted.</font>';
        }
    }

    $sql = "SELECT ast.id as id,
                   qn.id as questionnaire_id,
                   qn.name as questionnaire_name,
                   ast.origin_id as origin_id,
                   ast.user_id as user_id,
                   u.name as user_name,
                   ast.ip_address as ip_address,
                   ast.date as date
              FROM answer_sessions ast
         LEFT JOIN users u on u.id = ast.user_id
         LEFT JOIN questionnaires qn on qn.id = ast.questionnaire_id
             ORDER BY ast.id DESC";
    $result = Db::query($sql);
    if (count($result) > 0) {
        $body_content .= '<table class="admin-table">';
        $body_content .= '<tr>
                <th>ID</th>
                <th>Questionnaire</th>
                <th>Origin</th>
                <th>User</th>
                <th>IP Address</th>
                <th>Date</th>
                <th>Actions</th>
              </tr>';
        foreach ($result as $row) {
            $body_content .= '<tr>';
            $body_content .= '<td>'.$row['id'].'</td>';
            $body_content .= '<td>'.$row['questionnaire_id'].': '.$row['questionnaire_name'].'</td>';
            $originText = isset($row['origin_id']) ? '<a href="answers_view.php?session_id='.$row['origin_id'].'">'.$row['origin_id'].': View</a>' : '';
            $body_content .= '<td>'.$originText.'</td>';
            $body_content .= '<td>'.$row['user_id'].': '.$row['user_name'].'</td>';
            $body_content .= '<td>'.$row['ip_address'].'</td>';
            $body_content .= '<td>'.$row['date'].'</td>';
            $body_content .= '<td>';
            $body_content .= '<a href="answers_view.php?session_id='.$row['id'].'">View</a>';
            if (LoginDao::checkPermissions([Permission::AnswerSessionsDelete])) {
                $body_content .= ' <a href="answer_sessions_list.php?action=delete&id='.$row['id'].'">Delete</a>';
            }
            $body_content .= '</td>';
            $body_content .= '</tr>';
        }
        $body_content .= '</table>';
    } else {
        $body_content .= '<tr><td colspan="3">0 results</td></tr>';
    }

    include $_SERVER["DOCUMENT_ROOT"].'/admin/templates/page.php';
