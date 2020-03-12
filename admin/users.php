<?php
    if (!class_exists('LoginDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php'; }
    LoginDao::checkPermissionsAndRedirect(Permission::UsersView, './');

    $browser_title = 'Chaitanya Academy - Astrology';
    $page_title = 'Users View';
    $body_content = '';

    // Users Table
    $body_content .= '<h3>Users</h3>';
    $users = UserDao::getAll();
    if (count($users) > 0) {
        $body_content .= '<table class="admin-table">';
        $body_content .= '<th>ID</th>';
        $body_content .= '<th>Name</th>';
        $body_content .= '<th>E-Mail</th>';
        $body_content .= '<th>Active</th>';
        $body_content .= '<th>Roles</th>';
        $body_content .= '<th>Permissions</th>';

        foreach ($users as $user) {
            $body_content .= '<tr>';
            $body_content .= '<td>'.$user->id.'</td>';
            $body_content .= '<td>'.$user->name.'</td>';
            $body_content .= '<td>'.$user->email.'</td>';
            $body_content .= '<td>'.$user->active.'</td>';
            // Roles
            $body_content .= '<td>';
            foreach ($user->roles as $role) {
                $body_content .= $role->id.': '.$role->name.'<br />';
            }
            $body_content .= '</td>';
            // Permissions
            $body_content .= '<td>';
            foreach ($user->permissions as $permission) {
                $body_content .= $permission->id.': '.$permission->code.'<br />';
            }
            $body_content .= '</td>';
            $body_content .= '</tr>';
        }

        $body_content .= '</table>';
    } else {
        $body_content .= 'No Users was found.';
    }
    $body_content .= '<br />';

    // Roles and Permissions Markup Table
    $body_content .= '<table>';
    $body_content .= '<tr>';
    $body_content .= '<th><h3>Roles</h3></th>';
    $body_content .= '<th><h3>Permissions</h3></th>';
    $body_content .= '</tr><tr>';
    $body_content .= '<td valign="top">';

    // Roles Table
    $roles = RoleDao::getAll();
    if (count($roles) > 0) {
        $body_content .= '<table class="admin-table">';
        $body_content .= '<th>ID</th>';
        $body_content .= '<th>Name</th>';
        $body_content .= '<th>Permissions</th>';

        foreach ($roles as $role) {
            $body_content .= '<tr>';
            $body_content .= '<td>'.$role->id.'</td>';
            $body_content .= '<td>'.$role->name.'</td>';
            // Permissions
            $body_content .= '<td>';
            foreach ($role->permissions as $permission) {
                $body_content .= $permission->id.': '.$permission->code.'<br />';
            }
            $body_content .= '</td>';
            $body_content .= '</tr>';
        }

        $body_content .= '</table>';
    } else {
        $body_content .= 'No Roles was found.';
    }

    $body_content .= '</td><td valign="top">';

    // Permissions Table
    $permissions = PermissionDao::getAll();
    if (count($permissions) > 0) {
        $body_content .= '<table class="admin-table">';
        $body_content .= '<th>ID</th>';
        $body_content .= '<th>Code</th>';

        foreach ($permissions as $permission) {
            $body_content .= '<tr>';
            $body_content .= '<td>'.$permission->id.'</td>';
            $body_content .= '<td>'.$permission->code.'</td>';
            $body_content .= '</tr>';
        }

        $body_content .= '</table>';
    } else {
        $body_content .= 'No Permissions was found.';
    }

    $body_content .= '</td></tr></table>';

    include $_SERVER["DOCUMENT_ROOT"].'/admin/templates/page.php';
