<?php
    if (!class_exists('Tr')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/i18n.php'; }
?>
<html>
    <?
        $browser_title = Tr::trs('page.common.browserTitle', 'Astrology - Chaitanya Academy');
        $page_title = Tr::trs('page.index.pageTitle', 'The Chaitanya Academy ASTRO-PROJECT');

        include $_SERVER["DOCUMENT_ROOT"].'/templates/metadata.php';
    ?>

    <body>
        <table id="page-markup-table">
            <tr>
                <td id="page-top" colspan="2">
                    <? include $_SERVER["DOCUMENT_ROOT"].'/templates/page_top.php'; ?>
                </td>
            </tr>
            <tr>
                <td id="page-menu">
                    <? include $_SERVER["DOCUMENT_ROOT"].'/templates/menu.php'; ?>
                </td>
                <td id="page-body">
                    <? include $_SERVER["DOCUMENT_ROOT"].'/templates/body_top.php'; ?>

                    <? /* Body Area Start */ ?>

                    <?php echo Tr::trs('page.index.text.surveyIntroduction'); ?>

                    <? /* Body Area End */ ?>

                </td>
            </tr>
            <tr>
                <td id="page-footer" colspan="2">
                    <? include $_SERVER["DOCUMENT_ROOT"].'/templates/page_footer.php'; ?>
                </td>
            </tr>
        </table>
    </body>
</html>
