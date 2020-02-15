<?php
    include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php';
?>
<html>
    <?
        $browser_title = 'Chaitanya Academy - Astrology';
        $page_title = 'User View';

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
                                    echo $role->name.'<br />';
                                }
                                echo '</td>';
                                // Permissions
                                echo '<td>';
                                foreach ($user->permissions as $permission) {
                                    echo $permission->code.'<br />';
                                }
                                echo '</td>';
                                echo '</tr>';
                            }

                            echo '</table>';
                        } else {
                            echo 'No Users was found.';
                        }

                        // Roles Table
                        echo '<h3>Roles</h3>';
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
                                    echo $permission->code.'<br />';
                                }
                                echo '</td>';
                                echo '</tr>';
                            }

                            echo '</table>';
                        } else {
                            echo 'No Roles was found.';
                        }
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
