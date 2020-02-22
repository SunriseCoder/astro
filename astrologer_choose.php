<?php
    include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php';
    LoginDao::checkPermissions([Permission::AstrologerAnswering], '/');
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

                        $allQuestionnaires = QuestionnaireDao::getAll();
                        $allQuestions = QuestionDao::getAllNonSecret();

                        $currentUser = LoginDao::getCurrentUser();
                        // Only Participant's Answers (without Astrologers')
                        $answerSessions = AnswerSessionDao::getAllOriginsWithNonSecretAnswers();

                        // Already Guessed Answer Sessions
                        $guessedSessionsMapping = AnswerSessionDao::getGuessedIdsForCurrentUser();

                        if (count($answerSessions) > 0) {
                            foreach ($allQuestionnaires as $questionnaire) {
                                echo $questionnaire->name;
                                echo '<table>';

                                // Questions for the particular Table (Questionnaire);
                                // Table Header
                                $questions = [];
                                foreach ($allQuestions as $question) {
                                    if ($question->questionnaireId == $questionnaire->id) {
                                        $questions[$question->id] = $question;
                                    }
                                }
                                if (count($questions) > 0) {
                                    echo '<th>ID</th>';
                                    foreach ($questions as $question) {
                                        echo '<th>'.$question->text.'</th>';
                                    }
                                    echo '<th>Status</th>';
                                }

                                // Table Content
                                foreach ($answerSessions as $answerSession) {
                                    if ($answerSession->questionnaireId != $questionnaire->id) {
                                        continue;
                                    }

                                    // Selecting Answers mapped by Question ID
                                    $answers = [];
                                    foreach ($answerSession->answers as $answer) {
                                        $answers[$answer->questionId] = $answer;
                                    }

                                    echo '<tr>';
                                    echo '<td>'.$answerSession->id.'</td>';
                                    foreach ($questions as $question) {
                                        $answer = isset($answers[$question->id]) ? $answers[$question->id] : NULL;
                                        $value = AnswerRender::renderAnswer($questions, $answer);
                                        echo '<td>'.$value.'</td>';
                                    }

                                    // Status
                                    echo '<td>';
                                    // Check that the Astrologer doesn't guess his own answers
                                    if ($answerSession->userId == $currentUser->id) {
                                        echo 'Your answers';
                                    } else if (isset($guessedSessionsMapping[$answerSession->id])) {
                                        echo 'Already guessed';
                                    } else {
                                        echo '<a href="astrologer_answer.php?id='.$answerSession->id.'">New</a>';
                                    }
                                    echo '</td>';
                                    echo '</tr>';
                                }

                                echo '</table>';
                            }
                        } else {
                            echo 'No Answers found';
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
