<?php
    if (!class_exists('Json')) {
        include $_SERVER["DOCUMENT_ROOT"].'/utils/json.php';
    }

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
                return 'No Answer';
            }

            if (!isset($answer->questionId)) {
                return 'Error: questionId is not set';
            }

            $questionId = $answer->questionId;
            if (!isset($questions[$questionId])) {
                return 'Error: wrong questionId: '.$questionId;
            }

            $question = $questions[$questionId];
            if ($question->type->is(QuestionType::Complex)) {
                $value = self::renderComplexAnswer($question, $answer);
            } else if (isset($answer->questionOptionId)) {
                // Answer Value is a Reference to a QuestionOption
                $questionOptionId = $answer->questionOptionId;
                $questionOptions = $question->options;
                if (!isset($questionOptions)) {
                    return 'Error: In the Answer '.$answer->id.' questionOptionId is set: '.$questionOptionId.', but the Question '.$questionId.' has no Options';
                }

                if (!isset($questionOptions[$questionOptionId])) {
                    return 'Error: In the Answer '.$answer->id.' questionOptionId is set to: '.$questionOptionId.
                        ', but there is no such Option in Question '.$questionId;
                }
                $questionOption = $questionOptions[$questionOptionId];
                $value = $questionOption->text;
            } else if ($answer->value) {
                $value = $answer->value;
            }
            return $value;
        }

        public static function renderComplexAnswer($question, $answer) {
            if (!isset($answer->value)) {
                return 'No Answer';
            }

            $answerObject = Json::decode($answer->value);
            if (count($answerObject) == 0) {
                return 'No Answer';
            }

            $questionObject = Json::decode($question->markup);
            $result = '<table>';
            // Table Header using Question Text
            foreach ($questionObject->subQuestions as $subQuestion) {
                $result .= '<th>'.$subQuestion->text.'</th>';
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
                $result .= '<tr>';
                $answers = [];
                foreach ($entry as $key => $value) {
                    $answers[$key] = $value;
                }
                foreach ($questionObject->subQuestions as $subQuestion) {
                    $result .= '<td>';
                    if (isset($answers[$subQuestion->name])) {
                        $value = $answers[$subQuestion->name];
                        if (isset($subQuestion->options)) {
                            if (isset($questionOptions[$subQuestion->name][$value])) {
                                $result .= $questionOptions[$subQuestion->name][$value]->text;
                            } else {
                                $result .= 'No Answer';
                            }
                        } else {
                            $result .= $value;
                        }
                    } else {
                        $result .= 'No Answer';
                    }
                    $result .= '</td>';
                }
                $result .= '</tr>';
            }
            $result .= '</table>';

            return $result;
        }
    }

    class QuestionRender {
        public static function renderQuestion($question) {
            // Render different layouts for different question types
            switch($question->type->code) {
                case QuestionType::DateAndTime:
                    echo $question->text.' <input type="datetime-local" name="answer-'.$question->id.'" />';
                    break;
                case QuestionType::Date:
                    echo $question->text.' <input type="date" name="answer-'.$question->id.'" />';
                    break;
                case QuestionType::Time:
                    echo $question->text.' <input type="time" name="answer-'.$question->id.'" />';
                    break;
                case QuestionType::TextLine:
                    echo $question->text.' <input type="text" name="answer-'.$question->id.'" />';
                    break;
                case QuestionType::SingleChoice:
                    // Question Options Rendering
                    if ($question->options) {
                        echo $question->text;
                        foreach ($question->options as $questionOption) {
                            $group = 'answer-'.$question->id;
                            $value = $questionOption->id;
                            $text = $questionOption->text;
                            echo '<br /><input type="radio" name="'.$group.'" value="'.$value.'">'.$text;
                        }
                    }
                    break;
                case QuestionType::Complex:
                    // Very complex Question Rendering
                    $metadata = Json::decode($question->markup);
                    $questionText = $metadata->text;
                    $addEntryText = $metadata->addEntryText;
                    $subQuestions = $metadata->subQuestions;

                    echo $questionText;

                    // Metadata for adding Entry by JavaScript
                    echo '<div id="questionRoot-'.$question->id.'"></div>';
                    echo ' <button type="button" onclick="addQuestionEntry('.$question->id.')">'.$addEntryText.'</button>';
                    echo '<script>';
                    echo 'var subQuestions = [];';
                    foreach ($subQuestions as $subQuestion) {
                        echo 'var subQuestionStr = \''.Json::encode($subQuestion).'\';';
                        echo 'var subQuestion = JSON.parse(subQuestionStr);';
                        echo 'subQuestions.push(subQuestion);';
                    }
                    echo 'complexQuestions['.$question->id.'] = subQuestions;';
                    echo '</script>';
                    break;
                default:
                    echo '<font color="red">Unsupported Question Type: '.$question->type->code.'</font>';
                    var_dump($question);
                    break;
            }
        }
    }
?>
