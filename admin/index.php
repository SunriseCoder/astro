<?php
    if (!class_exists('LoginDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php'; }
    LoginDao::checkPermissionsAndRedirect([Permission::AdminMenuVisible], '../');

    $browser_title = 'Chaitanya Academy - Astrology';
    $page_title = 'Astrology - Administration';
    $body_content = '<div class="centered-content">Hello!<br />Welcome to the Administration page</div>';

    include $_SERVER["DOCUMENT_ROOT"].'/admin/templates/page.php';
