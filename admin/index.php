<?php
    if (!class_exists('LoginDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php'; }
    LoginDao::checkPermissionsAndRedirect([Permission::AdminMenuVisible], '../');
?>
<html>
    <?
        $browser_title = 'Chaitanya Academy - Astrology';
        $page_title = 'Astrology - Administration';

        include $_SERVER["DOCUMENT_ROOT"].'/admin/templates/metadata.php';
    ?>

    <body>
        <table id="page-markup-table">
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

                    Hello!<br />
                    Welcome to the Administration page<br />

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
