<?php
    include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php';

    setcookie(LoginDao::COOKIE_NAME);
    header("Location: /", true);
    exit;
?>
