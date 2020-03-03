<?php
    if (!class_exists('LoginDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php'; }
    LoginDao::checkPermissionsAndRedirect([Permission::QuestionsView], './');
?>
<html>
    <?
        $browser_title = 'Chaitanya Academy - Questionnaires';
        $page_title = 'Questionnaires - Administration';

        include $_SERVER["DOCUMENT_ROOT"].'/admin/templates/metadata.php';
    ?>

    <body>
        <table>
            <tr>
                <td colspan="2">
                    <? include $_SERVER["DOCUMENT_ROOT"].'/templates/page_top.php'; ?>
                </td>
            </tr>
            <tr>
                <td class="menu">
                    <? include $_SERVER["DOCUMENT_ROOT"].'/admin/templates/menu.php'; ?>
                </td>
                <td>
                    <? include $_SERVER["DOCUMENT_ROOT"].'/templates/body_top.php'; ?>

                    <? /* Body Area Start */ ?>

                    <table>
                        <?
                            $sql = "SELECT id, name, is_active FROM questionnaires";
                            $result = Db::query($sql);
                            if (count($result) > 0) {
                                echo '<tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                      </tr>';
                                foreach ($result as $row) {
                                    echo '<tr>';
                                    echo '<td>'.$row['id'].'</td>';
                                    echo '<td>'.$row['name'].'</td>';
                                    echo '<td>'.$row['is_active'].'</td>';
                                    echo '<td>';
                                    if (LoginDao::checkPermissions([Permission::QuestionsEdit])) {
                                        echo '<a href="questionnaire_edit.php?id='.$row['id'].'">Edit</a>';
                                    }
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            } else {
                                echo '<tr><td colspan="3">0 results</td></tr>';
                            }
                        ?>
                    </table>

                    <? /* Body Area End */ ?>

                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <? include $_SERVER["DOCUMENT_ROOT"].'/templates/page_footer.php'; ?>
                </td>
            </tr>
        </table>
    </body>
</html>
