<?php
    if (!class_exists('LoginDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php'; }
    LoginDao::checkPermissionsAndRedirect(Permission::QuestionsView, './');

    $browser_title = 'Chaitanya Academy - Question Types';
    $page_title = 'Question Types - Administration';
    $body_content = '';

    $sql = 'SELECT qt.id as id,
                   qt.code as code,
                   qt.name as name
              FROM question_types qt';
    $error = Db::query($sql);
    if (count($error) > 0) {
        $body_content .= '<table class="admin-table">';
        $body_content .= '<tr>
                <th>ID</th>
                <th>Code</th>
                <th>Name</th>
              </tr>';
        foreach ($error as $row) {
            $body_content .= '<tr>';
            $body_content .= '<td>'.$row['id'].'</td>';
            $body_content .= '<td>'.$row['code'].'</td>';
            $body_content .= '<td>'.$row['name'].'</td>';
            $body_content .= '</tr>';
        }
        $body_content .= '</table>';
    } else {
        $body_content .= '0 Question Types</td></tr>';
    }

    include $_SERVER["DOCUMENT_ROOT"].'/admin/templates/page.php';
