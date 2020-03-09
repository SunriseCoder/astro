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

    if (isset($error)) {
        $body_content = '<font color="red">'.$error.'</font><br /><br />';
    }

    include $_SERVER["DOCUMENT_ROOT"].'/templates/page.php';
