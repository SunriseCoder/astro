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
    ?>
</ul>
