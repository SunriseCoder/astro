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
                        // Queries
                        $questionnaire_sql =
                            'SELECT id, name, is_active, is_locked
                               FROM questionnaires
                              WHERE id = ?';

                        $questions_by_questionnaire_sql =
                            'SELECT q.id as question_id,
                                    q.text as question_text,
                                    qt.name as question_type
                               FROM questions q
                          LEFT JOIN question_types qt on qt.id = q.question_type_id
                              WHERE questionnaire_id = ?';

                        $all_questions_sql =
                            'SELECT q.id as question_id,
                                    q.text as question_text,
                                    qt.name as question_type
                               FROM questions q
                          LEFT JOIN question_types qt on qt.id = q.question_type_id';

                        $question_options_sql =
                            'SELECT id as question_option_id,
                                    question_id as question_id,
                                    text as question_option_text
                               FROM question_options';

                        include '../db.php';

                        // Question Options Map
                        $question_options_result = $mysqli->query($question_options_sql);
                        while($question_options_row = $question_options_result->fetch_assoc()) {
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
                            $questionnaire_stmt = $mysqli->prepare($questionnaire_sql);
                            $questionnaire_stmt->bind_param('i', $questionnaire_id);
                            $questionnaire_stmt->execute();
                            $questionnaire_result = $questionnaire_stmt->get_result();

                            if ($questionnaire_result->num_rows == 1) {
                                $questionnaire = $questionnaire_result->fetch_assoc();
                                echo '<div>'.$questionnaire['name'].':</div>';

                                $questions_stmt = $mysqli->prepare($questions_by_questionnaire_sql);
                                $questions_stmt->bind_param('i', $questionnaire_id);
                                $questions_stmt->execute();
                                $questions_result = $questions_stmt->get_result();
                            } else {
                                echo 'Questionnaire with ID '.$questionnaire_id.' is not found';
                                $questions_result = $mysqli->query($all_questions_sql);
                            }
                        } else {
                            echo 'Questionnaire ID is not set';
                            $questions_result = $mysqli->query($all_questions_sql);
                        }

                        // Parsing Questions for the Questionnaire
                        if ($questions_result->num_rows > 0) {
                            echo '<table>';
                            echo '<tr>
                                    <th>ID</th>
                                    <th>Text</th>
                                    <th>Options</th>
                                    <th>Type</th>
                                  </tr>';

                            // Questions List
                            while($question_row = $questions_result->fetch_assoc()) {
                                echo '<tr>';
                                echo '<td>'.$question_row['question_id'].'</td>';
                                echo '<td>'.$question_row['question_text'].'</td>';
                                echo '<td>'.$question_options_map[$question_row['question_id']].'</td>';
                                echo '<td>'.$question_row['question_type'].'</td>';
                                echo '</tr>';
                            }

                            echo '</table>';
                        }

                        echo $questions_result->num_rows.' question(s)';
                    ?>

                    <? /* Body Area End */ ?>

                </td>
            </tr>
            <tr>
                <td colspan="2"><? include '../templates/page_footer.php'; ?></td>
            </tr>
        </table>
    </body>
</html>
