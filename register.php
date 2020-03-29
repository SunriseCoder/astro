<?php
    if (!class_exists('Tr')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/i18n.php'; }

    $browser_title = Tr::trs('page.common.browserTitle', 'Astrology - Chaitanya Academy');
    $page_title = Tr::trs('page.register.pageTitle', 'Registration');

    $body_content = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $result = UserDao::register($_POST['name'], $_POST['email']);
        if ($result) {
            $body_content .= '<font color="red">'.$result.'</font>';
        } else {
            $body_content .= '<font color="green">'.Tr::trs('page.register.registerSuccessfully', 'Your password has been sent via E-Mail').'</font>';
        }
    } else {
        $body_content .= '<div class="centered-content">
                            <form action="register.php" method="POST">
                                <table>
                                    <tr>
                                        <td>'.Tr::trs('word.name', 'Name').':</td>
                                        <td>
                                            <input name="name" type="text" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>'.Tr::trs('word.email', 'E-Mail').':</td>
                                        <td>
                                            <input name="email" type="text" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" align="center">
                                            <input type="submit" value="'.Tr::trs('word.register', 'Register').'" />
                                        </td>
                                    </tr>
                                </table>
                            </form>
                        </div>';
    }

    include $_SERVER["DOCUMENT_ROOT"].'/templates/page.php';
