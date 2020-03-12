<?php
    include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php';
    LoginDao::checkPermissionsAndRedirect(Permission::AstrologerAnswering, './');

    if (!class_exists('Tr')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/i18n.php'; }
    if (!class_exists('QuestionDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/questions.php'; }
    if (!class_exists('QuestionRender')) { include $_SERVER["DOCUMENT_ROOT"].'/render/questions.php'; }

    $browser_title = Tr::trs('page.common.browserTitle', 'Astrology - Chaitanya Academy');
    $page_title = Tr::trs('page.astrologerChoose.pageTitle', 'Answer questions as an astrologer - choose answers session');
    $css_includes = ['/css/questions.css'];
    $style = 'body { max-width: 100%; }';
    $body_content = Tr::trs('page.astrologerChoose.text.surveyInstructions', '').'<br /><br />';

    $allQuestionnaires = QuestionnaireDao::getAll();
    $allQuestions = QuestionDao::getAllNonSecret();

    $currentUser = LoginDao::getCurrentUser();
    // Only Participant's Answers (without Astrologers')
    $answerSessionsMap = AnswerSessionDao::getAllOriginsWithNonSecretAnswers();

    // Already Guessed Answer Sessions
    $guessedSessionsMapping = AnswerSessionDao::getGuessedIdsForCurrentUser();

    if (count($answerSessionsMap) > 0) {
        foreach ($allQuestionnaires as $questionnaire) {
            // Questions for the particular Table (Questionnaire);
            $questionsMap = [];
            foreach ($allQuestions as $question) {
                if ($question->questionnaireId == $questionnaire->id) {
                    $questionsMap[$question->id] = $question;
                }
            }
            if (count($questionsMap) > 0) {
                $body_content .= $questionnaire->name;
                $body_content .= '<table class="questions-table">';

                // Table Header
                $body_content .= '<th class="table-top-left">'.Tr::trs('word.id', 'ID').'</th>';
                foreach ($questionsMap as $question) {
                    $body_content .= '<th class="table-top-middle">'.$question->text.'</th>';
                }
                $body_content .= '<th class="table-top-right">'.Tr::trs('word.status', 'Status').'</th>';

                $answerSessions = [];
                foreach ($answerSessionsMap as $answerSession) {
                    if ($answerSession->questionnaireId == $questionnaire->id) {
                        $answerSessions []= $answerSession;
                    }
                }

                // Table Content
                for ($i = 0; $i < count($answerSessions); $i++) {
                    $answerSession = $answerSessions[$i];

                    // Selecting Answers mapped by Question ID
                    $answers = [];
                    foreach ($answerSession->answers as $answer) {
                        $answers[$answer->questionId] = $answer;
                    }

                    $body_content .= '<tr>';
                    $lastRow = $i == (count($answerSessions) - 1);
                    $body_content .= '<td class="'.($lastRow ? 'table-bottom-left' : 'table-middle-left').'">'.$answerSession->id.'</td>';
                    foreach ($questionsMap as $question) {
                        $answer = isset($answers[$question->id]) ? $answers[$question->id] : NULL;
                        $value = AnswerRender::renderAnswer($questionsMap, $answer);
                        if ($lastRow) {
                            $body_content .= '<td class="table-bottom-middle">'.$value.'</td>';
                        } else {
                            $body_content .= '<td class="table-middle-middle">'.$value.'</td>';
                        }
                    }

                    // Status
                    $body_content .= '<td class="'.($lastRow ? 'table-bottom-right' : 'table-middle-right').'">';
                    // Check that the Astrologer doesn't guess his own answers
                    if ($answerSession->userId == $currentUser->id) {
                        $body_content .= Tr::trs('word.astrologer.yourAnswers', 'Your answers');
                    } else if (isset($guessedSessionsMapping[$answerSession->id])) {
                        $body_content .= Tr::trs('word.astrologer.alreadyGuessed', 'Already guessed');
                    } else {
                        $body_content .= '<a href="astrologer_answer.php?id='.$answerSession->id.'">'.Tr::trs('word.new', 'New').'</a>';
                    }
                    $body_content .= '</td>';
                    $body_content .= '</tr>';
                }

                $body_content .= '</table><br />';
            }
        }
    } else {
        $body_content .= Tr::trs('page.astrologerChoose.noAnswersFound', 'No Answers found');
    }

    include $_SERVER["DOCUMENT_ROOT"].'/templates/page.php';
