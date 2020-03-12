<?php
    if (!class_exists('Tr')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/i18n.php'; }

    echo '<a href="./">'.Tr::trs('menu.main', 'Main').'</a>';

    if (LoginDao::checkPermissions(Permission::AdminMenuVisible)) {
        echo '<a href="admin/">'.Tr::trs('menu.admin', 'Admin').'</a>';
    }

    if (LoginDao::isLogged()) {
        echo '<a href="survey.php">'.Tr::trs('menu.survey', 'Survey').'</a>';
    }

    if (LoginDao::checkPermissions(Permission::AstrologerAnswering)) {
        echo '<a href="astrologer_choose.php">'.Tr::trs('menu.astrologer', 'Astrologer').'</a>';
    }

    echo '<a href="contacts.php">'.Tr::trs('menu.contacts', 'Contacts').'</a>';
?>
