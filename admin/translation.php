<?php
    include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php';
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
                        <input id="editFormSubmit" type="button" value="Save" onclick="saveTranslation()" />
                    </form>

                    Filter: <input id="textFilter" type="text" size="20" oninput="renderTranslationData();" />
                    Empty Only <input id="emptyOnlyFilter" type="checkbox" onclick="renderTranslationData();" />

                    <div id="translationsRoot"></div>
                    <?php
                        if (!class_exists('Json')) {
                            include $_SERVER["DOCUMENT_ROOT"].'/utils/json.php';
                        }

                        $data = [];
                        $data['keywords'] = array_values(KeywordDao::getAll());
                        $data['languages'] = array_values(LanguageDao::getAll());
                        $data['translations'] = array_values(TranslationDao::getAll());

                        echo '<script>';
                        echo 'var translationDataStr = \''.Json::encode($data).'\';';
                        echo 'translationData = JSON.parse(translationDataStr);';
                        echo 'renderTranslationData();';
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
