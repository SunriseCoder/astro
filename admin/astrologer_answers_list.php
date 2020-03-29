<?php
    if (!class_exists('LoginDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php'; }
    LoginDao::checkPermissionsAndRedirect(Permission::AnswersView, './');

    if (!class_exists('ParticipantAnswerGroupDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/answers.php'; }
    if (!class_exists('HTMLRender')) { include $_SERVER["DOCUMENT_ROOT"].'/render/html.php'; }

    $browser_title = 'Chaitanya Academy - Participant Answer Groups';
    $page_title = 'Astrologer Answer Groups';
    $body_content = '';

    // Delete Participant Answer Group
    if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
        LoginDao::checkPermissionsAndRedirect(Permission::ParticipantAnswersDelete, './');
        $id = $_GET['id'];
        $error = AstrologerAnswerGroupDao::delete($id);
        if ($error) {
            $body_content .= '<font color="red">'.$error.'</font><br />';
        } else {
            $body_content .= '<font color="green">Astrologer Answer Group was successfully deleted.</font><br />';
        }
    }

    $sql = 'SELECT aag.id as id,
                   qn.id as questionnaire_id,
                   qn.name as questionnaire_name,
                   aag.user_id as user_id,
                   u.name as user_name,
                   aag.ip_address as ip_address,
                   aag.date as date,
                   COALESCE(pagc.participant_answers_count, 0) as participant_answers_count,
                   COALESCE(aagc.astrologer_answers_count, 0) as astrologer_answers_count
              FROM astrologer_answer_groups aag
         LEFT JOIN users u on u.id = aag.user_id
         LEFT JOIN questionnaires qn on qn.id = aag.questionnaire_id
         LEFT JOIN (SELECT s1aag.id as id,
                           count(1) as participant_answers_count
                      FROM astrologer_answer_groups s1aag
                      JOIN participant_answers s1pa on s1pa.group_id = s1aag.participant_answer_group_id
                  GROUP BY s1aag.id) pagc on pagc.id = aag.id
         LEFT JOIN (SELECT s2aa.group_id,
                           count(1) as astrologer_answers_count
                      FROM astrologer_answers s2aa
                  GROUP BY s2aa.group_id) aagc on aagc.group_id = aag.id
          ORDER BY aag.id DESC';
    $groups = Db::query($sql);
    if (count($groups) > 0) {
        $tableModel = new TableModel();

        // Table Header
        $tableModel->header []= ['ID', 'Questionnaire', 'User', 'IP Address', 'Date', 'Participant Answers', 'Astrologer Answers', 'Actions'];

        // Table Content
        foreach ($groups as $group) {
            $dataRow = [$group['id']];
            $dataRow []= $group['questionnaire_id'].': '.$group['questionnaire_name'];
            $dataRow []= $group['user_id'].': '.$group['user_name'];
            $dataRow []= $group['ip_address'];
            $dataRow []= $group['date'];
            $dataRow []= $group['participant_answers_count'];
            $dataRow []= $group['astrologer_answers_count'];
            $dataRow []= '<a href="astrologer_answers_view.php?id='.$group['id'].'">View</a>'.
                ' <a href="astrologer_answers_list.php?action=delete&id='.$group['id'].'"'.
                    ' onclick="return confirm(\'Are you sure to delete Astrologer Answer Group with ID: '.$group['id'].' ?\');">Delete</a>';
            $tableModel->data []= $dataRow;
        }
        $body_content .= HTMLRender::renderTable($tableModel, 'admin-table');
    } else {
        $body_content .= '0 Answer Groups found';
    }

    include $_SERVER["DOCUMENT_ROOT"].'/admin/templates/page.php';
?>
