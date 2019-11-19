<html>
    <?
        $browser_title = 'Chaitanya Academy - Questions';
        $page_title = 'Questions - Answer';

        include 'templates/metadata.php';
    ?>

    <body>
        <table>
            <tr>
                <td colspan="2"><? include 'templates/page_top.php'; ?></td>
            </tr>
            <tr>
                <td class="menu"><? include 'templates/menu.php'; ?></td>
                <td>
                    <? include 'templates/body_top.php'; ?>

                    <? /* Body Area Start */ ?>

                    <div>Hello Dear Stranger!</div>
                    <div>Could You please answer some questions anonymously?</div>

                    <?
                        include 'db.php';
                    ?>

                    <table>
                        <?
                            // Question Options Map
                            $question_options_sql =
                                'SELECT id as question_option_id,
                                        question_id as question_id,
                                        text as question_option_text
                                   FROM question_options';

                            $question_options_result = $mysqli->query($question_options_sql);
                            while($question_options_row = $question_options_result->fetch_assoc()) {
                                $stored_value = $question_options_map[$question_options_row['question_id']];
                                if (empty($stored_value)) {
                                    $stored_value = array();
                                }
                                array_push($stored_value, $question_options_row);
                                //$stored_value = $stored_value.$question_options_row['question_option_text'];
                                $question_options_map[$question_options_row['question_id']] = $stored_value;
                            }

                            // Questions List
                            $questions_sql = 'SELECT q.id as question_id,
                                                     q.text as question_text,
                                                     qt.code as question_type_code
                                                FROM questions q
                                           LEFT JOIN questionnaires qn on qn.id = q.questionnaire_id
                                           LEFT JOIN question_types qt on qt.id = q.question_type_id
                                               WHERE qn.id = 2
                                            ORDER BY q.position ASC';
                            $questions_result = $mysqli->query($questions_sql);

                            if ($questions_result->num_rows > 0) {
                                echo '<tr>
                                        <th>#</th>
                                        <th>Text</th>
                                      </tr>';
                                $counter = 1;
                                while($question_row = $questions_result->fetch_assoc()) {
                                    echo '<tr>';
                                    echo '<td>'.$counter++.'</td>';
                                    echo '<td>'.$question_row['question_text'];

                                    // Render different layouts for different question types
                                    switch($question_row['question_type_code']) {
                                        case "DATE_AND_TIME":
                                            echo ' <input type="datetime-local" name="answer-'.$question_row['question_id'].'" />';
                                            break;
                                        case "TEXT_LINE":
                                            echo ' <input type="text" name="answer-'.$question_row['question_id'].'" />';
                                            break;
                                        case "SINGLE_CHOICE":
                                            // Question Options Rendering
                                            foreach ($question_options_map[$question_row['question_id']] as $question_option_row) {
                                                $group = $question_option_row['question_id'];
                                                $value = $question_option_row['question_option_id'];
                                                $text = $question_option_row['question_option_text'];
                                                echo '<br /><input type="radio" name="'.$group.'" value="'.$value.'">'.$text;
                                            }
                                            break;
                                        default:
                                            echo '<font color="red">Unsupported Question Type: '.$question_row['question_type_code'].'</font>';
                                            break;
                                    }

                                    echo '</tr>';
                                }
                            } else {
                                echo '<tr><td colspan="2">0 results</td></tr>';
                            }
                        ?>
                    </table>
                    <input type="submit" value="Send" />

                    <? /* Body Area End */ ?>

                </td>
            </tr>
            <tr>
                <td colspan="2"><? include 'templates/page_footer.php'; ?></td>
            </tr>
        </table>
    </body>
</html>
