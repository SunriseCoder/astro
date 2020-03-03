<?php
    include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php';
    LoginDao::checkPermissionsAndRedirect([Permission::AstrologerAnswering], './');

    if (!class_exists('Tr')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/i18n.php'; }
?>
<html>
    <?
        $browser_title = Tr::trs('page.common.browserTitle', 'Astrology - Chaitanya Academy');
        $page_title = Tr::trs('page.astrologerChoose.pageTitle', 'Answer questions as an astrologer - choose answers session');

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
                                    echo '<th>'.Tr::trs('word.id', 'ID').'</th>';
                                    foreach ($questions as $question) {
                                        echo '<th>'.$question->text.'</th>';
                                    }
                                    echo '<th>'.Tr::trs('word.status', 'Status').'</th>';
                                }

                                // Table Content
                                foreach ($answerSessions as $answerSession) {
                                    if (!isset($answerSession->questionnaireId) || $answerSession->questionnaireId != $questionnaire->id) {
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
                                        echo Tr::trs('word.astrologer.yourAnswers', 'Your answers');
                                    } else if (isset($guessedSessionsMapping[$answerSession->id])) {
                                        echo Tr::trs('word.astrologer.alreadyGuessed', 'Already guessed');
                                    } else {
                                        echo '<a href="astrologer_answer.php?id='.$answerSession->id.'">'.Tr::trs('word.new', 'New').'</a>';
                                    }
                                    echo '</td>';
                                    echo '</tr>';
                                }

                                echo '</table>';
                            }
                        } else {
                            echo Tr::trs('page.astrologerChoose.noAnswersFound', 'No Answers found');
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
