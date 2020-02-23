<ul>
    <?php
        $user = LoginDao::getCurrentUser();

        echo "<li><a href=\"./\">Main</a></li>\n";

        if ($user && $user->hasPermission(Permission::AdminMenuVisible)) {
            echo "<li><a href=\"admin/\">Admin</a></li>";
        }

        if ($user) {
            echo "<li><a href=\"questions.php\">Survey</a></li>";
        }

        if ($user && $user->hasPermission(Permission::AstrologerAnswering)) {
            echo "<li><a href=\"astrologer_choose.php\">Astrologer</a></li>";
        }

        echo "<li><a href=\"contacts.php\">Contacts</a></li>\n";
    ?>
</ul>
