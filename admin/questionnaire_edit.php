<?php
    if (!class_exists('Question')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/questions.php'; }
?>
<html>
    <?
        $browser_title = 'Chaitanya Academy - Questionnaires';
        $page_title = 'Questionnaire - Edit';

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

                    <?
                        // Parsing Questionnaire by given ID
                        if (isset($_GET['id'])) {
                            $questionnaireId = $_GET['id'];
                            $questionnaire = QuestionnaireDao::getById($questionnaireId);
                            if (isset($questionnaire)) {
                                echo '<div>'.$questionnaire->name.':</div>';

                                // Parsing Questions for the Questionnaire
                                $questions = QuestionDao::getAllForQuestionnaire($questionnaire->id);
                                if (count($questions) > 0) {
                                    echo '<table>';
                                    echo '<tr>
                                            <th>ID</th>
                                            <th>Number</th>
                                            <th>Text</th>
                                            <th>Type</th>
                                            <th>Options</th>
                                            <th>Position</th>
                                            <th>Actions</th>
                                          </tr>';

                                    // Questions List
                                    foreach ($questions as $question) {
                                        echo '<tr>';
                                        echo '<td>'.$question->id.'</td>';
                                        echo '<td>'.$question->number.'</td>';
                                        echo '<td>'.$question->text.'</td>';
                                        echo '<td>'.$question->type->name.'</td>';
                                        echo '<td>';
                                        foreach ($question->options as $questionOption) {
                                            echo $questionOption->text.'<br />';
                                        }
                                        echo '</td>';
                                        echo '<td>'.$question->position.'</td>';
                                        echo '<td><a href="question_edit.php?id='.$question->id.'">Edit</a></td>';
                                        echo '</tr>';
                                    }
                                    echo '</table>';
                                }
                                echo count($questions).' question(s)<br />';
                                echo '<a href="question_edit.php?questionnaire_id='.$questionnaire->id.'">Add New Question</a>';
                            } else {
                                echo 'Questionnaire with ID '.$questionnaire->id.' is not found';
                            }
                        } else {
                            echo 'Questionnaire ID is not set';
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
