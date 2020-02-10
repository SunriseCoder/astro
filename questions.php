<html>
    <?
        $browser_title = 'Chaitanya Academy - Questions';
        $page_title = 'Questions - Answer';

        include $_SERVER["DOCUMENT_ROOT"].'/templates/metadata.php';
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
                    <? include $_SERVER["DOCUMENT_ROOT"].'/templates/menu.php'; ?>
                </td>
                <td>
                    <? include $_SERVER["DOCUMENT_ROOT"].'/templates/body_top.php'; ?>

                    <? /* Body Area Start */ ?>

                    <div>Hello Dear Stranger!</div>
                    <div>Could You please answer some questions anonymously?</div>

                    <table>
                        <?
                            // Question Options Map
                            $question_options_sql =
                                'SELECT id as question_option_id,
                                        question_id as question_id,
                                        text as question_option_text
                                   FROM question_options';
                            $question_options_result = Db::query($question_options_sql);
                            foreach ($question_options_result as $question_options_row) {
                                $stored_value = $question_options_map[$question_options_row['question_id']];
                                if (empty($stored_value)) {
                                    $stored_value = array();
                                }
                                array_push($stored_value, $question_options_row);
                                $question_options_map[$question_options_row['question_id']] = $stored_value;
                            }

                            // Questions List
                            $questions_sql = 'SELECT q.id as question_id,
                                                     q.text as question_text,
                                                     qt.code as question_type_code
                                                FROM questions q
                                           LEFT JOIN questionnaires qn on qn.id = q.questionnaire_id
                                           LEFT JOIN question_types qt on qt.id = q.question_type_id
                                               WHERE qn.id = (SELECT value FROM settings WHERE code = \'DEFAULT_QUESTIONNAIRE\')
                                            ORDER BY q.position ASC';
                            $questions_result = Db::query($questions_sql);
                            if (count($questions_result) > 0) {
                                echo '<tr>
                                        <th>#</th>
                                        <th>Text</th>
                                      </tr>';
                                $counter = 1;
                                foreach ($questions_result as $question_row) {
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
                                            $question_options = $question_options_map[$question_row['question_id']];
                                            if ($question_options) {
                                                foreach ($question_options as $question_option_row) {
                                                    $group = $question_option_row['question_id'];
                                                    $value = $question_option_row['question_option_id'];
                                                    $text = $question_option_row['question_option_text'];
                                                    echo '<br /><input type="radio" name="'.$group.'" value="'.$value.'">'.$text;
                                                }
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
                <td colspan="2">
                    <? include $_SERVER["DOCUMENT_ROOT"].'/templates/page_footer.php'; ?>
                </td>
            </tr>
        </table>
    </body>
</html>
