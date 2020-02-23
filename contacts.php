<html>
    <?
        $browser_title = 'Chaitanya Academy - Astrology';
        $page_title = 'Contacts';

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

                    <p>Project and survey administration: Vilas Manjari Dasi<br />
                    E-Mail: <a href="mailto:vilasmanjari108@gmail.com">vilasmanjari108@gmail.com</a><br />
                    WhatsApp: +905534440889</p>

                    <p>Technical questions: Giridhari Das<br />
                    E-Mail: <a href="mailto:sunrisecoder@gmail.com">sunrisecoder@gmail.com</a><br />
                    WhatsApp: +79256237577</p>

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
