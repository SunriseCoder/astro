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

    var td1 = document.createElement('td');
    td1.appendChild(document.createTextNode('New'));
    newRow.appendChild(td1);

    var td2 = document.createElement('td');
    var input2 = document.createElement('input');
    input2.setAttribute('type', 'text');
    input2.setAttribute('name', 'question_options[' + row_number + '][text]');
    input2.setAttribute('size', '30');
    td2.appendChild(input2);
    newRow.appendChild(td2);

    var td3 = document.createElement('td');
    var input3 = document.createElement('input');
    input3.setAttribute('type', 'text');
    input3.setAttribute('name', 'question_options[' + row_number + '][position]');
    input3.setAttribute('size', '4');
    input3.setAttribute('value', (row_number + 1) * 10);
    td3.appendChild(input3);
    newRow.appendChild(td3);

    var td4 = document.createElement('td');
    var input4 = document.createElement('input');
    input4.setAttribute('type', 'button');
    input4.setAttribute('onclick', 'deleteAnswerOption(' + row_number + ');');
    input4.setAttribute('value', 'Delete');
    td4.appendChild(input4);
    newRow.appendChild(td4);

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
