<?php
    if (!class_exists('LoginDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php'; }
    LoginDao::checkPermissionsAndRedirect([Permission::QuestionsView], './');
?>
<html>
    <?
        $browser_title = 'Chaitanya Academy - Question Types';
        $page_title = 'Question Types - Administration';

        include $_SERVER["DOCUMENT_ROOT"].'/admin/templates/metadata.php';
    ?>

    <body>
        <table id="page-markup-table">
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
                            $sql =
                                'SELECT  qt.id as id,
                                         qt.code as code,
                                         qt.name as name
                                    FROM question_types qt';
                            $result = Db::query($sql);
                            if (count($result) > 0) {
                                echo '<tr>
                                        <th>ID</th>
                                        <th>Code</th>
                                        <th>Name</th>
                                      </tr>';
                                foreach ($result as $row) {
                                    echo '<tr>';
                                    echo '<td>'.$row['id'].'</td>';
                                    echo '<td>'.$row['code'].'</td>';
                                    echo '<td>'.$row['name'].'</td>';
                                    echo '</tr>';
                                }
                            } else {
                                echo '<tr><td colspan="2">0 results</td></tr>';
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
