<?php
    if (!class_exists('LoginDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php'; }
    LoginDao::checkPermissionsAndRedirect(Permission::QuestionsView, './');

    $browser_title = 'Chaitanya Academy - Questionnaires';
    $page_title = 'Questionnaires - Administration';
    $body_content = '';

    $sql = "SELECT id, name, is_active FROM questionnaires";
    $result = Db::query($sql);
    if (count($result) > 0) {
        // TODO Rewrite using HTMLRender::renderTable(...)
        $body_content .= '<table class="admin-table">';
        $body_content .= '<tr>
                <th>ID</th>
                <th>Name</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>';
        foreach ($result as $row) {
            $body_content .= '<tr>';
            $body_content .= '<td>'.$row['id'].'</td>';
            $body_content .= '<td>'.$row['name'].'</td>';
            $body_content .= '<td>'.$row['is_active'].'</td>';
            $body_content .= '<td>';
            if (LoginDao::checkPermissions(Permission::QuestionsEdit)) {
                $body_content .= '<a href="questionnaire_edit.php?id='.$row['id'].'">Edit</a>';
            }
            $body_content .= '</td>';
            $body_content .= '</tr>';
        }
        $body_content .= '</table>';
    } else {
        $body_content .= '<tr><td colspan="3">0 results</td></tr>';
    }

    include $_SERVER["DOCUMENT_ROOT"].'/admin/templates/page.php';
?>
