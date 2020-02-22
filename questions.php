<?php
    include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php';
    LoginDao::checkPermissions([], '/login.php');
?>

<html>
    <?
        $browser_title = 'Chaitanya Academy - Questions';
        $page_title = 'Survey';

        $js_includes = array('js/questions.js');

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

                    <?php
                        include $_SERVER["DOCUMENT_ROOT"].'/dao/questions.php';
                        include $_SERVER["DOCUMENT_ROOT"].'/render/questions.php';

                        // Saving Question Answers
                        $alreadyAnswered = AnswerSessionDao::hasAlreadyAnswered();
                        if ($alreadyAnswered) {
                            echo 'Sorry, but you already have taken part in the survey';
                        } else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            $errors = AnswerSessionDao::saveAnswers();
                            if ($errors) {
                                foreach ($errors as $error) {
                                    echo '<font color="red">'.$error.'</font><br />';
                                }
                            } else {
                                echo 'Thank you for participating the survey.';
                            }
                        } else {
                            ?>
                            <div>Please take a few minutes to answer the following questions.</div><br />
                            <div>GUARANTEE OF FULL CONFIDENTIALITY:<br />
                                 Your privacy is important to us.  No one, including the astrologers,<br />
                                 will see any of the personal data you give in the survey.<br />
                                 The comparison of your answers and the astrologers' answers<br />
                                 will be completely automated by the computer software.<br />
                                 So you can freely answer all the questions in confidence without concern for privacy issues.
                             </div><br />

                            <form action="questions.php" method="post">
                                <table>
                                    <?php
                                    $questions = QuestionDao::getDefaultQuestionnaire();
                                    if (count($questions) > 0) {
                                        echo '<tr><th>#</th><th>Text</th></tr>';
                                        foreach ($questions as $question) {
                                            echo '<tr><td>'.$question->number.'</td><td>';
                                            QuestionRender::renderQuestion($question);
                                            echo '</td></tr>';
                                        }
                                    } else {
                                        echo '<tr><td colspan="2">0 results</td></tr>';
                                    }
                                ?>
                                </table>
                                <input type="submit" value="Send" />
                            </form>
                            <?php
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
