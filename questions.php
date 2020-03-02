<?php
    include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php';
    LoginDao::checkPermissions([], '/login.php');

    if (!class_exists('Tr')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/i18n.php'; }
    if (!class_exists('Question')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/questions.php'; }
    if (!class_exists('QuestionRender')) { include $_SERVER["DOCUMENT_ROOT"].'/render/questions.php'; }
?>

<html>
    <?
        $browser_title = Tr::trs('page.common.browserTitle', 'Astrology - Chaitanya Academy');
        $page_title = Tr::trs('page.questions.pageTitle', 'Survey');

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
                        // Saving Question Answers
                        $alreadyAnswered = AnswerSessionDao::hasAlreadyAnswered();
                        if ($alreadyAnswered) {
                            echo Tr::trs('page.questions.error.alreadyAnswered', 'Sorry, but you already have taken part in the survey');
                        } else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            $error = AnswerSessionDao::saveAnswers();
                            if ($error) {
                                echo '<font color="red">'.$error.'</font><br />';
                            } else {
                                echo Tr::trs('page.questions.message.successfullyComplete', 'Thank you for participating in the survey');
                            }
                        } else {
                            echo Tr::trs('page.questions.text.surveyInstructions');
                            ?>
                            <form action="questions.php" method="post">
                                    <?php
                                    $questions = QuestionDao::getDefaultQuestionnaire();
                                    if (count($questions) > 0) {
                                        echo '<table>';
                                        echo '<tr>';
                                        echo '<th>'.Tr::trs('word.question.numberShort', '#').'</th>';
                                        echo '<th>'.Tr::trs('word.question.text', 'Text').'</th>';
                                        echo '</tr>';
                                        foreach ($questions as $question) {
                                            echo '<tr><td>'.$question->number.'</td><td>';
                                            QuestionRender::renderQuestion($question);
                                            echo '</td></tr>';
                                        }
                                        echo '</table>';
                                    } else {
                                        echo Tr::trs('page.questions.noQuestions', 'No questions');
                                    }
                                ?>
                                <input type="submit" value="<?php echo Tr::trs('word.send', 'Send'); ?>" />
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
