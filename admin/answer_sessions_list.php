<html>
    <?
        $browser_title = 'Chaitanya Academy - Answer Sessions';
        $page_title = 'Answer Sessions - Administration';

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
                            include $_SERVER["DOCUMENT_ROOT"].'/dao/questions.php';

                            if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
                                // Delete Answer Session
                                $id = $_GET['id'];
                                $result = AnswerSessionDao::delete($id);
                                if ($result) {
                                    echo '<font color="green">Answer Session was successfully deleted.</font>';
                                } else {
                                    echo '<font color="red">Answer Session was not deleted.</font>';
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
                                echo '<tr>
                                        <th>ID</th>
                                        <th>Questionnaire</th>
                                        <th>Origin</th>
                                        <th>User</th>
                                        <th>IP Address</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                      </tr>';
                                foreach ($result as $row) {
                                    echo '<tr>';
                                    echo '<td>'.$row['id'].'</td>';
                                    echo '<td>'.$row['questionnaire_id'].': '.$row['questionnaire_name'].'</td>';
                                    $originText = isset($row['origin_id']) ? '<a href="answers_view.php?session_id='.$row['origin_id'].'">'.$row['origin_id'].': View</a>' : '';
                                    echo '<td>'.$originText.'</td>';
                                    echo '<td>'.$row['user_id'].': '.$row['user_name'].'</td>';
                                    echo '<td>'.$row['ip_address'].'</td>';
                                    echo '<td>'.$row['date'].'</td>';
                                    echo '<td>
                                            <a href="answers_view.php?session_id='.$row['id'].'">View</a>
                                            <a href="answer_sessions_list.php?action=delete&id='.$row['id'].'">Delete</a>
                                        </td>';
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
