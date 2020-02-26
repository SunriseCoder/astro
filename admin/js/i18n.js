var translationData;
var keywordsMap;
var languagesMap;
var translationsMap;

function refreshTranslationData() {
    clearEditForm();

    // Load data via Ajax
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            translationData = JSON.parse(this.responseText);
            renderTranslationData();
        }
    };
    xhttp.open("GET", "translation_ajax.php", true);
    xhttp.send();

    renderTranslationData();
}

function renderTranslationData() {
    var translationRoot = document.getElementById('translationsRoot');
    translationRoot.innerHTML = '';

    var keywords = translationData.keywords;
    keywordsMap = mapById(keywords);

    var languages = translationData.languages;
    languagesMap = mapById(languages);

    var translations = translationData.translations;
    translationsMap = createTranslationsMap(translationData.translations);

    var table = createElement('table', translationRoot);
    var tr = createElement('tr', table);

    // Table Header
    createElementWithText('th', 'Keyword', tr);
    languages.forEach(function(element) {
        createElementWithText('th', element.nameEnglish, tr);
    });

    // Table Content
    for (var i = 0; i < keywords.length; i++) {
        var keyword = keywords[i];
        var keywordMap = translationsMap[keyword.id];
        if (!matchFilters(keyword, keywordMap)) {
            continue;
        }

        var tr = createElement('tr', table);

        createElementWithText('td', keyword.code, tr);
        for (var j = 0; j < languages.length; j++) {
            var language = languages[j];
            var translation = keywordMap == undefined ? undefined : keywordMap[language.id];
            var element;
            if (translation == undefined) {
                element = createElement('td', tr);
                element.innerHTML = '<font color="red"><b>Empty</b></font>';
            } else {
                element = createElementWithText('td', translation.text, tr);
            }
            element.setAttribute('onclick', 'editTranslation(' + keyword.id + ', ' + language.id + ');');
        }
    }

    clearEditForm();
}

function matchFilters(keyword, keywordMap) {
    var textFilterValue = document.getElementById('textFilter').value;
    var emptyCellsOnlyFilterValue = document.getElementById('emptyCellsOnlyFilter').checked;
    var emptyRowsOnlyFilterValue = document.getElementById('emptyRowsOnlyFilter').checked;

    var foundEmpty = false;
    var foundMatch = textFilterValue.length >= 2 && keyword.code.toLowerCase().includes(textFilterValue.toLowerCase());
    for (var i = 0; i < languagesMap.length; i++) {
        var language = languagesMap[i];
        if (language == undefined) {
            continue;
        }

        var translation = keywordMap == undefined ? undefined : keywordMap[language.id];

        if (translation == undefined) {
            // No need to apply any other filters to the empty values
            foundEmpty = true;
            continue;
        } else if (emptyRowsOnlyFilterValue) {
            // If any non-empty value found with Empty Rows Only filter, the row doesn't match
            return false;
        }

        // Text Filter
        if (textFilterValue.length >= 2 && translation.text.toLowerCase().includes(textFilterValue.toLowerCase())) {
            foundMatch = true;
        }
    }

    // Empty Cells Only Checkbox
    if (emptyCellsOnlyFilterValue && !foundEmpty) {
        return false;
    }

    // Text Filter Result
    if (textFilterValue.length >= 2 && !foundMatch) {
        return false;
    }

    return true;
}

// Copying Translation from selected Cell to the Edit Form
function editTranslation(keywordId, languageId) {
    clearEditForm();

    var translation = translationsMap[keywordId] == undefined ? undefined : translationsMap[keywordId][languageId];
    if (translation != undefined) {
        document.getElementById('translationId').value = translation.id;
    }
    document.getElementById('keywordId').value = keywordId;
    document.getElementById('languageId').value = languageId;

    var keyword = keywordsMap[keywordId];
    var keywordCell = document.getElementById('keywordCell');
    createTextElement(keyword.code, keywordCell);

    var language = languagesMap[languageId];
    var languageCell = document.getElementById('languageCell');
    createTextElement(language.nameEnglish, languageCell);

    var translationCell = document.getElementById('translationCell');
    if (translation) {
        translationCell.value = translation.text;
    }

    document.getElementById('editFormSubmit').disabled = false;
    window.scrollTo(0, 0); // Scroll to the top of the page
}

function clearEditForm() {
    document.getElementById('translationId').value = '';
    document.getElementById('keywordId').value = '';
    document.getElementById('languageId').value = '';

    document.getElementById('keywordCell').innerHTML = '';
    document.getElementById('languageCell').innerHTML = '';
    document.getElementById('translationCell').value = '';

    document.getElementById('editFormSubmit').disabled = true;
}

function saveTranslation() {
    var form = document.getElementById('translationForm');
    var query = '';
    for (var i = 0; i < form.elements.length; i++) {
        var name = form.elements[i].name;
        var value = form.elements[i].value;
        if (name && value) {
            query += query != '' ? '&' : '';
            query += name + '=' + value;
        }
    }

    // Sending Ajax to Server
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            translationData = JSON.parse(this.responseText);
            clearEditForm();
            renderTranslationData();
        }
    };
    xhttp.open("POST", "translation_ajax.php", true);
    xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhttp.send(query);
}

function mapById(elements) {
    var resultMap = [];
    for (var i = 0; i < elements.length; i++) {
        var element = elements[i];
        resultMap[element.id] = element;
    }
    return resultMap;
}

function createTranslationsMap(translations) {
    var resultMap = [];
    for (var i = 0; i < translations.length; i++) {
        var translation = translations[i];

        var keywordMap = resultMap[translation.keywordId];
        if (keywordMap == null) {
            keywordMap = [];
            resultMap[translation.keywordId] = keywordMap;
        }

        keywordMap[translation.languageId] = translation;
    }

    return resultMap;
}

//TODO Move to Utils Library
function createElementWithText(name, text, parent) {
    var element = document.createElement(name);
    createTextElement(text, element);

    if (parent != null) {
        parent.appendChild(element);
    }

    return element;
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
