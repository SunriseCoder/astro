<?php
    if (!class_exists('LoginDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php'; }
    LoginDao::checkPermissionsAndRedirect([Permission::UsersView], './');
?>
<html>
    <?
        $browser_title = 'Chaitanya Academy - Astrology';
        $page_title = 'Users View';

        include $_SERVER["DOCUMENT_ROOT"].'/admin/templates/metadata.php';
    ?>

    <body>
        <table>
            <tr>
                <td colspan="2">
                    <? include $_SERVER["DOCUMENT_ROOT"].'/templates/page_top.php'; ?>
                </td>
            </tr>
            <tr>
                <td class="menu">
                    <? include $_SERVER["DOCUMENT_ROOT"].'/admin/templates/menu.php'; ?>
                </td>
                <td>
                    <? include $_SERVER["DOCUMENT_ROOT"].'/templates/body_top.php'; ?>

                    <? /* Body Area Start */ ?>

                    <?php
                        // Users Table
                        echo '<h3>Users</h3>';
                        $users = UserDao::getAll();
                        if (count($users) > 0) {
                            echo '<table border="1">';
                            echo '<th>ID</th>';
                            echo '<th>Name</th>';
                            echo '<th>E-Mail</th>';
                            echo '<th>Active</th>';
                            echo '<th>Roles</th>';
                            echo '<th>Permissions</th>';

                            foreach ($users as $user) {
                                echo '<tr>';
                                echo '<td>'.$user->id.'</td>';
                                echo '<td>'.$user->name.'</td>';
                                echo '<td>'.$user->email.'</td>';
                                echo '<td>'.$user->active.'</td>';
                                // Roles
                                echo '<td>';
                                foreach ($user->roles as $role) {
                                    echo $role->id.': '.$role->name.'<br />';
                                }
                                echo '</td>';
                                // Permissions
                                echo '<td>';
                                foreach ($user->permissions as $permission) {
                                    echo $permission->id.': '.$permission->code.'<br />';
                                }
                                echo '</td>';
                                echo '</tr>';
                            }

                            echo '</table>';
                        } else {
                            echo 'No Users was found.';
                        }
                        echo '<br />';

                        // Roles and Permissions Markup Table
                        echo '<table>';
                        echo '<tr>';
                        echo '<th><h3>Roles</h3></th>';
                        echo '<th><h3>Permissions</h3></th>';
                        echo '</tr><tr>';
                        echo '<td valign="top">';

                        // Roles Table
                        $roles = RoleDao::getAll();
                        if (count($roles) > 0) {
                            echo '<table border="1">';
                            echo '<th>ID</th>';
                            echo '<th>Name</th>';
                            echo '<th>Permissions</th>';

                            foreach ($roles as $role) {
                                echo '<tr>';
                                echo '<td>'.$role->id.'</td>';
                                echo '<td>'.$role->name.'</td>';
                                // Permissions
                                echo '<td>';
                                foreach ($role->permissions as $permission) {
                                    echo $permission->id.': '.$permission->code.'<br />';
                                }
                                echo '</td>';
                                echo '</tr>';
                            }

                            echo '</table>';
                        } else {
                            echo 'No Roles was found.';
                        }

                        echo '</td><td valign="top">';

                        // Permissions Table
                        $permissions = PermissionDao::getAll();
                        if (count($permissions) > 0) {
                            echo '<table border="1">';
                            echo '<th>ID</th>';
                            echo '<th>Code</th>';

                            foreach ($permissions as $permission) {
                                echo '<tr>';
                                echo '<td>'.$permission->id.'</td>';
                                echo '<td>'.$permission->code.'</td>';
                                echo '</tr>';
                            }

                            echo '</table>';
                        } else {
                            echo 'No Permissions was found.';
                        }

                        echo '</td></tr></table>';
                    ?>

                    <? /* Body Area End */ ?>

                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <? include $_SERVER["DOCUMENT_ROOT"].'/templates/page_footer.php'; ?>
                </td>
            </tr>
        </table>
    </body>
</html>
