<?php
    if (!class_exists('Json')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/json.php'; }
    if (!class_exists('Tr')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/i18n.php'; }

    class AnswerRender {
        /**
         * Render particular answer.
         * Required all array parameters be indexed by Id, for example:
         * $questions[$question->id] = $question;
         *
         * @param array of Question $questions
         * @param Question $question
         * @param Answer $answer
         */
        public static function renderAnswer($questions, $answer) {
            if (!isset($answer)) {
                return Tr::trs('word.noAnswer', 'No Answer');
            }

            if (!isset($answer->questionId)) {
                return Tr::format('error.renderAnswer.noQuestionId', [$answer->id], 'Error: For Answer with ID: {0} the QuestionID is not set');
            }

            $questionId = $answer->questionId;
            if (!isset($questions[$questionId])) {
                return Tr::format('error.renderAnswer.nonExistentQuestionId', [$answer->id, $questionId],
                    'Error: Answer with ID: {0} references to non-existent Question with ID: {1}');
            }

            $question = $questions[$questionId];
            if ($question->type->is(QuestionType::Complex)) {
                $content = self::renderComplexAnswer($question, $answer);
            } else if (isset($answer->questionOptionId)) {
                // Answer Value is a Reference to a QuestionOption
                $questionOptionId = $answer->questionOptionId;
                $questionOptions = $question->options;
                if (!isset($questionOptions)) {
                    return Tr::format('error.renderAnswer.questionOptionIsSetButQuestionHasNoOptions', [$answer->id, $questionOptionId, $questionId],
                        'Error: In the Answer with ID: {0} questionOptionId is set to: {1}, but the Question with ID: {2} has no Options');
                }

                if (!isset($questionOptions[$questionOptionId])) {
                    return Tr::format('error.renderAnswer.questionOptionIsSetButQuestionHasNoThisOption', [$answer->id, $questionOptionId, $questionId],
                        'Error: In the Answer with ID: {0} questionOptionId is set to: {1}, but there is no such Option in Question with ID: {2}');
                }
                $questionOption = $questionOptions[$questionOptionId];
                $content = $questionOption->text;
            } else if ($answer->value) {
                $content = $answer->value;
            }
            return $content;
        }

        public static function renderComplexAnswer($question, $answer) {
            if (!isset($answer->value)) {
                return Tr::trs('word.noAnswer', 'No Answer');
            }

            $answerObject = Json::decode($answer->value);
            if (count($answerObject) == 0) {
                return Tr::trs('word.noAnswer', 'No Answer');
            }

            $questionObject = Json::decode($question->markup);
            $content = '<table>';
            // Table Header using Question Text
            foreach ($questionObject->subQuestions as $subQuestion) {
                $content .= '<th>'.$subQuestion->text.'</th>';
            }

            // SubQuestions' Options
            $questionOptions = [];
            foreach ($questionObject->subQuestions as $subQuestion) {
                if (isset($subQuestion->options)) {
                    foreach ($subQuestion->options as $option) {
                        $questionOptions[$subQuestion->name][$option->name] = $option;
                    }
                }
            }

            // Table Content
            foreach ($answerObject as $entry) {
                $content .= '<tr>';
                $answers = [];
                foreach ($entry as $key => $value) {
                    $answers[$key] = $value;
                }
                foreach ($questionObject->subQuestions as $subQuestion) {
                    $content .= '<td>';
                    if (isset($answers[$subQuestion->name])) {
                        $value = $answers[$subQuestion->name];
                        if (isset($subQuestion->options)) {
                            if (isset($questionOptions[$subQuestion->name][$value])) {
                                $content .= $questionOptions[$subQuestion->name][$value]->text;
                            } else {
                                $content .= Tr::trs('word.noAnswer', 'No Answer');
                            }
                        } else {
                            $content .= $value;
                        }
                    } else {
                        $content .= Tr::trs('word.noAnswer', 'No Answer');
                    }
                    $content .= '</td>';
                }
                $content .= '</tr>';
            }
            $content .= '</table>';

            return $content;
        }
    }

    class QuestionRender {
        public static function renderQuestion($question) {
            $content = '';
            // Render different layouts for different question types
            switch($question->type->code) {
                case QuestionType::DateAndTime:
                    $content .= $question->text.' <input type="datetime-local" name="answer-'.$question->id.'" />';
                    break;
                case QuestionType::Date:
                    $content .= $question->text.' <input type="date" name="answer-'.$question->id.'" />';
                    break;
                case QuestionType::Time:
                    $content .= $question->text.' <input type="time" name="answer-'.$question->id.'" />';
                    break;
                case QuestionType::TextLine:
                    $content .= $question->text.' <input type="text" name="answer-'.$question->id.'" />';
                    break;
                case QuestionType::SingleChoice:
                    // Question Options Rendering
                    if ($question->options) {
                        $content .= $question->text;
                        foreach ($question->options as $questionOption) {
                            $group = 'answer-'.$question->id;
                            $value = $questionOption->id;
                            $text = $questionOption->text;
                            $content .= '<br /><input type="radio" name="'.$group.'" value="'.$value.'">'.$text;
                        }
                    }
                    break;
                case QuestionType::Complex:
                    // Very complex Question Rendering
                    $metadata = Json::decode($question->markup);
                    $addEntryText = $metadata->addEntryText;
                    $subQuestions = $metadata->subQuestions;

                    $content .= $question->text;

                    // Metadata for adding Entry by JavaScript
                    $content .= '<div id="questionRoot-'.$question->id.'"></div>';
                    $content .= ' <button type="button" onclick="addQuestionEntry('.$question->id.')">'.$addEntryText.'</button>';
                    $content .= '<script>';
                    $content .= 'var subQuestions = [];';
                    foreach ($subQuestions as $subQuestion) {
                        $content .= 'var subQuestionStr = \''.Json::encode($subQuestion).'\';';
                        $content .= 'var subQuestion = JSON.parse(subQuestionStr);';
                        $content .= 'subQuestions.push(subQuestion);';
                    }
                    $content .= 'complexQuestions['.$question->id.'] = subQuestions;';
                    $content .= '</script>';
                    break;
                default:
                    Logger::error('Unsupported Question Type: '.$question->type->code);
                    $message = Tr::format('error.renderQuestion.unsupportedQuestionType', [$question->type->code], 'Unsupported QuestionType: {0}');
                    $content .= '<font color="red">'.$message.'</font>';
                    break;
            }
            return $content;
        }
    }
?>
