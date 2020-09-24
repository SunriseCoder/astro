<?php
    if (!class_exists('LoginDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php'; }
    LoginDao::checkPermissionsAndRedirect(Permission::AnswersView, './');

    if (!class_exists('ParticipantAnswerGroupDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/answers.php'; }
    if (!class_exists('HTMLRender')) { include $_SERVER["DOCUMENT_ROOT"].'/render/html.php'; }

    $browser_title = 'Chaitanya Academy - Participant Answer Groups';
    $page_title = 'Participant Answer Groups';
    $body_content = '';

    // Delete Participant Answer Group
    if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
        LoginDao::checkPermissionsAndRedirect(Permission::ParticipantAnswersDelete, './');
        $id = $_GET['id'];
        $error = ParticipantAnswerGroupDao::delete($id);
        if ($error) {
            $body_content .= '<font color="red">'.$error.'</font>';
        } else {
            $body_content .= '<font color="green">Participant Answer Group was successfully deleted.</font>';
        }
    }

    $sql = 'SELECT pag.id as id,
                   qn.id as questionnaire_id,
                   qn.name as questionnaire_name,
                   pag.user_id as user_id,
                   u.name as user_name,
                   pag.ip_address as ip_address,
                   pag.date as date,
                   pag.survey_language_id as survey_language_id,
                   sl.name_english as survey_language_name_english,
                   COALESCE(pac.participant_answers_count, 0) as participant_answers_count,
                   COALESCE(aagc.astrologer_answer_groups_count, 0) as astrologer_answer_groups_count
              FROM participant_answer_groups pag
         LEFT JOIN users u on u.id = pag.user_id
         LEFT JOIN questionnaires qn on qn.id = pag.questionnaire_id
         LEFT JOIN i18n_languages sl on sl.id = pag.survey_language_id
         LEFT JOIN (SELECT group_id,
                           COUNT(1) as participant_answers_count
                      FROM participant_answers
                  GROUP BY group_id) pac on pac.group_id = pag.id
         LEFT JOIN (SELECT participant_answer_group_id as id,
                           count(1) as astrologer_answer_groups_count
                      FROM astrologer_answer_groups
                  GROUP BY participant_answer_group_id) aagc on aagc.id = pag.id
          ORDER BY pag.id DESC';
    $groups = Db::query($sql);
    if (count($groups) > 0) {
        $tableModel = new TableModel();

        // Table Header
        $tableModel->header = [['ID', 'Questionnaire', 'User', 'IP Address', 'Date', 'Language', 'Participant Answers', 'Astrologer Answer Groups', 'Actions']];

        // Table Content
        foreach ($groups as $group) {
            $actions = '<a href="participant_answers_view.php?group_id='.$group['id'].'">View</a>';
            if ($group['astrologer_answer_groups_count'] == 0 && LoginDao::checkPermissions(Permission::ParticipantAnswersDelete)) {
                $actions .= ' <a href="participant_answers_list.php?action=delete&id='.$group['id'].'"
                    onclick="return confirm(\'Are you sure to delete Participant Answer Group with ID: '.$group['id'].' ?\');">Delete</a>';
            }
            $tableModel->data []= [$group['id'], $group['questionnaire_id'].': '.$group['questionnaire_name'], $group['user_id'].': '.$group['user_name'],
                $group['ip_address'], $group['date'], $group['survey_language_id'].': '.$group['survey_language_name_english'],
                $group['participant_answers_count'], $group['astrologer_answer_groups_count'], $actions];
        }

        $body_content .= HTMLRender::renderTable($tableModel, 'admin-table');
    } else {
        $body_content .= '0 Answer Groups found';
    }

    include $_SERVER["DOCUMENT_ROOT"].'/admin/templates/page.php';
?>
