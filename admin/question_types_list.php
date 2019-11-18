<html>
    <?
        $browser_title = 'Chaitanya Academy - Question Types';
        $page_title = 'Question Types - Administration';

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
                            $sql =
                                'SELECT  qt.id as id,
                                         qt.code as code,
                                         qt.name as name
                                    FROM question_types qt';

                            $result = $mysqli->query($sql);

                            if ($result->num_rows > 0) {
                                echo '<tr>
                                        <th>ID</th>
                                        <th>Code</th>
                                        <th>Name</th>
                                      </tr>';
                                while($row = $result->fetch_assoc()) {
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
                <td colspan="2"><? include '../templates/page_footer.php'; ?></td>
            </tr>
        </table>
    </body>
</html>
