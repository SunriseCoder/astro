<?php
    include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php';

    LoginDao::autologin();

    if (!LoginDao::isLogged()) {
        Utils::redirect('login.php');
    }
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
                        include $_SERVER["DOCUMENT_ROOT"].'/utils/json.php';

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
                                        echo '<tr>
                                                <th>#</th>
                                                <th>Text</th>
                                              </tr>';
                                        $counter = 1;
                                        $prefix = '';

                                        function renderQuestion($question, $prefix) {
                                            echo '<tr>';
                                            echo '<td>'.$question->number.'</td>';
                                            echo '<td>';

                                            // Render different layouts for different question types
                                            switch($question->type->code) {
                                                case QuestionType::DateAndTime:
                                                    echo $question->text.' <input type="datetime-local" name="answer-'.$prefix.$question->id.'" />';
                                                    break;
                                                case QuestionType::TextLine:
                                                    echo $question->text.' <input type="text" name="answer-'.$prefix.$question->id.'" />';
                                                    break;
                                                case QuestionType::SingleChoice:
                                                    // Question Options Rendering
                                                    if ($question->options) {
                                                        echo $question->text;
                                                        foreach ($question->options as $questionOption) {
                                                            $group = 'answer-'.$prefix.$question->id;
                                                            $value = $questionOption->id;
                                                            $text = $questionOption->text;
                                                            echo '<br /><input type="radio" name="'.$group.'" value="'.$value.'">'.$text;
                                                        }
                                                    }
                                                    break;
                                                case QuestionType::Complex:
                                                    // Very complex Question Rendering
                                                    $metadata = Json::decode($question->text);
                                                    $questionText = $metadata->text;
                                                    $isArray = $metadata->array;
                                                    $addEntryText = $metadata->addEntryText;
                                                    $subQuestions = $metadata->subQuestions;

                                                    echo $questionText;

                                                    // If the Complex Question has multiple entries, adding Entry by JavaScript
                                                    if ($isArray) {
                                                        echo '<div id="questionRoot-'.$question->id.'"></div>';
                                                        echo ' <button type="button" onclick="addQuestionEntry('.$question->id.')">'.$addEntryText.'</button>';
                                                        echo '<script>';
                                                        echo 'var subQuestions = [];';
                                                        foreach ($subQuestions as $subQuestion) {
                                                            echo 'var subQuestionStr = \''.Json::encode($subQuestion).'\';';
                                                            echo 'var subQuestion = JSON.parse(subQuestionStr);';
                                                            echo 'subQuestions.push(subQuestion);';
                                                        }
                                                        echo 'complexQuestions['.$question->id.'] = subQuestions;';
                                                        echo '</script>';
                                                    } else {
                                                        // Otherwise render just one Entry via PHP
                                                        echo '<table>';
                                                        $prefix = $question->id.'-';
                                                        foreach ($subIds as $subId) {
                                                            $subQuestion = QuestionDao::get($subId)[0];
                                                            renderQuestion($subQuestion, $prefix);
                                                        }
                                                        echo '</table>';
                                                    }
                                                    break;
                                                default:
                                                    echo '<font color="red">Unsupported Question Type: '.$question->type->code.'</font>';
                                                    var_dump($question);
                                                    break;
                                            }
                                            echo '</td></tr>';
                                        }

                                        foreach ($questions as $question) {
                                            renderQuestion($question, $prefix);
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
