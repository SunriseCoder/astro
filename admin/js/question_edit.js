function questionTypeChanged() {
    var select = document.getElementById('question_type_select');
    var questionTypeCode = select.selectedOptions[0].getAttribute('question_type_code');
    if (questionTypeCode == 'SINGLE_CHOICE' || questionTypeCode == 'MULTIPLE_CHOICE') {
        // Show Table Rows with Answer Options
    } else {
        // Hide Table Rows with Answer Options
        var table = document.getElementById('question_options_table');
        var tbody = table.childNodes[0];
        var optionRows = tbody.childNodes;
        for (var i = 0; i < optionRows.length; i++) {
            optionRow = optionRows[i];
            if (optionRow.hasAttribute('row_number')) {
                tbody.removeChild(optionRow);
            }
        }
    }
}

function addAnswerOption() {
    var table = document.getElementById('question_options_table');
    var tbody = table.childNodes[0];
    var elementCount = tbody.childElementCount - 2; // Table Header Row and Add Answer Option Button Row
    var lastOptionRow = tbody.childNodes[elementCount];
    var row_number = lastOptionRow.getAttribute('row_number');
    row_number++;
    var newRow = createNewRow(row_number);
    tbody.insertBefore(newRow, tbody.childNodes[tbody.childElementCount - 1]); // Insert new Row before the last one (with button)
}

function createNewRow(row_number) {
    var newRow = document.createElement('tr');
    newRow.setAttribute('row_number', row_number);

    // ID (New)
    var td = document.createElement('td');
    td.appendChild(document.createTextNode('New'));
    newRow.appendChild(td);

    // Text
    var td = document.createElement('td');
    var input = document.createElement('input');
    input.setAttribute('type', 'text');
    input.setAttribute('name', 'question_options[' + row_number + '][text]');
    input.setAttribute('size', '30');
    td.appendChild(input);
    newRow.appendChild(td);

    // Is Not Applicable
    var td = document.createElement('td');
    var input = document.createElement('input');
    input.setAttribute('type', 'checkbox');
    input.setAttribute('name', 'question_options[' + row_number + '][isNotApplicable]');
    input.setAttribute('size', '30');
    td.appendChild(input);
    newRow.appendChild(td);

    // Position
    var td = document.createElement('td');
    var input = document.createElement('input');
    input.setAttribute('type', 'text');
    input.setAttribute('name', 'question_options[' + row_number + '][position]');
    input.setAttribute('size', '4');
    input.setAttribute('value', (row_number + 1) * 10);
    td.appendChild(input);
    newRow.appendChild(td);

    // Actions (Delete)
    var td = document.createElement('td');
    var input = document.createElement('input');
    input.setAttribute('type', 'button');
    input.setAttribute('onclick', 'deleteAnswerOption(' + row_number + ');');
    input.setAttribute('value', 'Delete');
    td.appendChild(input);
    newRow.appendChild(td);

    return newRow;
}

function deleteAnswerOption(row_number) {
    var table = document.getElementById('question_options_table');
    var tbody = table.childNodes[0];
    var optionRows = tbody.childNodes;
    for (var i = 0; i < optionRows.length; i++) {
        optionRow = optionRows[i];
        if (optionRow.getAttribute('row_number') == row_number) {
            tbody.removeChild(optionRow);
            break;
        }
    }
}
