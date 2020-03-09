<html>
    <?php
        if (!isset($css_includes)) { $css_includes = []; }
        $css_includes []= '/admin/css/styles.css';
        include $_SERVER["DOCUMENT_ROOT"].'/templates/metadata.php';
    ?>
    <body>
        <table id="page-markup-table">
            <tr>
                <td id="page-top" colspan="2">
                    <?php include $_SERVER["DOCUMENT_ROOT"].'/templates/page_top.php'; ?>
                </td>
            </tr>
            <tr>
                <td id="page-menu-admin">
                    <?php include $_SERVER["DOCUMENT_ROOT"].'/admin/templates/menu.php'; ?>
                </td>
                <td id="page-body-admin">
                    <?php
                        include $_SERVER["DOCUMENT_ROOT"].'/templates/body_top.php';
                        if (isset($body_content)) {
                            echo $body_content;
                        }
                    ?>
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
