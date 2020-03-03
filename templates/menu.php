<ul>
    <?php
        if (!class_exists('Tr')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/i18n.php'; }

        echo '<li><a href="./">'.Tr::trs('menu.main', 'Main').'</a></li>';

        if (LoginDao::checkPermissions([Permission::AdminMenuVisible])) {
            echo '<li><a href="admin/">'.Tr::trs('menu.admin', 'Admin').'</a></li>';
        }

        if (LoginDao::isLogged()) {
            echo '<li><a href="questions.php">'.Tr::trs('menu.survey', 'Survey').'</a></li>';
        }

        if (LoginDao::checkPermissions([Permission::AstrologerAnswering])) {
            echo '<li><a href="astrologer_choose.php">'.Tr::trs('menu.astrologer', 'Astrologer').'</a></li>';
        }

        echo '<li><a href="contacts.php">'.Tr::trs('menu.contacts', 'Contacts').'</a></li>';
    ?>
</ul>
