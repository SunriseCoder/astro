<div class="login">
    <?php
        if (!class_exists('Permission')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php'; }
        if (!class_exists('Tr')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/i18n.php'; }

        $user = LoginDao::autologin();

        if ($user) {
            echo Tr::format('phrases.welcome', [$user->name], 'Welcome, {0}');
            echo ' (<a href="/logout.php">'.Tr::trs('word.logout', 'Logout').'</a>)';
        } else {
    ?>
        <form action="login.php" method="POST">
            <table>
                <tr>
                    <td><?php echo Tr::trs('word.email', 'E-Mail'); ?>:</td>
                    <td>
                        <input name="email" type="text" />
                    </td>
                </tr>
                <tr>
                    <td><?php echo Tr::trs('word.password', 'Password'); ?>:</td>
                    <td>
                        <input name="password" type="password" />
                    </td>
                </tr>
                <tr>
                    <td>
                        <input type="submit" value="<?php echo Tr::trs('word.signIn', 'Sign In'); ?>" />
                    </td>
                    <td>
                        <a href="register.php"><?php echo Tr::trs('word.signUp', 'Sign Up'); ?></a>
                    </td>
                </tr>
            </table>
        </form>
    <?php
    }
    ?>
</div>
