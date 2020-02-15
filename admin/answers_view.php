<?php
    if (!isset($_GET['session_id']) || !preg_match('/^[0-9]+$/', $_GET['session_id'])) {
        Utils::redirect('admin/answer_sessions_list.php');
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

                            // Delete Answer Session
                            $id = $_GET['session_id'];

                            $sql = "SELECT a.id as id,
                                           q.number as question_number,
                                           q.text as question_text,
                                           qt.name as question_type_name,
                                           qo.text as question_option_text,
                                           a.value as answer_value
                                      FROM questionnaires qn
                                 LEFT JOIN questions q on q.questionnaire_id = qn.id
                                 LEFT JOIN answers a on a.question_id = q.id
                                 LEFT JOIN question_options qo on qo.id = a.question_option_id
                                 LEFT JOIN question_types qt on qt.id = q.question_type_id
                                 LEFT JOIN answer_sessions ast on ast.id = a.session_id
                                     WHERE qn.id = (SELECT questionnaire_id FROM answer_sessions WHERE id = ?)
                                  ORDER BY q.position ASC";
                            $answers = Db::prepQuery($sql, 'i', [$id]);
                            if (count($answers) == 0) {
                                echo 'No Answers found';
                            }
                            if (count($answers) > 0) {
                                echo '<tr>
                                        <th>ID</th>
                                        <th>Question</th>
                                        <th>Type</th>
                                        <th>Option Text</th>
                                        <th>Value</th>
                                      </tr>';
                                foreach ($answers as $row) {
                                    echo '<tr>';
                                    echo '<td>'.($row['id'] ? $row['id'] : '<font color="red"><b>! empty !</b></font>').'</td>';
                                    echo '<td>'.($row['question_number'] ? $row['question_number'].') ' : '').$row['question_text'].'</td>';
                                    echo '<td>'.$row['question_type_name'].'</td>';
                                    echo '<td>'.$row['question_option_text'].'</td>';
                                    echo '<td>'.$row['answer_value'].'</td>';
                                    echo '</tr>';
                                }
                            } else {
                                echo '<tr><td colspan="3">0 results</td></tr>';
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
