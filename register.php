<?php
    if (!class_exists('Tr')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/i18n.php'; }
?>
<html>
    <?
        $browser_title = Tr::trs('page.common.browserTitle', 'Astrology - Chaitanya Academy');
        $page_title = Tr::trs('page.register.pageTitle', 'Registration');

        include $_SERVER["DOCUMENT_ROOT"].'/templates/metadata.php';
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
                    <? include $_SERVER["DOCUMENT_ROOT"].'/templates/menu.php'; ?>
                </td>
                <td>
                    <? include $_SERVER["DOCUMENT_ROOT"].'/templates/body_top.php'; ?>

                    <? /* Body Area Start */ ?>

                    <?php
                        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            $error = UserDao::register($_POST['name'], $_POST['email']);
                            if ($error) {
                                echo '<font color="red">'.$error.'</font>';
                            } else {
                                echo '<font color="green">'.Tr::trs('page.register.registerSuccessfully', 'Your password has been sent via E-Mail').'</font>';
                            }
                        }
                    ?>

                    <form action="register.php" method="POST">
                        <table>
                            <tr>
                                <td><?php echo Tr::trs('word.name', 'Name'); ?>:</td>
                                <td>
                                    <input name="name" type="text" />
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo Tr::trs('word.email', 'E-Mail'); ?>:</td>
                                <td>
                                    <input name="email" type="text" />
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" align="center">
                                    <input type="submit" value="<?php echo Tr::trs('word.register', 'Register'); ?>" />
                                </td>
                            </tr>
                        </table>
                    </form>

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
