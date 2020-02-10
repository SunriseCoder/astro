<html>
    <?
        $browser_title = 'Chaitanya Academy - Questionnaires';
        $page_title = 'Questionnaire - Edit';

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

                    <?
                        // Queries
                        $questionnaire_sql =
                            'SELECT id, name, is_active, is_locked
                               FROM questionnaires
                              WHERE id = ?';

                        $questions_by_questionnaire_sql =
                            'SELECT q.id as question_id,
                                    q.text as question_text,
                                    q.position as question_position,
                                    qt.name as question_type
                               FROM questions q
                          LEFT JOIN question_types qt on qt.id = q.question_type_id
                              WHERE q.questionnaire_id = ?
                           ORDER BY q.position ASC';

                        $all_questions_sql =
                            'SELECT q.id as question_id,
                                    q.text as question_text,
                                    q.position as question_position,
                                    qt.name as question_type
                               FROM questions q
                          LEFT JOIN question_types qt on qt.id = q.question_type_id';

                        $question_options_sql =
                            'SELECT id as question_option_id,
                                    question_id as question_id,
                                    text as question_option_text
                               FROM question_options
                           ORDER BY position ASC';

                        // Question Options Map
                        $question_options_result = Db::query($question_options_sql);
                        foreach ($question_options_result as $question_options_row) {
                            $stored_value = $question_options_map[$question_options_row['question_id']];
                            if (!empty($stored_value)) {
                                $stored_value = $stored_value.'<br />';
                            }
                            $stored_value = $stored_value.$question_options_row['question_option_text'];
                            $question_options_map[$question_options_row['question_id']] = $stored_value;
                        }

                        // Parsing Questionnaire by given ID
                        if (isset($_GET['id'])) {
                            $questionnaire_id = $_GET['id'];

                            // Questionnaire
                            $questionnaire_result = Db::prepQuery($questionnaire_sql, 'i', [$questionnaire_id]);
                            if (count($questionnaire_result) == 1) {
                                $questionnaire = $questionnaire_result[0];
                                echo '<div>'.$questionnaire['name'].':</div>';

                                // Parsing Questions for the Questionnaire
                                $questions_result = Db::prepQuery($questions_by_questionnaire_sql, 'i', [$questionnaire_id]);
                                if (count($questions_result) > 0) {
                                    echo '<table>';
                                    echo '<tr>
                                            <th>ID</th>
                                            <th>Text</th>
                                            <th>Options</th>
                                            <th>Type</th>
                                            <th>Position</th>
                                            <th>Actions</th>
                                          </tr>';

                                    // Questions List
                                    foreach ($questions_result as $question_row) {
                                        echo '<tr>';
                                        echo '<td>'.$question_row['question_id'].'</td>';
                                        echo '<td>'.$question_row['question_text'].'</td>';
                                        echo '<td>'.$question_options_map[$question_row['question_id']].'</td>';
                                        echo '<td>'.$question_row['question_type'].'</td>';
                                        echo '<td>'.$question_row['question_position'].'</td>';
                                        echo '<td><a href="question_edit.php?id='.$question_row['question_id'].'">Edit</a></td>';
                                        echo '</tr>';
                                    }
                                    echo '</table>';
                                }
                                echo count($questions_result).' question(s)<br />';
                                echo '<a href="question_edit.php?questionnaire_id='.$questionnaire_id.'">Add New Question</a>';
                            } else {
                                echo 'Questionnaire with ID '.$questionnaire_id.' is not found';
                            }
                        } else {
                            echo 'Questionnaire ID is not set';
                        }
                    ?>

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
