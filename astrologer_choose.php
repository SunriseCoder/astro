<?php
    include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php';
    LoginDao::checkPermissionsAndRedirect(Permission::AstrologerAnswering, './');

    if (!class_exists('Tr')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/i18n.php'; }
    if (!class_exists('QuestionDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/questions.php'; }
    if (!class_exists('AstrologerAnswerGroupDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/answers.php'; }
    if (!class_exists('QuestionRender')) { include $_SERVER["DOCUMENT_ROOT"].'/render/questions.php'; }
    if (!class_exists('HTMLRender')) { include $_SERVER["DOCUMENT_ROOT"].'/render/html.php'; }

    $browser_title = Tr::trs('page.common.browserTitle', 'Astrology - Chaitanya Academy');
    $page_title = Tr::trs('page.astrologerChoose.pageTitle', 'Answer questions as an astrologer - choose answer group');
    $css_includes = ['/css/questions.css'];
    $style = 'body { max-width: 100%; }';
    $body_content = Tr::trs('page.astrologerChoose.text.surveyInstructions', '').'<br /><br />';

    $allQuestionnaires = QuestionnaireDao::getAll();
    $allQuestions = QuestionDao::getAllNonSecret();

    $currentUser = LoginDao::getCurrentUser();
    // Only Participant's Answers (without Astrologers')
    $participantAnswerGroups = ParticipantAnswerGroupDao::getAllWithNonSecretAnswers();
    // Already Guessed Astrology Answer Groups
    $currentUsersAnswerGroupIds = AstrologerAnswerGroupDao::getAnsweredIdsForCurrentUser();

    if (count($participantAnswerGroups) > 0) {
        foreach ($allQuestionnaires as $questionnaire) {
            // Questions for the particular Table (Questionnaire);
            $questionsMap = [];
            foreach ($allQuestions as $question) {
                if ($question->questionnaireId == $questionnaire->id) {
                    $questionsMap[$question->id] = $question;
                }
            }

            // Filtering the relevant answers for the astrologer and the questionnaire
            $astrologerAnswerGroups = [];
            foreach ($participantAnswerGroups as $answerGroup) {
                // Checking if this group is astrologer's own answers
                if ($answerGroup->userId == $currentUser->id) {
                    continue;
                }

                // Checking if the strologer has already gave answers for this group
                if (isset($currentUsersAnswerGroupIds[$answerGroup->id])) {
                    continue;
                }

                // Checking that current group is the answers for the current questionnaire
                if ($answerGroup->questionnaireId == $questionnaire->id) {
                    $astrologerAnswerGroups []= $answerGroup;
                }
            }

            $body_content .= '<h3>'.$questionnaire->name.'</h3>';
            if (count($questionsMap) > 0 && count($astrologerAnswerGroups) > 0) {
                $tableModel = new TableModel();

                // Table Header
                $headerRow = [Tr::trs('word.id', 'ID')];
                foreach ($questionsMap as $question) {
                    $headerRow []= $question->text;
                }
                $headerRow []= Tr::trs('word.table.actions', 'Actions');
                $tableModel->header []= $headerRow;

                // Table Content
                for ($i = 0; $i < count($astrologerAnswerGroups); $i++) {
                    $answerGroup = $astrologerAnswerGroups[$i];

                    // Selecting Answers mapped by Question ID
                    $answers = [];
                    foreach ($answerGroup->answers as $answer) {
                        $answers[$answer->questionId] = $answer;
                    }

                    $dataRow = [$answerGroup->id];
                    foreach ($questionsMap as $question) {
                        $answer = isset($answers[$question->id]) ? $answers[$question->id] : NULL;
                        $dataRow []= AnswerRender::renderAnswer($questionsMap, $answer);
                    }

                    // Actions
                    $dataRow []= '<a href="astrologer_answer.php?id='.$answerGroup->id.'">'.Tr::trs('word.astrologer.start', 'Start').'</a>';;
                    $tableModel->data []= $dataRow;
                }

                $body_content .= HTMLRender::renderTable($tableModel, 'questions-table');
            } else {
                $body_content .= Tr::trs('page.astrologerChoose.noAnswersFound', 'No Answers found');
            }
        }
    } else {
        $body_content .= Tr::trs('page.astrologerChoose.noAnswersFound', 'No Answers found');
    }

    include $_SERVER["DOCUMENT_ROOT"].'/templates/page.php';
?>
