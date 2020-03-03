<?php
    if (!class_exists('LoginDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php'; }
    LoginDao::checkPermissionsAndRedirect([Permission::TranslationsView], './');
?>
<html>
    <?
        $browser_title = 'Chaitanya Academy - Astrology';
        $page_title = 'Translation';

        $js_includes = array('js/i18n.js');

        include $_SERVER["DOCUMENT_ROOT"].'/admin/templates/metadata.php';
    ?>

    <body>
        <table>
            <tr>
                <td colspan="2">
                    <? include $_SERVER["DOCUMENT_ROOT"].'/templates/page_top.php'; ?>
                </td>
            </tr>
            <tr>
                <td class="menu">
                    <? include $_SERVER["DOCUMENT_ROOT"].'/admin/templates/menu.php'; ?>
                </td>
                <td>
                    <? include $_SERVER["DOCUMENT_ROOT"].'/templates/body_top.php'; ?>

                    <? /* Body Area Start */ ?>

                    <!-- Edit Form -->
                    <div id="editFormDiv"></div>
                    <form id="translationForm">
                        <input id="translationId" type="hidden" name="id" value="" />
                        <input id="keywordId" type="hidden" name="keyword_id" value="" />
                        <input id="languageId" type="hidden" name="language_id" value="" />
                        <table>
                            <tr>
                                <td>Keyword</td>
                                <td id="keywordCell"></td>
                            </tr>
                            <tr>
                                <td>Language</td>
                                <td id="languageCell"></td>
                            </tr>
                            <tr>
                                <td>Translation</td>
                                <td><textarea id="translationCell" name="text" rows="10" cols="100"></textarea></td>
                            </tr>
                        </table>
                        <input id="editFormSubmit" type="button" value="Save" onclick="saveTranslation();" />
                        <input type="button" value="Clear" onclick="clearEditForm();" />
                        <label id="saveTranslationStatus"></label>
                    </form>

                    <!-- Table Filters -->
                    <input type="button" value="Refresh" onclick="refreshTranslationData();" />
                    Filter: <input id="textFilter" type="text" size="20" oninput="renderTranslationTable();" />
                    <input id="emptyCellsOnlyFilter" type="checkbox" onclick="renderTranslationTable();" />Empty Cells Only
                    <input id="emptyRowsOnlyFilter" type="checkbox" onclick="renderTranslationTable();" />Empty Rows Only
                    <input id="copyDefaultLanguageValueIfEmpty" type="checkbox" />Copy Default Language Value if empty<br />
                    <div id="languageFilter">
                        <input type="button" value="All" onclick="selectAllLanguageFilters(true);" />
                        <input type="button" value="None" onclick="selectAllLanguageFilters(false);" />
                    </div>

                    <!-- Table Placeholder -->
                    <div id="translationsRoot"></div>

                    <?php
                        if (!class_exists('Json')) {
                            include $_SERVER["DOCUMENT_ROOT"].'/utils/json.php';
                        }

                        echo '<script>';
                        echo 'refreshTranslationData();';
                        echo '</script>';
                    ?>

                    <? /* Body Area End */ ?>

                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <? include $_SERVER["DOCUMENT_ROOT"].'/templates/page_footer.php'; ?>
                </td>
            </tr>
        </table>
    </body>
</html>
