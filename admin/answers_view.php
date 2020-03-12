<?php
    if (!class_exists('LoginDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php'; }
    LoginDao::checkPermissionsAndRedirect(Permission::AnswerSessionsView, './');

    if (!isset($_GET['session_id']) || !preg_match('/^[0-9]+$/', $_GET['session_id'])) {
        Utils::redirect('/admin/answer_sessions_list.php');
    }

    include $_SERVER["DOCUMENT_ROOT"].'/dao/questions.php';
    include $_SERVER["DOCUMENT_ROOT"].'/render/questions.php';

    $browser_title = 'Chaitanya Academy - Answer Sessions';
    $page_title = 'Answer Sessions - Administration';
    $css_includes = ['/css/questions.css'];
    $body_content = '';

    // Delete Answer Session
    $answerSessionId = $_GET['session_id'];
    $answerSessions = AnswerSessionDao::getWithAllAnswers($answerSessionId);

    if (count($answerSessions) > 0) {
        $answerSession = $answerSessions[$answerSessionId];
        $originSession = $answerSession->origin;

        // Prepare Answer Maps
        $originAnswersByQuestions = [];
        if (isset($originSession)) {
            foreach ($originSession->answers as $answer) {
                $originAnswersByQuestions[$answer->questionId] = $answer;
            }
        }

        $answersByQuestions = [];
        foreach ($answerSession->answers as $answer) {
            $answersByQuestions[$answer->questionId] = $answer;
        }

        $questions = QuestionDao::getAllForAnswerSession($answerSessionId);
        if (count($questions) > 0 ) {
            // Table Headers
            $body_content .= '<table class="admin-table">';
            $body_content .=    '<tr>';
            $body_content .=        '<th colspan="3">Question</th>';
            $body_content .=        '<th colspan="3">Participant</th>';
            if (isset($originSession)) {
                $body_content .=    '<th colspan="3">Astrologer</th>';
            }
            $body_content .=    '</tr>';
            $body_content .=    '<tr>';
            $body_content .=        '<th>ID</th><th>Text</th><th>Type</th>';
            $body_content .=        '<th>ID</th><th>Option</th><th>Text</th>';
            if (isset($originSession)) {
                $body_content .=    '<th>ID</th><th>Option</th><th>Text</th>';
            }
            $body_content .=    '</tr>';

            function renderAnswerCells($question, $answer) {
                $content = '<td>'.$answer->id.'</td>';
                if (isset($answer->questionOptionId)) {
                    $option = $question->options[$answer->questionOptionId];
                    $content .= '<td>'.$option->text.'</td>';
                } else {
                    $content .= '<td></td>';
                }
                if (!empty($answer->value)) {
                    $content .= '<td>';
                    $content .= $question->type->is(QuestionType::Complex) ? AnswerRender::renderComplexAnswer($question, $answer) : $answer->value;
                    $content .= '</td>';
                } else {
                    $content .= '<td></td>';
                }
                return $content;
            }

            // Table Content
            foreach ($questions as $question) {
                $body_content .= '<tr>';
                // Questions
                $body_content .= '<td>'.$question->id.'</td>';
                $body_content .= '<td>';
                if (!empty($question->number)) {
                    $body_content .= $question->number.') ';
                }
                $body_content .= $question->text;
                $body_content .= '</td>';
                $body_content .= '<td>'.$question->type->code.'</td>';

                // Participant's Answers
                if (isset($originSession)) {
                    if (isset($originAnswersByQuestions[$question->id])) {
                        $answer = $originAnswersByQuestions[$question->id];
                        $body_content .= renderAnswerCells($question, $answer);
                    } else {
                        $body_content .= '<td colspan="3"></td>';
                    }
                }

                // Astrologer's Answers
                if (isset($answersByQuestions[$question->id])) {
                    $answer = $answersByQuestions[$question->id];
                    $body_content .= renderAnswerCells($question, $answer);
                } else {
                    $body_content .= '<td colspan="3"></td>';
                }

                $body_content .= '</tr>';
            }
            $body_content .= '</table>';
        } else {
            $body_content .= 'No questions found for Answer Session: '.$answerSessionId;
        }
    } else {
        $body_content .= 'Answer Session with ID: '.$answerSessionId.' was not found';
    }

    include $_SERVER["DOCUMENT_ROOT"].'/admin/templates/page.php';
