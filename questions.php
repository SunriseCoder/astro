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

                    <form action="questions.php" method="post">
                        <table>
                            <?php
                                include $_SERVER["DOCUMENT_ROOT"].'/dao/questions.php';

                                $questions = QuestionDao::getDefaultQuestionnaire();
                                if (count($questions) > 0) {
                                    echo '<tr>
                                            <th>#</th>
                                            <th>Text</th>
                                          </tr>';
                                    $counter = 1;
                                    foreach ($questions as $question) {
                                        echo '<tr>';
                                        echo '<td>'.$counter++.'</td>';
                                        echo '<td>'.$question->text;

                                        // Render different layouts for different question types
                                        switch($question->type->code) {
                                            case "DATE_AND_TIME":
                                                echo ' <input type="datetime-local" name="answer-'.$question->id.'" />';
                                                break;
                                            case "TEXT_LINE":
                                                echo ' <input type="text" name="answer-'.$question->id.'" />';
                                                break;
                                            case "SINGLE_CHOICE":
                                                // Question Options Rendering
                                                if ($question->options) {
                                                    foreach ($question->options as $questionOption) {
                                                        $group = $question->id;
                                                        $value = $questionOption->id;
                                                        $text = $questionOption->text;
                                                        echo '<br /><input type="radio" name="'.$group.'" value="'.$value.'">'.$text;
                                                    }
                                                }
                                                break;
                                            default:
                                                echo '<font color="red">Unsupported Question Type: '.$question->type->code.'</font>';
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
                    </form>

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
