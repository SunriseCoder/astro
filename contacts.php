<?php
    if (!class_exists('Tr')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/i18n.php'; }
?>
<html>
    <?
        $browser_title = Tr::trs('page.common.browserTitle', 'Astrology - Chaitanya Academy');
        $page_title = Tr::trs('page.contacts.pageTitle', 'Contacts');

        include $_SERVER["DOCUMENT_ROOT"].'/templates/metadata.php';
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
                    <? include $_SERVER["DOCUMENT_ROOT"].'/templates/menu.php'; ?>
                </td>
                <td>
                    <? include $_SERVER["DOCUMENT_ROOT"].'/templates/body_top.php'; ?>

                    <? /* Body Area Start */ ?>

                    <p><?php echo Tr::trs('contacts.roles.administration', 'Project and survey administration'); ?>: Vilas Manjari Dasi<br />
                    <?php echo Tr::trs('word.email', 'E-Mail') ?>: <a href="mailto:vilasmanjari108@gmail.com">vilasmanjari108@gmail.com</a><br />
                    <?php echo Tr::trs('word.whatsapp', 'WhatsApp'); ?>: +905534440889</p>

                    <p><?php echo Tr::trs('contacts.roles.techical', 'Technical questions'); ?>: Giridhari Das<br />
                    <?php echo Tr::trs('word.email', 'E-Mail') ?>: <a href="mailto:sunrisecoder@gmail.com">sunrisecoder@gmail.com</a><br />
                    <?php echo Tr::trs('word.whatsapp', 'WhatsApp'); ?>: +79256237577</p>

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
