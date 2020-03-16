<?php
    // Used to add Test Users to Test server after copy data from Prod

    if (!class_exists('Db')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/db.php'; }
    if (!class_exists('LoginDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php'; }

    if (!LoginDao::checkPermissions(Permission::TechnicalAdmin) || Config::ENV == 'Prod') {
        LoginDao::checkPermissionsAndRedirect(Permission::TechnicalAdmin, './');
    }

    $roles = [];

    $users = [
        ['name' => 'Participant', 'email' => 'participant@test.com', 'pass' => '123', 'roles' => ['Participant']],
        ['name' => 'Astrologer', 'email' => 'astrologer@test.com', 'pass' => '123', 'roles' => ['Astrologer']],
        ['name' => 'Administrator', 'email' => 'administrator@test.com', 'pass' => '123', 'roles' => ['Administrator']],
        ['name' => 'TranslatorEnglish', 'email' => 'translator.english@test.com', 'pass' => '123', 'roles' => ['TranslatorEnglish']],
        ['name' => 'TranslatorRussian', 'email' => 'translator.russian@test.com', 'pass' => '123', 'roles' => ['TranslatorRussian']],
        ['name' => 'TranslatorItalian', 'email' => 'translator.italian@test.com', 'pass' => '123', 'roles' => ['TranslatorItalian']],
        ['name' => 'TranslatorSpanish', 'email' => 'translator.spanish@test.com', 'pass' => '123', 'roles' => ['TranslatorSpanish']],
        ['name' => 'TranslatorTurkish', 'email' => 'translator.turkish@test.com', 'pass' => '123', 'roles' => ['TranslatorTurkish']],
        ['name' => 'TranslatorGerman', 'email' => 'translator.german@test.com', 'pass' => '123', 'roles' => ['TranslatorGerman']],
        ['name' => 'TranslatorFrench', 'email' => 'translator.french@test.com', 'pass' => '123', 'roles' => ['TranslatorFrench']]
    ];

    $storedUsers = UserDao::getAll();
    foreach ($users as $user) {
        foreach ($storedUsers as $storedUser) {
            if ($user->email == $storedUser->email) {
                echo 'User with the E-Mail '.$user->email.' is already exists';
                continue 2;
            }
        }
        Db::beginTransaction(0, 'AddTestUsers');
        $sql = 'INSERT INTO users (name, email, pass, active) VALUES (?, ?, ?, TRUE)';
        Db::prepStmt($sql, 'sss', [$user['name'], $user['email'], md5($user['pass'])]);
        $userId = Db::insertedId();
        $sql = 'INSERT INTO j_users_roles (user_id, role_id) VALUES (?, (SELECT id FROM roles WHERE name = ?))';
        foreach ($user['roles'] as $roleName) {
            Db::prepStmt($sql, 'is', [$userId, $roleName]);
        }
        Db::commit();
        echo 'User '.$user['name'].' has been created<br />';
    }

    echo 'All users have been created';
?>
