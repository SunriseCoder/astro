<html>
    <?
        $browser_title = 'Chaitanya Academy - Astrology';
        $page_title = 'Astrology';

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

                    <?php
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        if (isset($_POST['email'])) {
                            $email = $_POST['email'];
                            $emailIsFree = UserDao::isEmailFree($email);
                            if ($emailIsFree) {
                                $user = new User();
                                $user->email = $email;
                                $user->generatePassword();
                                UserDao::create($user);
                                echo '<font color="green">Your password has been sent via E-Mail.</font>';
                            } else {
                                echo '<font color="red">E-mail is already in use.</font>';
                            }
                        } else {
                            echo '<font color="red">E-mail is empty.</font>';
                        }
                    }
                    ?>

                    <h1>Registration</h1>

                    <form action="register.php" method="POST">
                        <table>
                            <tr>
                                <td>e-mail:</td>
                                <td>
                                    <input name="email" type="text" />
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" align="center">
                                    <input type="submit" value="Register" />
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
