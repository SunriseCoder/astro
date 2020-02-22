<?php
    include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php';
    LoginDao::checkPermissions([Permission::AstrologerAnswering], '/');
    if ($_SERVER['REQUEST_METHOD'] == 'GET' && (!isset($_GET['id']) || !preg_match('/^[0-9]+$/', $_GET['id']))) {
        Utils::redirect('/astrologer_choose.php');
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && (!isset($_POST['id']) || !preg_match('/^[0-9]+$/', $_POST['id']))) {
        Utils::redirect('/astrologer_choose.php');
    }
?>
<html>
    <?
        $browser_title = 'Chaitanya Academy - Astrology';
        $page_title = 'Answer questions as an Astrologer';

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

                        $answerSessionId = $_GET['id'];
                        $answerSession = AnswerSessionDao::get($answerSessionId);
                        // Check that current Astrologer has not processed this session yet
                        $alreadyAnswered = AnswerSessionDao::hasCurrentAstrologerAnsweredAlready($answerSessionId);
                        if ($alreadyAnswered) {
                            echo 'Sorry, but you already have taken part in the survey';
                        // Check that the Astrologer doesn't guess his own answers
                        } else if (isset($answerSession->originId)) {
                            echo 'This is not a participant\'s answers';
                        } else if ($answerSession->userId == LoginDao::getCurrentUser()->id) {
                            echo 'Sorry, but you can not guess your own answers';
                        } else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                            // Saving Astrologer's Answers
                            $error = AnswerSessionDao::saveAnswers();
                            if ($error) {
                                echo '<font color="red">'.$error.'</font><br />';
                            } else {
                                echo '<font color="green">Your answers has been sent, thank you very much</font>';
                            }
                        } else {
                            $answerSessions = AnswerSessionDao::getWithNonSecretAnswers($answerSessionId);

                            if (count($answerSessions) > 0) {
                                echo '<h3>Participant\'s birth data:</h3>';
                                echo '<table>';

                                // Table Header
                                $questions = QuestionDao::getForAnswerSession($answerSessionId, FALSE);
                                if (count($questions) > 0) {
                                    echo '<th>Question</th>';
                                    echo '<th>Answer</th>';

                                    // Table Content
                                    $answerSession = $answerSessions[$answerSessionId];

                                    // Selecting Answers mapped by Question ID
                                    $answers = [];
                                    foreach ($answerSession->answers as $answer) {
                                        $answers[$answer->questionId] = $answer;
                                    }

                                    foreach ($questions as $question) {
                                        echo '<tr>';
                                        echo '<td>'.$question->text.'</td>';
                                        $answer = isset($answers[$question->id]) ? $answers[$question->id] : NULL;
                                        $value = AnswerRender::renderAnswer($questions, $answer);
                                        echo '<td>'.$value.'</td>';
                                    }
                                    echo '</tr>';

                                }
                                echo '</table>';


                                echo '<form action="astrologer_answer.php" method="post">';
                                echo '<input type="hidden" name="id" value="'.$answerSessionId.'" />';

                                // Secret Answers Table
                                echo '<h3>Participant\'s private data:</h3>';
                                $questions = QuestionDao::getForAnswerSession($answerSessionId, TRUE);
                                if (count($questions) > 0) {
                                    echo '<table>';
                                    echo '<tr><th>#</th><th>Text</th></tr>';
                                    foreach ($questions as $question) {
                                        echo '<tr><td>'.$question->number.'</td><td>';
                                        QuestionRender::renderQuestion($question);
                                        echo '</td></tr>';
                                    }
                                    echo '</table>';
                                } else {
                                    echo 'No questions found';
                                }

                                echo '<input type="submit" value="Send" />';
                                echo '</form>';
                            } else {
                                echo 'Invalid Answer Session ID: '.$answerSessionId;
                            }
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
