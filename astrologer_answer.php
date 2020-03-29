<?php
    if (!class_exists('LoginDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php'; }
    LoginDao::checkPermissionsAndRedirect(Permission::AstrologerAnswering, './');

    if (!class_exists('Tr')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/i18n.php'; }
    if (!class_exists('QuestionDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/questions.php'; }
    if (!class_exists('AstrologerAnswerGroupDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/answers.php'; }
    if (!class_exists('QuestionRender')) { include $_SERVER["DOCUMENT_ROOT"].'/render/questions.php'; }
    if (!class_exists('HTMLRender')) { include $_SERVER["DOCUMENT_ROOT"].'/render/html.php'; }

    if ($_SERVER['REQUEST_METHOD'] == 'GET' && (!isset($_GET['id']) || !preg_match('/^[0-9]+$/', $_GET['id']))) {
        Utils::redirect('/astrologer_choose.php');
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && (!isset($_POST['id']) || !preg_match('/^[0-9]+$/', $_POST['id']))) {
        Utils::redirect('/astrologer_choose.php');
    }

    $browser_title = Tr::trs('page.common.browserTitle', 'Astrology - Chaitanya Academy');
    $page_title = Tr::trs('page.astrologerAnswers.pageTitle', 'Answer questions as an Astrologer');
    $css_includes = ['/css/questions.css'];
    $js_includes = ['/js/questions.js'];
    $body_content = '';

    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $astrologerAnswerGroupId = $_GET['id'];

        // Checking that ParticipantAnswerGroup exists
        $astrologerAnswerGroups = ParticipantAnswerGroupDao::getWithNonSecretAnswers($astrologerAnswerGroupId);
        if (count($astrologerAnswerGroups) == 0) {
            $body_content .= Tr::format('page.astrologerAnswers.error.participantAnswerGroupNotFound', [$astrologerAnswerGroupId],
                'Participant Answer Group with ID: {0} was not found');
        } else {
            $answerGroup = $astrologerAnswerGroups[$astrologerAnswerGroupId];

            // Check that current Astrologer has not processed this Participant Answer Group yet
            $alreadyAnswered = AstrologerAnswerGroupDao::hasCurrentAstrologerAnsweredAlready($astrologerAnswerGroupId);
            if ($alreadyAnswered) {
                $body_content .= Tr::format('page.astrologerAnswers.error.alreadyAnswered', [$astrologerAnswerGroupId],
                    'Sorry, but you have already gave answers for Participant Answer Group with ID: {0}');
            } else {
                // Check that the Astrologer doesn't guess his own answers
                if (isset($answerGroup) && $answerGroup->userId == LoginDao::getCurrentUser()->id) {
                    $body_content .= Tr::format('page.astrologerAnswers.error.ownAnswers', [$astrologerAnswerGroupId],
                        'Sorry, but the Participant Answer Group with ID: {0} consists of your own answers and you cannot guess them');
                } else {
                    // Astrologer Questions Table
                    $body_content .= Tr::trs('page.astrologerAnswers.text.surveyInstructions', '');
                    if (count($astrologerAnswerGroups) > 0) {
                        // Participant's birth data (non-secret answers)
                        $body_content .= '<h3>'.Tr::trs('page.astrologerAnswers.participantsPublicData', 'Participant\'s birth data').':</h3>';

                        $questionsMap = QuestionDao::getAllForQuestionnaire($answerGroup->questionnaireId);
                        if (count($questionsMap) > 0) {
                            $tableModel = new TableModel();
                            // Table Header
                            $tableModel->header []= [Tr::trs('word.question', 'Question'), Tr::trs('word.answer', 'Answer')];

                            // Selecting Answers mapped by Question ID
                            $answers = [];
                            $answerGroup = $astrologerAnswerGroups[$astrologerAnswerGroupId];
                            foreach ($answerGroup->answers as $answer) {
                                $answers[$answer->questionId] = $answer;
                            }

                            // Table Content
                            $questions = array_values($questionsMap);
                            for ($i = 0; $i < count($questions); $i++) {
                                $question = $questions[$i];
                                if ($question->secret) {
                                    continue;
                                }

                                $answer = isset($answers[$question->id]) ? $answers[$question->id] : NULL;
                                $value = AnswerRender::renderAnswer($questionsMap, $answer);
                                $tableModel->data []= [$question->text, $value];
                            }
                        }
                        $body_content .= HTMLRender::renderTable($tableModel, 'questions-table');

                        // Participant's private data (secret answers)
                        $body_content .= '<h3>'.Tr::trs('page.astrologerAnswers.participantsPrivateData', 'Participant\'s private data').':</h3>';
                        if (count($questionsMap) > 0) {
                            $body_content .= '<form action="astrologer_answer.php" method="post">';
                            $body_content .= '<input type="hidden" name="id" value="'.$astrologerAnswerGroupId.'" />';

                            $tableModel = new TableModel();
                            $tableModel->header []= [Tr::trs('word.question.numberShort', '#'), Tr::trs('word.question.text', 'Text')];
                            foreach ($questionsMap as $question) {
                                if (!$question->secret) {
                                    continue;
                                }
                                $tableModel->data []= [$question->number, QuestionRender::renderQuestion($question)];
                            }
                            $tableModel->data []= [['align' => 'center', 'colspan' => 2, 'value' => '<input type="submit" value="'.Tr::trs('word.send', 'Send').'" />']];
                            $body_content .= HTMLRender::renderTable($tableModel, 'questions-table');
                            $body_content .= '</form>';
                        } else {
                            $body_content .= Tr::trs('page.astrologerAnswers.message.noQuestionsFound', 'No questions found');
                        }
                    }
                }
            }
        }
    } else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Saving Astrologer's Answers
        $error = AstrologerAnswerGroupDao::saveAnswers();
        if ($error) {
            $body_content .= '<font color="red">'.$error.'</font><br />';
        } else {
            $message = Tr::trs('page.astrologerAnswers.answeredSuccessfully', 'Your answers has been sent, thank you very much');
            $body_content .= '<font color="green">'.$message.'</font>';
        }
    }

    include $_SERVER["DOCUMENT_ROOT"].'/templates/page.php';
?>
