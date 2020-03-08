<html>
    <?php include $_SERVER["DOCUMENT_ROOT"].'/templates/metadata.php'; ?>
    <body>
        <table id="page-markup-table">
            <tr>
                <td id="page-top">
                    <?php include $_SERVER["DOCUMENT_ROOT"].'/templates/page_top.php'; ?>
                </td>
            </tr>
            <tr>
                <td id="page-menu">
                    <?php include $_SERVER["DOCUMENT_ROOT"].'/templates/menu.php'; ?>
                </td>
            </tr>
            <tr>
                <td id="page-body">
                    <?php
                        include $_SERVER["DOCUMENT_ROOT"].'/templates/body_top.php';
                        if (isset($page_title)) {
                            echo '<p class="page-title">'.$page_title.'</p>';
                        }
                        if (isset($body_content)) {
                            echo $body_content;
                        }
                    ?>
                </td>
            </tr>
            <tr>
                <td id="page-footer">
                    <? include $_SERVER["DOCUMENT_ROOT"].'/templates/page_footer.php'; ?>
                </td>
            </tr>
        </table>
    </body>
</html>
