<?php
    if (!class_exists('LoginDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php'; }
    if (!class_exists('Tr')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/i18n.php'; }

    LoginDao::checkPermissionsAndRedirect([Permission::AstrologerAnswering], './');

    if ($_SERVER['REQUEST_METHOD'] == 'GET' && (!isset($_GET['id']) || !preg_match('/^[0-9]+$/', $_GET['id']))) {
        Utils::redirect('/astrologer_choose.php');
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && (!isset($_POST['id']) || !preg_match('/^[0-9]+$/', $_POST['id']))) {
        Utils::redirect('/astrologer_choose.php');
    }
?>
<html>
    <?
        $browser_title = Tr::trs('page.common.browserTitle', 'Astrology - Chaitanya Academy');
        $page_title = Tr::trs('page.astrologerAnswers.pageTitle', 'Answer questions as an Astrologer');

        include $_SERVER["DOCUMENT_ROOT"].'/templates/metadata.php';
    ?>

    <body>
        <table id="page-markup-table">
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

                        // TODO Rewrite these if-statements with a better way
                        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
                            $answerSessionId = $_GET['id'];
                            $answerSession = AnswerSessionDao::get($answerSessionId);
                            // Check that current Astrologer has not processed this session yet
                            $alreadyAnswered = AnswerSessionDao::hasCurrentAstrologerAnsweredAlready($answerSessionId);
                        }

                        // TODO Rewrite these if-statements with a better way
                        if (isset($alreadyAnswered) && $alreadyAnswered) {
                            echo Tr::format('page.astrologerAnswers.error.alreadyAnswered', [$answerSessionId],
                                'Sorry, but you already have answered Session with ID: {0}');
                        // Check that the Astrologer doesn't guess his own answers
                        } else if (isset($answerSession->originId)) {
                            echo Tr::format('page.astrologerAnswers.error.notParticipantAnswers', [$answerSessionId],
                                'Answer Session with ID: {0} is not a Participant\'s answers, but an Astrologer\'s answers');
                        } else if (isset($answerSession) && $answerSession->userId == LoginDao::getCurrentUser()->id) {
                            echo Tr::format('page.astrologerAnswers.error.ownAnswers', [$answerSessionId],
                                'Sorry, but AnswerSession with ID: {0} is your own answers and you cannot guess them');
                        } else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                            // Saving Astrologer's Answers
                            $error = AnswerSessionDao::saveAnswers();
                            if ($error) {
                                echo '<font color="red">'.$error.'</font><br />';
                            } else {
                                $message = Tr::trs('page.astrologerAnswers.answeredSuccessfully', 'Your answers has been sent, thank you very much');
                                echo '<font color="green">'.$message.'</font>';
                            }
                        } else {
                            $answerSessions = AnswerSessionDao::getWithNonSecretAnswers($answerSessionId);

                            echo Tr::trs('page.astrologerAnswers.text.surveyInstructions', '');

                            if (count($answerSessions) > 0) {
                                // Non-secret Answers Table
                                echo '<h3>'.Tr::trs('page.astrologerAnswers.participantsPublicData', 'Participant\'s birth data').':</h3>';
                                echo '<table>';

                                // Table Header
                                $questions = QuestionDao::getForAnswerSession($answerSessionId, FALSE);
                                if (count($questions) > 0) {
                                    echo '<th>'.Tr::trs('word.question', 'Question').'</th>';
                                    echo '<th>'.Tr::trs('word.answer', 'Answer').'</th>';

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
                                echo '<h3>'.Tr::trs('page.astrologerAnswers.participantsPrivateData', 'Participant\'s private data').':</h3>';
                                $questions = QuestionDao::getForAnswerSession($answerSessionId, TRUE);
                                if (count($questions) > 0) {
                                    echo '<table>';
                                    echo '<tr>';
                                    echo '<th>'.Tr::trs('word.question.numberShort', '#').'</th>';
                                    echo '<th>'.Tr::trs('word.question.text', 'Text').'</th>';
                                    echo '</tr>';
                                    foreach ($questions as $question) {
                                        echo '<tr><td>'.$question->number.'</td><td>';
                                        echo QuestionRender::renderQuestion($question);
                                        echo '</td></tr>';
                                    }
                                    echo '</table>';
                                } else {
                                    echo Tr::trs('page.astrologerAnswers.message.noQuestionsFound', 'No questions found');
                                }

                                echo '<input type="submit" value="'.Tr::trs('word.send', 'Send').'" />';
                                echo '</form>';
                            } else {
                                echo Tr::format('page.astrologerAnswers.error.invalidSessionId', [$answerSessionId], 'Invalid Answer Session ID: {0}');
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
