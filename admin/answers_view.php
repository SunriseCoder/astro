<?php
    if (!isset($_GET['session_id']) || !preg_match('/^[0-9]+$/', $_GET['session_id'])) {
        Utils::redirect('/admin/answer_sessions_list.php');
    }
?>

<html>
    <?
        $browser_title = 'Chaitanya Academy - Answer Sessions';
        $page_title = 'Answer Sessions - Administration';

        include $_SERVER["DOCUMENT_ROOT"].'/admin/templates/metadata.php';
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
                    <? include $_SERVER["DOCUMENT_ROOT"].'/admin/templates/menu.php'; ?>
                </td>
                <td>
                    <? include $_SERVER["DOCUMENT_ROOT"].'/templates/body_top.php'; ?>

                    <? /* Body Area Start */ ?>

                    <table>
                        <?
                            include $_SERVER["DOCUMENT_ROOT"].'/dao/questions.php';
                            include $_SERVER["DOCUMENT_ROOT"].'/render/questions.php';

                            // Delete Answer Session
                            $answerSessionId = $_GET['session_id'];
                            $answerSessions = AnswerSessionDao::getWithAllAnswers($answerSessionId);

                            if (count($answerSessions) > 0) {
                                $answerSession = $answerSessions[$answerSessionId];
                                $originSession = $answerSession->origin;

                                // Prepare Answer Maps
                                $originAnswersByQuestions = [];
                                if (isset($originSession)) {
                                    foreach ($originSession->answers as $answer) {
                                        $originAnswersByQuestions[$answer->questionId] = $answer;
                                    }
                                }

                                $answersByQuestions = [];
                                foreach ($answerSession->answers as $answer) {
                                    $answersByQuestions[$answer->questionId] = $answer;
                                }

                                $questions = QuestionDao::getAllForAnswerSession($answerSessionId);
                                if (count($questions) > 0 ) {
                                    // Table Headers
                                    echo '<table>';
                                    echo    '<tr>';
                                    echo        '<th colspan="3">Question</th>';
                                    echo        '<th colspan="3">Participant</th>';
                                    if (isset($originSession)) {
                                        echo    '<th colspan="3">Astrologer</th>';
                                    }
                                    echo    '</tr>';
                                    echo    '<tr>';
                                    echo        '<th>ID</th><th>Text</th><th>Type</th>';
                                    echo        '<th>ID</th><th>Option</th><th>Text</th>';
                                    if (isset($originSession)) {
                                        echo    '<th>ID</th><th>Option</th><th>Text</th>';
                                    }
                                    echo    '</tr>';

                                    function renderAnswerCells($question, $answer) {
                                        echo '<td>'.$answer->id.'</td>';
                                        if (isset($answer->questionOptionId)) {
                                            $option = $question->options[$answer->questionOptionId];
                                            echo '<td>'.$option->text.'</td>';
                                        } else {
                                            echo '<td></td>';
                                        }
                                        if (!empty($answer->value)) {
                                            echo '<td>';
                                            echo $question->type->is(QuestionType::Complex) ? AnswerRender::renderComplexAnswer($question, $answer) : $answer->value;
                                            echo '</td>';
                                        } else {
                                            echo '<td></td>';
                                        }
                                    }

                                    // Table Content
                                    foreach ($questions as $question) {
                                        echo '<tr>';
                                        // Questions
                                        echo '<td>'.$question->id.'</td>';
                                        echo '<td>';
                                        if (!empty($question->number)) {
                                            echo $question->number.') ';
                                        }
                                        echo $question->text;
                                        echo '</td>';
                                        echo '<td>'.$question->type->code.'</td>';

                                        // Participant's Answers
                                        if (isset($originSession)) {
                                            if (isset($originAnswersByQuestions[$question->id])) {
                                                $answer = $originAnswersByQuestions[$question->id];
                                                renderAnswerCells($question, $answer);
                                            } else {
                                                echo '<td colspan="3"></td>';
                                            }
                                        }

                                        // Astrologer's Answers
                                        if (isset($answersByQuestions[$question->id])) {
                                            $answer = $answersByQuestions[$question->id];
                                            renderAnswerCells($question, $answer);
                                        } else {
                                            echo '<td colspan="3"></td>';
                                        }

                                        echo '</tr>';
                                    }
                                } else {
                                    echo 'No questions found for Answer Session: '.$answerSessionId;
                                }
                            } else {
                                echo 'Answer Session with ID: '.$answerSessionId.' was not found';
                            }
                        ?>
                    </table>

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
