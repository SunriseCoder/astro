<div class="login">
    <?php
    if (!class_exists('Permission')) {
        include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php';
    }

    $user = LoginDao::autologin();

    if ($user) {
        echo 'Welcome, '.$user->name;
        echo ' (<a href="/logout.php">Logout</a>)';
    } else {
    ?>
        <form action="login.php" method="POST">
            <table>
                <tr>
                    <td>e-mail:</td>
                    <td>
                        <input name="email" type="text" />
                    </td>
                </tr>
                <tr>
                    <td>password:</td>
                    <td>
                        <input name="password" type="password" />
                    </td>
                </tr>
                <tr>
                    <td>
                        <input type="submit" value="Sign In" />
                    </td>
                    <td>
                        <a href="register.php">Sign Up</a>
                    </td>
                </tr>
            </table>
        </form>
    <?php
    }
    ?>
</div>
