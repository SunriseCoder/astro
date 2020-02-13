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
                            if (isset($_POST['name']) && isset($_POST['email'])) {
                                $name = $_POST['name'];
                                $email = $_POST['email'];
                                $nameIsFree = UserDao::isNameFree($name);
                                if (!$nameIsFree) {
                                    echo '<font color="red">Name is already in use.</font>';
                                }

                                $emailIsFree = UserDao::isEmailFree($email);
                                if (!$emailIsFree) {
                                    echo '<font color="red">E-mail is already in use.</font>';
                                }

                                $user = new User();
                                $user->name = $name;
                                $user->email = $email;
                                $user->generatePassword();
                                $result = UserDao::create($user);

                                if ($result) {
                                    echo '<font color="green">Your password has been sent via E-Mail.</font>';
                                } else {
                                    echo '<font color="red">Could not create new user, please contact administrator.</font>';
                                }
                            } else {
                                echo '<font color="red">Name or E-mail is empty.</font>';
                            }
                        }
                    ?>

                    <h1>Registration</h1>

                    <form action="register.php" method="POST">
                        <table>
                            <tr>
                                <td>Name:</td>
                                <td>
                                    <input name="name" type="text" />
                                </td>
                            </tr>
                            <tr>
                                <td>E-Mail:</td>
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
