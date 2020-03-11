<?php
    include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php';

    if (LoginDao::isLogged()) {
        header("Location: /", true);
        exit;
    }

    if (!class_exists('Tr')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/i18n.php'; }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['email']) && isset($_POST['password'])) {
            $email = $_POST['email'];
            $pass = $_POST['password'];
            $error = LoginDao::login($email, $pass);
            if (!$error) {
                header("Location: /", true);
                exit;
            }
        }
    }

    $browser_title = Tr::trs('page.common.browserTitle', 'Astrology - Chaitanya Academy');
    $page_title = Tr::trs('page.login.pageTitle', 'Login');
    $body_content = '';

    if (isset($error)) {
        $body_content = '<font color="red">'.$error.'</font><br /><br />';
    }

    $body_content .= '<div class="centered-content">
                        <form id="login-form" action="" method="POST">
                            <table>
                                <tr>
                                    <td>'.Tr::trs('word.email', 'E-Mail').':</td>
                                    <td>
                                        <input name="email" type="text" />
                                    </td>
                                </tr>
                                <tr>
                                    <td>'.Tr::trs('word.password', 'Password').':</td>
                                    <td>
                                        <input name="password" type="password" />
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" align="center">
                                        <a class="button" onclick="document.getElementById(\'login-form\').submit();">'.Tr::trs('word.signIn', 'Sign In').'</a>
                                        <a class="button" href="register.php">'.Tr::trs('word.signUp', 'Sign Up').'</a>
                                    </td>
                                </tr>
                            </table>
                        </form>
                    </div>';

    include $_SERVER["DOCUMENT_ROOT"].'/templates/page.php';
