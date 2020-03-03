<?php
    if (!class_exists('LoginDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php'; }
    LoginDao::checkPermissionsAndRedirect([Permission::QuestionsEdit], './');
?>
<html>
    <?
        $browser_title = 'Chaitanya Academy - Questions';
        $page_title = 'Question - Edit';

        $js_includes = array('js/question_edit.js');

        include $_SERVER["DOCUMENT_ROOT"].'/admin/templates/metadata.php';
    ?>

    <body>
        <table>
            <tr>
                <td colspan="2">
                    <? include $_SERVER["DOCUMENT_ROOT"].'/templates/page_top.php'; ?></td>
            </tr>
            <tr>
                <td class="menu">
                    <? include $_SERVER["DOCUMENT_ROOT"].'/admin/templates/menu.php'; ?>
                </td>
                <td>
                    <? include $_SERVER["DOCUMENT_ROOT"].'/templates/body_top.php'; ?>

                    <? /* Body Area Start */ ?>

                    <?
                        include $_SERVER["DOCUMENT_ROOT"].'/dao/questions.php';
                        // Save Question after Edit
                        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            if (isset($_POST['question_id'])) {
                                $error = QuestionDao::updateFromPost();
                            } else {
                                $error = QuestionDao::insertFromPost();
                            }

                            if (isset($error)) {
                                echo '<font color="red">Error: '.$error.'</font><br />';
                            } else {
                                echo '<font color="green">Saved Successfully</font><br />';
                            }
                        }

                        if (isset($_GET['id'])) {
                            $questionId = $_GET['id'];
                            $question = QuestionDao::getById($questionId);
                        }
                        if (isset($questionId) && !isset($question)) {
                            echo 'Question with ID: '.$questionId.' is not found';
                        } else {
                            echo '<form action="" method="post">';

                            echo '<div>Question:</div>';
                            echo '<table>';
                            echo '<tr>
                                    <th>Field</th>
                                    <th>Value</th>
                                  </tr>';

                            echo '<tr>';
                            echo '<td>ID</td>';
                            echo '<td>';
                            if (isset($question)) {
                                echo '<input type="hidden" name="question_id" value="'.$question->id.'" />'.$question->id;
                            } else {
                                echo 'New Question';
                            }
                            echo '</td>';
                            echo '</tr>';

                            // Question Number
                            $questionNumber = isset($question) ? $question->number : '';
                            echo '<tr><td>Question Number</td><td><input type="text" name="question_number" size="50" value="'.$questionNumber.'" /></td></tr>';

                            // Question Text
                            $questionText = isset($question) ? $question->text : '';
                            echo '<tr><td>Question Text</td><td><input type="text" name="question_text" size="50" value="'.$questionText.'" /></td></tr>';

                            // Question Markup
                            $questionMarkup = isset($question) ? $question->markup : '';
                            echo '<tr><td>Question Markup</td><td><textarea name="question_markup" rows="10" cols="50">'.$questionMarkup.'</textarea></td></tr>';

                            // Question Position
                            $questionPosition = isset($question) ? $question->position : '';
                            echo '<tr><td>Position in Questionnaire</td><td><input type="text" name="question_position" size="50" value="'.$questionPosition.'" /></td></tr>';

                            // Questionnaire Field
                            echo '<tr><td>Questionnaire</td><td><select name="questionnaire_id">';
                            $questionnaires = QuestionnaireDao::getAll();
                            $questionnaireId = isset($question) ? $question->questionnaireId : isset($_GET['questionnaire_id']) ? $_GET['questionnaire_id'] : '';
                            foreach ($questionnaires as $questionnaire) {
                                echo '<option value="'.$questionnaire->id.'"';
                                if ($questionnaire->id == $questionnaireId) {
                                    echo ' selected="selected"';
                                }
                                echo '>'.$questionnaire->name.'</option>';
                            }
                            echo '</select></td></tr>';

                            // Question Type
                            echo '<tr><td>Question Type</td><td>';
                            echo '<select id="question_type_select" name="question_type_id" onchange="questionTypeChanged()">';
                            $questionTypes = QuestionTypeDao::getAll();
                            foreach ($questionTypes as $questionType) {
                                echo '<option question_type_code="'.$questionType->code.'" value="'.$questionType->id.'"';
                                if (isset($question) && $question->typeId == $questionType->id) {
                                    echo ' selected="selected"';
                                }
                                echo '>'.$questionType->name.'</option>';
                            }
                            echo '</select></td></tr>';
                            echo '</table>';

                            echo '<div>Answer options:</div>';
                            echo '<table id="question_options_table">';
                            echo '<tr>
                                    <th>ID</th>
                                    <th>Text</th>
                                    <th>Position</th>
                                    <th>Actions</th>
                                  </tr>';

                            // Question Options
                            if (isset($question)) {
                                $questionType = $questionTypes[$question->typeId];
                                if (isset($questionType) && $questionType->is(QuestionType::SingleChoice)) {
                                    // QuestionsOptions List
                                    $questionOptions = QuestionOptionDao::getAllForQuestion($question->id);
                                    $questionOptionsCounter = 0;
                                    foreach ($questionOptions as $questionOption) {
                                        echo '<tr row_number="'.$questionOptionsCounter.'">';

                                        // Question Option ID
                                        $questionOptionId = $questionOption->id;
                                        $questionOptionIdName = 'question_options['.$questionOptionsCounter.'][id]';
                                        echo '<td><input type="hidden" name="'.$questionOptionIdName.'" value="'.$questionOptionId.'" />'.$questionOptionId.'</td>';

                                        // Question Option Text
                                        $questionOptionText = $questionOption->text;
                                        $questionOptionTextName = 'question_options['.$questionOptionsCounter.'][text]';
                                        echo '<td><input type="text" name="'.$questionOptionTextName.'" value="'.$questionOptionText.'" size="30" /></td>';

                                        // Question Option Position
                                        $questionOptionPosition = $questionOption->position;
                                        $questionOptionPositionName = 'question_options['.$questionOptionsCounter.'][position]';
                                        echo '<td><input type="text" name="'.$questionOptionPositionName.'" value="'.$questionOptionPosition.'" size="4" /></td>';

                                        // Question Option Actions
                                        echo '<td><input type="button" onclick="deleteAnswerOption('.$questionOptionsCounter.');" value="Delete" /></td>';
                                        echo '</tr>';
                                        $questionOptionsCounter++;
                                    }
                                }
                            }

                            echo '<tr><td colspan="4"><input type="button" onclick="addAnswerOption();" value="Add Answer Option" /></td></tr>';
                            echo '</table>';
                            echo '<input type="submit" value="Save" />';
                            echo '</form>';
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
