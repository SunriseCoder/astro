var complexQuestions = [];

function addQuestionEntry(questionId) {
    var questionRoot = document.getElementById('questionRoot-' + questionId);
    var entryNumber = questionRoot.getElementsByTagName('table').length;

    var table = createElement('table', questionRoot);
    table.className = 'questions-table';

    var subQuestions = complexQuestions[questionId];
    for (var i = 0; i < subQuestions.length; i++) {
        var subQuestion = subQuestions[i];
        var tr = createElement('tr', table);
        var td = createElement('td', tr);
        if (i == 0) {
            td.className = 'table-top-single';
        } else if (i < subQuestions.length - 1) {
            td.className = 'table-middle-first';
        } else {
            td.className = 'table-bottom-single';
        }
        createTextElement(subQuestion.text + ' ', td);

        var inputName = 'answer-' + questionId + '-' + entryNumber + '-' + subQuestion.name;

        switch (subQuestion.type) {
        case 'DATE_AND_TIME':
            var input = createElement('input', td);
            input.setAttribute('type', 'datetime-local');
            input.setAttribute('name', inputName);
            break;
        case 'DATE':
            var input = createElement('input', td);
            input.setAttribute('type', 'date');
            input.setAttribute('name', inputName);
            break;
        case 'TIME':
            var input = createElement('input', td);
            input.setAttribute('type', 'time');
            input.setAttribute('name', inputName);
            break;
        case 'TEXT_LINE':
            var input = createElement('input', td);
            input.setAttribute('type', 'text');
            input.setAttribute('name', inputName);
            break;
        case 'SINGLE_CHOICE':
            // SubQuestion Options Rendering
            subQuestion.options.forEach(option => {
                createElement('br', td);

                var input = createElement('input', td);
                input.setAttribute('type', 'radio');
                input.setAttribute('name', inputName);
                input.setAttribute('value', option.name);

                createTextElement(option.text, td);
            });
            break;
        default:
            break;
        }
    }

    createElement('br', questionRoot);
}

// TODO Move to Utils Library
function createElement(name, parent) {
    var element = document.createElement(name);
    if (parent != null) {
        parent.appendChild(element);
    }

    return element;
}

// TODO Move to Utils Library
function createTextElement(text, parent) {
    var element = document.createTextNode(text);
    if (parent != null) {
        parent.appendChild(element);
    }

    return element;
}
