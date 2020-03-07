<?php
    if (!class_exists('Permission')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php'; }
    if (!class_exists('Tr')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/i18n.php'; }

    $user = LoginDao::autologin();

    if ($user) {
        echo '<div class="dropdown">';
        echo '<button class="dropdownbtn">';
        echo $user->name.' <img src="/images/arrow-down.png" />';
        echo '</button>';
        // User Profile Menu
        echo '<div class="dropdown-content">';
        echo '<a href="/logout.php" class="drop-down">'.Tr::trs('word.logout', 'Logout').'</a>';
        echo '</div></div>';

    } else {
        ?>
        <script>
            function displayLoginForm() {
                console.log('show');
                document.getElementById('dropdown-form-content').style.display = 'block';
                document.getElementById('login-email-input').focus();
            }
            function hideLoginForm() {
                console.log('hide');
                document.getElementById('dropdown-form-content').style.display = 'none';
            }
            function processKeyDown(event) {
                if (event.keyCode == 13) { // Enter pressed
                    document.getElementById('popup-login-form').submit();
                }
            }
        </script>
        <div class="dropdown">
            <button class="dropdownbtn" onclick="displayLoginForm();" onmouseover="displayLoginForm();">
                <?php echo Tr::trs('word.signIn', 'Sign In'); ?>
                <img src="/images/arrow-down.png" />
            </button>
            <div id="dropdown-form-content" class="dropdown-content">
                <form id="popup-login-form" action="/login.php" method="POST">
                    <table>
                        <tr>
                            <td><?php echo Tr::trs('word.email', 'E-Mail'); ?>:</td>
                            <td>
                                <input id="login-email-input" name="email" type="text" />
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo Tr::trs('word.password', 'Password'); ?>:</td>
                            <td>
                                <input name="password" type="password" onkeydown="processKeyDown(event);" />
                            </td>
                        </tr>
                        <tr>
                            <!-- <td>
                                <input type="submit" value="<?php echo Tr::trs('word.signIn', 'Sign In'); ?>" />
                            </td> -->
                            <td colspan="2">
                                <a onclick="document.getElementById('popup-login-form').submit();"><?php echo Tr::trs('word.signIn', 'Sign In'); ?></a>
                                <a href="register.php"><?php echo Tr::trs('word.signUp', 'Sign Up'); ?></a>
                                <a onclick="hideLoginForm();"><?php echo Tr::trs('word.cancel', 'Cancel'); ?></a>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>
    <?php
    }
?>
