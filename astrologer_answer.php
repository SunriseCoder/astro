<?php
    if (!class_exists('LoginDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php'; }
    if (!class_exists('Tr')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/i18n.php'; }
    if (!class_exists('QuestionDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/questions.php'; }
    if (!class_exists('QuestionRender')) { include $_SERVER["DOCUMENT_ROOT"].'/render/questions.php'; }

    LoginDao::checkPermissionsAndRedirect(Permission::AstrologerAnswering, './');

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

    // TODO Rewrite these if-statements with a better way
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $answerSessionId = $_GET['id'];
        $answerSession = AnswerSessionDao::get($answerSessionId);
        // Check that current Astrologer has not processed this session yet
        $alreadyAnswered = AnswerSessionDao::hasCurrentAstrologerAnsweredAlready($answerSessionId);
    }

    // TODO Rewrite these if-statements with a better way
    if (isset($alreadyAnswered) && $alreadyAnswered) {
        $body_content .= Tr::format('page.astrologerAnswers.error.alreadyAnswered', [$answerSessionId],
            'Sorry, but you already have answered Session with ID: {0}');
    // Check that the Astrologer doesn't guess his own answers
    } else if (isset($answerSession->originId)) {
        $body_content .= Tr::format('page.astrologerAnswers.error.notParticipantAnswers', [$answerSessionId],
            'Answer Session with ID: {0} is not a Participant\'s answers, but an Astrologer\'s answers');
    } else if (isset($answerSession) && $answerSession->userId == LoginDao::getCurrentUser()->id) {
        $body_content .= Tr::format('page.astrologerAnswers.error.ownAnswers', [$answerSessionId],
            'Sorry, but AnswerSession with ID: {0} is your own answers and you cannot guess them');
    } else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Saving Astrologer's Answers
        $error = AnswerSessionDao::saveAnswers();
        if ($error) {
            $body_content .= '<font color="red">'.$error.'</font><br />';
        } else {
            $message = Tr::trs('page.astrologerAnswers.answeredSuccessfully', 'Your answers has been sent, thank you very much');
            $body_content .= '<font color="green">'.$message.'</font>';
        }
    } else {
        $answerSessions = AnswerSessionDao::getWithNonSecretAnswers($answerSessionId);

        $body_content .= Tr::trs('page.astrologerAnswers.text.surveyInstructions', '');

        if (count($answerSessions) > 0) {
            // Non-secret Answers Table
            $body_content .= '<h3>'.Tr::trs('page.astrologerAnswers.participantsPublicData', 'Participant\'s birth data').':</h3>';
            $body_content .= '<table class="questions-table">';

            // Table Header
            $questionsMap = QuestionDao::getForAnswerSession($answerSessionId, FALSE);
            if (count($questionsMap) > 0) {
                $body_content .= '<th class="table-top-left">'.Tr::trs('word.question', 'Question').'</th>';
                $body_content .= '<th class="table-top-right">'.Tr::trs('word.answer', 'Answer').'</th>';

                // Table Content
                $answerSession = $answerSessions[$answerSessionId];

                // Selecting Answers mapped by Question ID
                $answers = [];
                foreach ($answerSession->answers as $answer) {
                    $answers[$answer->questionId] = $answer;
                }

                $questions = array_values($questionsMap);
                for ($i = 0; $i < count($questions); $i++) {
                    $question = $questions[$i];
                    $answer = isset($answers[$question->id]) ? $answers[$question->id] : NULL;
                    $value = AnswerRender::renderAnswer($questionsMap, $answer);
                    $body_content .= '<tr>';
                    if ($i < count($questions) - 1) {
                        // Not last row
                        $body_content .= '<td class="table-middle-left">'.$question->text.'</td>';
                        $body_content .= '<td class="table-middle-middle">'.$value.'</td>';
                    } else {
                        // Last row
                        $body_content .= '<td class="table-bottom-left">'.$question->text.'</td>';
                        $body_content .= '<td class="table-bottom-right">'.$value.'</td>';
                    }
                }
                $body_content .= '</tr>';

            }
            $body_content .= '</table>';


            // Secret Answers Table
            $body_content .= '<h3>'.Tr::trs('page.astrologerAnswers.participantsPrivateData', 'Participant\'s private data').':</h3>';
            $questionsMap = QuestionDao::getForAnswerSession($answerSessionId, TRUE);
            if (count($questionsMap) > 0) {
                $body_content .= '<form action="astrologer_answer.php" method="post">';
                $body_content .= '<input type="hidden" name="id" value="'.$answerSessionId.'" />';

                $body_content .= '<table class="questions-table">';
                $body_content .= '<tr>';
                $body_content .= '<th class="table-top-left">'.Tr::trs('word.question.numberShort', '#').'</th>';
                $body_content .= '<th class="table-top-right">'.Tr::trs('word.question.text', 'Text').'</th>';
                $body_content .= '</tr>';
                foreach ($questionsMap as $question) {
                    $body_content .= '<tr><td class="table-middle-left">'.$question->number.'</td>';
                    $body_content .= '<td class="table-middle-middle">';
                    $body_content .= QuestionRender::renderQuestion($question);
                    $body_content .= '</td></tr>';
                }
                $body_content .= '<tr><td class="table-bottom-single" colspan="2" align="center">';
                $body_content .= '<input type="submit" value="'.Tr::trs('word.send', 'Send').'" />';
                $body_content .= '</td></tr>';
                $body_content .= '</table>';
                $body_content .= '</form>';
            } else {
                $body_content .= Tr::trs('page.astrologerAnswers.message.noQuestionsFound', 'No questions found');
            }
        } else {
            $body_content .= Tr::format('page.astrologerAnswers.error.invalidSessionId', [$answerSessionId], 'Invalid Answer Session ID: {0}');
        }
    }

    include $_SERVER["DOCUMENT_ROOT"].'/templates/page.php';
