<?php
    include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php';
    LoginDao::checkPermissionsAndRedirect([], '/login.php');

    if (!class_exists('Tr')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/i18n.php'; }
    if (!class_exists('Question')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/questions.php'; }
    if (!class_exists('QuestionRender')) { include $_SERVER["DOCUMENT_ROOT"].'/render/questions.php'; }

    $browser_title = Tr::trs('page.common.browserTitle', 'Astrology - Chaitanya Academy');
    $page_title = Tr::trs('page.questions.pageTitle', 'Survey');
    $css_includes = ['/css/questions.css'];
    $js_includes = ['/js/questions.js'];
    $body_content = '';

    // Saving Question Answers
    $alreadyAnswered = AnswerSessionDao::hasCurrentUserAlreadyAnswered();
    if ($alreadyAnswered) {
        $body_content .= Tr::trs('page.questions.error.alreadyAnswered', 'Sorry, but you already have taken part in the survey');
    } else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $error = AnswerSessionDao::saveAnswers();
        if ($error) {
            $body_content .= '<font color="red">'.$error.'</font><br />';
        } else {
            $body_content .= Tr::trs('page.questions.message.successfullyComplete', 'Thank you for participating in the survey');
        }
    } else {
        $body_content .= Tr::trs('page.questions.text.surveyInstructions');

        $questionsMap = QuestionDao::getDefaultQuestionnaire();
        if (count($questionsMap) > 0) {
            $body_content .= '<form action="" method="post">';
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
            $body_content .= Tr::trs('page.questions.noQuestions', 'No questions');
        }
    }

    include $_SERVER["DOCUMENT_ROOT"].'/templates/page.php';
