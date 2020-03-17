<?php
    if (!class_exists('LoginDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php'; }
    LoginDao::checkPermissionsAndRedirect(Permission::TechnicalView, './');

    phpinfo();
?>
