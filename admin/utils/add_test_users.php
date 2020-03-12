<?php
    // Used to add Test Users to Test server after copy data from Prod

    if (!class_exists('LoginDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php'; }
    LoginDao::checkPermissionsAndRedirect(Permission::TechnicalChange, './');

    if (!class_exists('Db')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/db.php'; }

    $roles = [];

    $users = [
        ['name' => 'Participant', 'email' => 'participant@test.com', 'pass' => '123', 'roles' => ['Participant']],
        ['name' => 'Astrologer', 'email' => 'astrologer@test.com', 'pass' => '123', 'roles' => ['Astrologer']],
        ['name' => 'Administrator', 'email' => 'administrator@test.com', 'pass' => '123', 'roles' => ['Administrator']],
        ['name' => 'TranslatorEnglish', 'email' => 'translator.english@test.com', 'pass' => '123', 'roles' => ['TranslatorEnglish']],
        ['name' => 'TranslatorRussian', 'email' => 'translator.russian@test.com', 'pass' => '123', 'roles' => ['TranslatorRussian']],
        ['name' => 'TranslatorTurkish', 'email' => 'translator.turkish@test.com', 'pass' => '123', 'roles' => ['TranslatorTurkish']]
    ];

    foreach ($users as $user) {
        Db::beginTransaction(0, 'AddTestUsers');
        $sql = 'INSERT INTO users (name, email, pass, active) VALUES (?, ?, ?, TRUE)';
        Db::prepStmt($sql, 'sss', [$user['name'], $user['email'], md5($user['pass'])]);
        $userId = Db::insertedId();
        $sql = 'INSERT INTO j_users_roles (user_id, role_id) VALUES (?, (SELECT id FROM roles WHERE name = ?))';
        foreach ($user['roles'] as $roleName) {
            Db::prepStmt($sql, 'is', [$userId, $roleName]);
        }
        Db::commit();
        echo 'User: '.$user['name'].' has been created<br />';
    }

    echo 'All users have been created';
?>
