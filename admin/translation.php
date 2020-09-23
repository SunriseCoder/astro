<?php
    if (!class_exists('LoginDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php'; }
    LoginDao::checkPermissionsAndRedirect(Permission::TranslationsView, './');

    $browser_title = 'Chaitanya Academy - Astrology';
    $page_title = 'Translation';
    $js_includes = ['js/i18n.js', 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js', 'https://www.google.com/jsapi'];

    $body_content = '
                    <!-- Edit Form -->
                    <div id="editFormDiv"></div>
                    <form id="translationForm">
                        <input id="translationId" type="hidden" name="id" value="" />
                        <input id="keywordId" type="hidden" name="keyword_id" value="" />
                        <input id="languageId" type="hidden" name="language_id" value="" />
                        <table class="admin-table">
                            <tr>
                                <td>Keyword</td>
                                <td id="keywordCell"></td>
                            </tr>
                            <tr>
                                <td>Language</td>
                                <td id="languageCell"></td>
                            </tr>
                            <tr>
                                <td>Original text</td>
                                <td id="originalTextCell"></td>
                            </tr>
                            <tr>
                                <td>Translation</td>
                                <td>
                                    <textarea id="translationCell" name="text" rows="10" cols="100"></textarea>
                                    <div id="google-transliteration"></div>
                                </td>
                            </tr>
                        </table>
                        <input id="editFormSubmit" type="button" value="Save" onclick="saveTranslation();" />
                        <input type="button" value="Clear" onclick="clearEditForm();" />
                        <label id="saveTranslationStatus"></label>
                    </form>

                    <!-- Table Filters -->
                    Filter: <input id="textFilter" type="text" size="20" oninput="renderTranslationTable();" />
                    <input id="emptyCellsFilter" type="checkbox" onclick="renderTranslationTable();" />Empty Cells
                    <input id="emptyRowsFilter" type="checkbox" onclick="renderTranslationTable();" />Empty Rows
                    <input id="outdatedCellsFilter" type="checkbox" onclick="renderTranslationTable();" />Outdated Cells<br />
                    <input type="button" value="Refresh" onclick="refreshTranslationData();" />
                    <input id="copyDefaultLanguageValueIfEmpty" type="checkbox" />Copy Default Language Value if empty<br />
                    <div id="languageFilter">
                        <input type="button" value="All" onclick="selectAllLanguageFilters(true);" />
                        <input type="button" value="None" onclick="selectAllLanguageFilters(false);" />
                    </div>

                    <!-- Table Placeholder -->
                    <div id="translationsRoot"></div>';

    $body_content .= '<script>';
    $body_content .= 'refreshTranslationData();';
    $body_content .= 'google.load("elements", "1", {packages: "transliteration"});';
    $body_content .= 'google.setOnLoadCallback(onGoogleTransliterationLoad);';
    $body_content .= '</script>';

    include $_SERVER["DOCUMENT_ROOT"].'/admin/templates/page.php';
?>
