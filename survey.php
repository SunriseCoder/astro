<?php
    if (!class_exists('Utils')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/utils.php'; }
    if (!LoginDao::isLogged()) {
        Utils::redirect('/login.php');
    }

    if (!class_exists('Tr')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/i18n.php'; }
    if (!class_exists('Question')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/questions.php'; }
    if (!class_exists('ParticipantAnswerGroupDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/answers.php'; }
    if (!class_exists('QuestionRender')) { include $_SERVER["DOCUMENT_ROOT"].'/render/questions.php'; }
    if (!class_exists('HTMLRender')) { include $_SERVER["DOCUMENT_ROOT"].'/render/html.php'; }

    $browser_title = Tr::trs('page.common.browserTitle', 'Astrology - Chaitanya Academy');
    $page_title = Tr::trs('page.questions.pageTitle', 'Survey');
    $css_includes = ['/css/questions.css'];
    $js_includes = ['/js/questions.js'];
    $body_content = '';

    // Saving Question Answers
    $alreadyAnswered = ParticipantAnswerGroupDao::hasCurrentUserAlreadyAnswered();
    if ($alreadyAnswered) {
        $body_content .= Tr::trs('page.questions.error.alreadyAnswered', 'Sorry, but you already have taken part in the survey');
    } else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $error = ParticipantAnswerGroupDao::saveAnswers();
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
            $tableModel = new TableModel();
            $tableModel->header []= [Tr::trs('word.question.numberShort', '#'), Tr::trs('word.question.text', 'Text')];
            foreach ($questionsMap as $question) {
                $tableModel->data []= [$question->number, QuestionRender::renderQuestion($question)];
            }
            $submitValue = '<input type="submit" value="'.Tr::trs('word.send', 'Send').'" />';
            $tableModel->data []= [['colspan' => 2, 'align' => 'center', 'value' => $submitValue]];
            $body_content .= HTMLRender::renderTable($tableModel, 'questions-table');
            $body_content .= '</form>';
        } else {
            $body_content .= Tr::trs('page.questions.noQuestions', 'No questions');
        }
    }

    include $_SERVER["DOCUMENT_ROOT"].'/templates/page.php';
?>
