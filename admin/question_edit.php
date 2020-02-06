<html>
    <?
        $browser_title = 'Chaitanya Academy - Questions';
        $page_title = 'Question - Edit';

        $js_includes = array('js/question_edit.js');

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
                        include '../utils/db.php';

                        // Save Question after Edit
                        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            if (isset($_POST['question_id'])) {
                                $question_id = $_POST['question_id'];
                                // Update Query
                                $question_update_sql =
                                    'UPDATE questions
                                        SET questionnaire_id = ?,
                                            question_type_id = ?,
                                            text = ?
                                      WHERE id = ?';

                                $db->prepStmt($question_update_sql, 'iisi',
                                    [$_POST['questionnaire_id'], $_POST['question_type_id'], $_POST['question_text'], $question_id]);
                            } else {
                                // Insert New Question
                                $question_insert_sql =
                                    'INSERT INTO questions (questionnaire_id, question_type_id, position, text) VALUES (?, ?, ?, ?)';

                                $position = 0;
                                $db->prepStmt($question_insert_sql, 'iiis',
                                    [$_POST['questionnaire_id'], $_POST['question_type_id'], $position, $_POST['question_text']]);
                                $question_id = $db->insertedId();
                            }

                            if (isset($_POST['question_options'])) {
                                // Saving Question Options
                                $question_options = $_POST['question_options'];
                                function question_options_compare($a, $b) {
                                    return $a['position'] - $b['position'];
                                }
                                usort($question_options, 'question_options_compare');

                                $question_options_position = 10;
                                $survival_ids = array();
                                foreach($question_options as $question_option) {
                                    if (isset($question_option['id'])) {
                                        // Update existing Question Options
                                        $question_option_update_sql =
                                            'UPDATE question_options
                                                SET text = ?,
                                                    position = ?
                                              WHERE id = ?';
                                        $db->prepStmt($question_option_update_sql, 'sii',
                                            [$question_option['text'], $question_options_position, $question_option['id']]);
                                        array_push($survival_ids, $question_option['id']);
                                    } else {
                                        // Insert new Question Options
                                        $question_option_insert_sql =
                                            'INSERT INTO question_options (question_id, position, text)
                                                  VALUES (?, ?, ?)';
                                        $db->prepStmt($question_option_insert_sql, 'iis', [$question_id, $question_options_position, $question_option['text']]);
                                        $last_id = $db->insertedId();
                                        array_push($survival_ids, $last_id);
                                    }
                                    $question_options_position += 10;
                                }

                                // Delete deleted Question Options from Database
                                $question_options_select_sql =
                                    'SELECT id
                                       FROM question_options
                                      WHERE question_id = ?';
                                $question_options_result = $db->prepStmt($question_options_select_sql, 'i', [$question_id]);
                                foreach ($question_options_result as $question_options_row) {
                                    $question_option_id = $question_options_row['id'];
                                    if (!in_array($question_option_id, $survival_ids)) {
                                        $question_options_delete_sql =
                                            'DELETE FROM question_options
                                                   WHERE id = ?';
                                        $db->prepStmt($question_options_delete_sql, 'i', [$question_option_id]);
                                    }
                                }
                            } else {
                                // If Question Options are not set, remove ALL Question Options for this Question
                                $question_options_delete_sql =
                                    'DELETE FROM question_options
                                           WHERE question_id = ?';
                                $db->prepStmt($question_options_delete_sql, 'i', [$question_id]);
                            }
                        }

                        // Queries
                        $questionnaires_sql =
                            'SELECT id, name FROM questionnaires';

                        $question_types_sql =
                            'SELECT id, code, name FROM question_types';

                        $question_sql =
                            'SELECT q.id as question_id,
                                    q.text as question_text,
                                    q.questionnaire_id as questionnaire_id,
                                    q.question_type_id as question_type_id,
                                    qt.code as question_type_code
                               FROM questions q
                          LEFT JOIN question_types qt on qt.id = q.question_type_id
                              WHERE q.id = ?';

                        $question_options_sql =
                            'SELECT qo.id as question_option_id,
                                    qo.position as question_option_position,
                                    qo.text as question_option_text
                               FROM question_options qo
                              WHERE qo.question_id = ?
                           ORDER BY qo.position';

                        if (isset($_GET['id'])) {
                            $question_id = $_GET['id'];
                        }

                        // Parsing Question by given ID
                        if (isset($question_id)) {
                            // Question
                            $question_result = $db->prepStmt($question_sql, 'i', [$question_id]);
                            if (count($question_result) == 1) {
                                $question_row = $question_result[0];
                            } else {
                                echo 'Question with ID '.$question_id.' is not found';
                            }
                        }

                        echo '<form action="" method="post">';

                        echo '<div>Question:</div>';
                        echo '<table>';
                        echo '<tr>
                                <th>Field</th>
                                <th>Value</th>
                              </tr>';

                        // Question ID
                        $question_id = $question_row['question_id'];
                        echo '<tr><td>ID</td><td>';
                        if (isset($question_id)) {
                            echo '<input type="hidden" name="question_id" value="'.$question_id.'" />'.$question_id;
                        } else {
                            echo 'New Question';
                        }
                        echo '</td></tr>';

                        // Question Text
                        echo '<tr><td>Question Text</td><td><input type="text" name="question_text" size="50" value="'.$question_row['question_text'].'" /></td></tr>';

                        // Questionnaire Field
                        echo '<tr><td>Questionnaire</td><td><select name="questionnaire_id">';
                        if (isset($_GET['questionnaire_id'])) {
                            $questionnaire_id = $_GET['questionnaire_id'];
                        }
                        if (isset($question_row['questionnaire_id'])) {
                            $questionnaire_id = $question_row['questionnaire_id'];
                        }
                        $questionnaires_result = $db->query($questionnaires_sql);
                        foreach ($questionnaires_result as $questionnaire_row) {
                            echo '<option value="'.$questionnaire_row['id'].'"';
                            if ($questionnaire_id == $questionnaire_row['id']) {
                                echo ' selected="selected"';
                            }
                            echo '>'.$questionnaire_row['name'].'</option>';
                        }
                        echo '</select></td></tr>';

                        // Question Type
                        echo '<tr><td>Question Type</td><td>';
                        echo '<select id="question_type_select" name="question_type_id" onchange="questionTypeChanged()">';
                        $question_types_result = $db->query($question_types_sql);
                        foreach ($question_types_result as $question_type_row) {
                            echo '<option question_type_code="'.$question_type_row['code'].'" value="'.$question_type_row['id'].'"';
                            if ($question_row['question_type_id'] == $question_type_row['id']) {
                                echo ' selected="selected"';
                            }
                            echo '>'.$question_type_row['name'].'</option>';
                        }
                        echo '</select></td></tr>';
                        echo '</table>';

                        echo '<div>Answer options:</div>';
                        echo '<table id="question_options_table">';
                        echo '<tr>
                                <th>ID</th>
                                <th>Text</th>
                                <th>Position</th>
                                <th>Actions</th>
                              </tr>';

                        // Question Options
                        $question_type = $question_row['question_type_code'];
                        if ($question_type == 'SINGLE_CHOICE') {
                            $question_options_result = $db->prepStmt($question_options_sql, 'i', [$question_id]);

                            // Questions List
                            $question_options_counter = 0;
                            foreach ($question_options_result as $question_options_row) {
                                echo '<tr row_number="'.$question_options_counter.'">';

                                // Question Option ID
                                $qo_id = $question_options_row['question_option_id'];
                                $name_id = 'question_options['.$question_options_counter.'][id]';
                                echo '<td><input type="hidden" name="'.$name_id.'" value="'.$qo_id.'" />'.$qo_id.'</td>';

                                // Question Option Text
                                $qo_text = $question_options_row['question_option_text'];
                                $name_text = 'question_options['.$question_options_counter.'][text]';
                                echo '<td><input type="text" name="'.$name_text.'" value="'.$qo_text.'" size="30" /></td>';

                                // Question Option Position
                                $qo_position = $question_options_row['question_option_position'];
                                $name_position = 'question_options['.$question_options_counter.'][position]';
                                echo '<td><input type="text" name="'.$name_position.'" value="'.$qo_position.'" size="4" /></td>';

                                // Question Option Actions
                                echo '<td><input type="button" onclick="deleteAnswerOption('.$question_options_counter.');" value="Delete" /></td>';
                                echo '</tr>';
                                $question_options_counter++;
                            }
                        }
                        echo '<tr><td colspan="4"><input type="button" onclick="addAnswerOption();" value="Add Answer Option" /></td></tr>';
                        echo '</table>';
                        echo '<input type="submit" value="Save" />';
                        echo '</form>';
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
