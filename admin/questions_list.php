<?php
    if (!class_exists('Question')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/questions.php'; }
?>
<html>
    <?
        $browser_title = 'Chaitanya Academy - Questions';
        $page_title = 'Questions - Administration';

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
                    <? include $_SERVER["DOCUMENT_ROOT"].'/admin/templates/menu.php'; ?></td>
                <td>
                    <? include $_SERVER["DOCUMENT_ROOT"].'/templates/body_top.php'; ?>

                    <? /* Body Area Start */ ?>

                    <?php
                        $questionnaires = QuestionnaireDao::getAll();
                        $questions = QuestionDao::getAll();
                        if (count($questions) > 0) {
                            echo '<table>';
                            echo '<tr>
                                    <th>ID</th>
                                    <th>Number</th>
                                    <th>Text</th>
                                    <th>Type</th>
                                    <th>Options</th>
                                    <th>Position</th>
                                    <th>Questionnaire</th>
                                    <th>Actions</th>
                                  </tr>';
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
                                echo '<td>'.$questionnaires[$question->questionnaireId]->name.'</td>';
                                echo '<td><a href="question_edit.php?id='.$question->id.'">Edit</a></td>';
                                echo '</tr>';
                            }
                            echo '</table>';
                        } else {
                            echo '<tr><td colspan="2">0 results</td></tr>';
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
