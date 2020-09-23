<?php
    if (!class_exists('LoginDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php'; }
    LoginDao::checkPermissionsAndRedirect(Permission::AnswersView, './');

    if (!class_exists('ParticipantAnswerGroupDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/answers.php'; }
    if (!class_exists('AnswerEvaluationMethodDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/evaluation.php'; }
    if (!class_exists('AnswerEvaluationService')) { include $_SERVER["DOCUMENT_ROOT"].'/services/evaluation.php'; }
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

    // Evaluate missing answers
    if (isset($_GET['action']) && $_GET['action'] == 'evaluate_missing') {
        $result = AnswerEvaluationService::evaluateAllMissing();
        if ($result) {
            $body_content .= '<font color="red">'.$result.'</font>';
        }
    }

    // Evaluate missing answers form
    $body_content .= '<form action="" method="GET">
                        <input type="hidden" name="action" value="evaluate_missing" />
                        <input type="submit" value="Evaluate missing answer groups" />
                    </form>';

    $sql = 'SELECT aag.id as id,
                   qn.id as questionnaire_id,
                   qn.name as questionnaire_name,
                   pag.user_id as participant_id,
                   pu.name as participant_name,
                   aag.user_id as astrologer_id,
                   au.name as astrologer_name,
                   aag.ip_address as ip_address,
                   aag.date as date,
                   COALESCE(pagc.participant_answers_count, 0) as participant_answers_count,
                   COALESCE(aagc.astrologer_answers_count, 0) as astrologer_answers_count
              FROM astrologer_answer_groups aag
         LEFT JOIN participant_answer_groups pag on pag.id = aag.participant_answer_group_id
         LEFT JOIN users pu on pu.id = pag.user_id
         LEFT JOIN users au on au.id = aag.user_id
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
        $evaluationMethodEntities = AnswerEvaluationMethodDao::getAll();

        $evaluationGroupEntities = AnswerEvaluationGroupDao::getAll();
        $evaluationGroupEntitiesMap = [];
        foreach ($evaluationGroupEntities as $evaluationGroupEntity) {
            $evaluationGroupEntitiesMap[$evaluationGroupEntity->methodId][$evaluationGroupEntity->astrologerAnswerGroupId] = $evaluationGroupEntity;
        }

        $tableModel = new TableModel();

        // Table Header
        $headerRow1 = [['colspan' => 6, 'value' => 'General'], ['colspan' => 2, 'value' => 'Answers amount']];
        if (count($evaluationMethodEntities) > 0) {
            $headerRow1 []= ['colspan' => count($evaluationMethodEntities), 'value' => 'Evaluation methods'];
        }
        $headerRow1 []= ['rowspan' => 2, 'value' => 'Actions'];

        $headerRow2 = ['ID', 'Questionnaire', 'Participant', 'Astrologer', 'IP Address', 'Date', 'Participant', 'Astrologer'];
        foreach ($evaluationMethodEntities as $evaluationMethodEntity) {
            $headerRow2 []= ['value' => $evaluationMethodEntity->code, 'tooltip' => $evaluationMethodEntity->description];
        }
        $tableModel->header = [$headerRow1, $headerRow2];

        // Table Content
        foreach ($groups as $group) {
            $dataRow = [$group['id']];
            $dataRow []= $group['questionnaire_id'].': '.$group['questionnaire_name'];
            $dataRow []= $group['participant_id'].': '.$group['participant_name'];
            $dataRow []= $group['astrologer_id'].': '.$group['astrologer_name'];
            $dataRow []= $group['ip_address'];
            $dataRow []= $group['date'];
            $dataRow []= $group['participant_answers_count'];
            $dataRow []= $group['astrologer_answers_count'];
            foreach ($evaluationMethodEntities as $evaluationMethodEntity) {
                if (!empty($evaluationGroupEntitiesMap[$evaluationMethodEntity->id][$group['id']])) {
                    $score = $evaluationGroupEntitiesMap[$evaluationMethodEntity->id][$group['id']]->score;
                    $dataRow []= $score;
                } else {
                    $dataRow []= '<font color="red">N/A</font>';
                }
            }
            $dataRow []= '<a href="astrologer_answers_view.php?id='.$group['id'].'">View</a>'.
                ' <a href="astrologer_answers_evaluation.php?id='.$group['id'].'">Evaluate</a>'.
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
