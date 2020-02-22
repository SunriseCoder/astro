<html>
    <?
        $browser_title = 'Chaitanya Academy - Questions';
        $page_title = 'Questions - Administration';

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
                    <? include $_SERVER["DOCUMENT_ROOT"].'/admin/templates/menu.php'; ?></td>
                <td>
                    <? include $_SERVER["DOCUMENT_ROOT"].'/templates/body_top.php'; ?>

                    <? /* Body Area Start */ ?>
                    <table>
                        <?
                            // Question Options Map
                            $question_options_sql =
                                'SELECT id as question_option_id,
                                        question_id as question_id,
                                        text as question_option_text
                                   FROM question_options';
                            $question_options_result = Db::query($question_options_sql);
                            $question_options_map = [];
                            foreach ($question_options_result as $question_options_row) {
                                if (isset($question_options_map[$question_options_row['question_id']])) {
                                    $stored_value = $question_options_map[$question_options_row['question_id']];
                                    $stored_value = $stored_value.'<br />';
                                } else {
                                    $stored_value = '';
                                }
                                $stored_value = $stored_value.$question_options_row['question_option_text'];
                                $question_options_map[$question_options_row['question_id']] = $stored_value;
                            }

                            // Questions List
                            $questions_sql = 'SELECT  q.id as question_id,
                                                      q.number as question_number,
                                                      q.position as question_position,
                                                      q.text as question_text,
                                                      qn.name as questionnaire_name,
                                                      qt.name as question_type_name
                                                 FROM questions q
                                            LEFT JOIN questionnaires qn on qn.id = q.questionnaire_id
                                            LEFT JOIN question_types qt on qt.id = q.question_type_id';
                            $questions_result = Db::query($questions_sql);
                            if (count($questions_result) > 0) {
                                echo '<tr>
                                        <th>ID</th>
                                        <th>Number</th>
                                        <th>Text</th>
                                        <th>Type</th>
                                        <th>Options</th>
                                        <th>Position</th>
                                        <th>Questionnaire</th>
                                        <th>Actions</th>
                                      </tr>';
                                foreach ($questions_result as $question_row) {
                                    echo '<tr>';
                                    echo '<td>'.$question_row['question_id'].'</td>';
                                    echo '<td>'.$question_row['question_number'].'</td>';
                                    echo '<td>'.$question_row['question_text'].'</td>';
                                    echo '<td>'.$question_row['question_type_name'].'</td>';
                                    echo '<td>';
                                    if (isset($question_options_map[$question_row['question_id']])) {
                                        echo $question_options_map[$question_row['question_id']];
                                    }
                                    echo '</td>';
                                    echo '<td>'.$question_row['question_position'].'</td>';
                                    echo '<td>'.$question_row['questionnaire_name'].'</td>';
                                    echo '<td><a href="question_edit.php?id='.$question_row['question_id'].'">Edit</a></td>';
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
