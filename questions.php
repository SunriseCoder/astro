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
<p>This survey which consists of 50 simple multiple-choice questions covers several areas of your life in detail and here are a few important points to take into account while giving your answers:</p>
<ul>
    <li>We are asking for your legal birth name for this survey. We do NOT need your spiritual name since initiation name is above karma. If you have an additional name which is not in your passport but used by others to call you, then please add this name as well.</li>
    <li>Questions from 2 to 9 are about your relationship with your parents at specific time cycles. The option "Not applicable" in each question should be selected only if you cannot respond to the question because you have not yet lived till that age mentioned there or you have already experienced the loss of that parent at an earlier time such as childhood. Otherwise, please do not choose this "Not applicable" option if your answer is available in the question.</li>
    <li>Same logic above mentioned applies for the questions from 17 to 21; from 27 to 34; from 36 to 39. For example, if you are 25 years old and the question is asked for the middle or old ages, then "Not applicable" should be your answer.</li>
</ul>
<p>It is very important for us that you do not skip any question and give your honest answers since it will heavily effect the results of the project. Your answers will not be seen or examined by anyone, so you can have full trust in the confidentiality of your privacy.</p>
<p>If you get any problems or questions with the survey, feel free to contact via WhatsApp: +905534440889</p>

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
