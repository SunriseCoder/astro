<?php
    if (!class_exists('LoginDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php'; }
    LoginDao::checkPermissionsAndRedirect([Permission::AnswerSessionsView], './');

    include $_SERVER["DOCUMENT_ROOT"].'/dao/questions.php';

    $browser_title = 'Chaitanya Academy - Answer Sessions';
    $page_title = 'Answer Sessions - Administration';
    $body_content = '';

    // Delete Answer Session
    if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
        LoginDao::checkPermissionsAndRedirect([Permission::AnswerSessionsDelete], './');
        $id = $_GET['id'];
        $error = AnswerSessionDao::delete($id);
        if ($error) {
            $body_content .= '<font color="red">'.$error.'</font>';
        } else {
            $body_content .= '<font color="green">Answer Session was successfully deleted.</font>';
        }
    }

    $sql = 'SELECT ast.id as id,
                   qn.id as questionnaire_id,
                   qn.name as questionnaire_name,
                   ast.origin_id as origin_id,
                   ast.user_id as user_id,
                   u.name as user_name,
                   ast.ip_address as ip_address,
                   ast.date as date,
                   a.answers_count as answers_count,
                   COALESCE(d.descendants_count, 0) as descendants_count
              FROM answer_sessions ast
         LEFT JOIN users u on u.id = ast.user_id
         LEFT JOIN questionnaires qn on qn.id = ast.questionnaire_id
         LEFT JOIN (SELECT session_id,
                           COUNT(1) as answers_count
                      FROM answers
                  GROUP BY session_id) a on a.session_id = ast.id
         LEFT JOIN (SELECT origin_id as id,
                           count(1) as descendants_count
                      FROM answer_sessions
                     WHERE origin_id IS NOT NULL
                  GROUP BY origin_id) d on d.id = ast.id
          ORDER BY ast.id DESC';
    $error = Db::query($sql);
    if (count($error) > 0) {
        $body_content .= '<table class="admin-table">';
        $body_content .= '<tr>
                <th>ID</th>
                <th>Questionnaire</th>
                <th>Origin</th>
                <th>User</th>
                <th>IP Address</th>
                <th>Date</th>
                <th>Answers</th>
                <th>Calcs</th>
                <th>Actions</th>
              </tr>';
        foreach ($error as $row) {
            $body_content .= '<tr>';
            $body_content .= '<td>'.$row['id'].'</td>';
            $body_content .= '<td>'.$row['questionnaire_id'].': '.$row['questionnaire_name'].'</td>';
            $originText = isset($row['origin_id']) ? '<a href="answers_view.php?session_id='.$row['origin_id'].'">'.$row['origin_id'].': View</a>' : '';
            $body_content .= '<td>'.$originText.'</td>';
            $body_content .= '<td>'.$row['user_id'].': '.$row['user_name'].'</td>';
            $body_content .= '<td>'.$row['ip_address'].'</td>';
            $body_content .= '<td>'.$row['date'].'</td>';
            $body_content .= '<td>'.$row['answers_count'].'</td>';
            $body_content .= '<td>'.$row['descendants_count'].'</td>';
            $body_content .= '<td>';
            $body_content .= '<a href="answers_view.php?session_id='.$row['id'].'">View</a>';
            if ($row['descendants_count'] == 0 && LoginDao::checkPermissions([Permission::AnswerSessionsDelete])) {
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
