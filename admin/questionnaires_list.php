<html>
    <?
        $browser_title = 'Chaitanya Academy - Questionnaires';
        $page_title = 'Questionnaires - Administration';

        include 'templates/metadata.php';
    ?>

    <body>
        <table>
            <tr>
                <td colspan="2"><? include '../templates/page_top.php'; ?></td>
            </tr>
            <tr>
                <td class="menu"><? include 'templates/menu.php'; ?></td>
                <td>
                    <? include '../templates/body_top.php'; ?>

                    <? /* Body Area Start */ ?>

                    <?
                        include '../db.php';
                    ?>

                    <table>
                        <?
                            $sql = "SELECT id, name, is_active FROM questionnaires";
                            $result = $mysqli->query($sql);

                            if ($result->num_rows > 0) {
                                echo '<tr><th>ID</th><th>Name</th><th>Status</th></tr>';
                                while($row = $result->fetch_assoc()) {
                                    echo '<tr>';
                                    echo '<td>'.$row['id'].'</td>';
                                    echo '<td><a href="questionnaire_edit.php?id='.$row['id'].'">'.$row['name'].'</a></td>';
                                    echo '<td>'.$row['is_active'].'</td>';
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
                <td colspan="2"><? include '../templates/page_footer.php'; ?></td>
            </tr>
        </table>
    </body>
</html>
