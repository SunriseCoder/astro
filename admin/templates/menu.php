<?php
    if (!class_exists('Tr')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/i18n.php'; }

    echo '<table>';
    // Main
    echo '<tr><td><a href="../">'.Tr::trs('menu.main', 'Main').'</a></td></tr>';

    // Admin
    echo '<tr><td><a href="./">'.Tr::trs('menu.admin', 'Admin').'</a></td></tr>';

    // Answers Groups
    if (LoginDao::checkPermissions(Permission::AnswersView)) {
        echo '<tr><td><a href="participant_answers_list.php">'.Tr::trs('menu.admin.participantAnswers', 'Participant Answers').'</a></td></tr>';
        echo '<tr><td><a href="astrologer_answers_list.php">'.Tr::trs('menu.admin.astrologerAnswers', 'Astrologer Answers').'</a></td></tr>';
    }

    // Questions Group
    if (LoginDao::checkPermissions(Permission::QuestionsView)) {
        echo '<tr><td><a href="questionnaires_list.php">'.Tr::trs('menu.admin.questionnaires', 'Questionnaires').'</a></td></tr>';
        echo '<tr><td><a href="questions_list.php">'.Tr::trs('menu.admin.questions', 'Questions').'</a></td></tr>';
        echo '<tr><td><a href="question_types_list.php">'.Tr::trs('menu.admin.questionTypes', 'Question Types').'</a></td></tr>';
    }

    // Translations
    if (LoginDao::checkPermissions(Permission::TranslationsView)) {
        echo '<tr><td><a href="translation.php">'.Tr::trs('menu.admin.translation', 'Translation').'</a></td></tr>';
    }

    // Users
    if (LoginDao::checkPermissions(Permission::UsersView)) {
        echo '<tr><td><a href="users.php">'.Tr::trs('menu.admin.users', 'Users').'</a></td></tr>';
    }

    // Databases
    if (LoginDao::checkPermissions(Permission::DatabaseBackup)) {
        echo '<tr><td><a href="database_backups.php">'.Tr::trs('menu.admin.database', 'Databases').'</a></td></tr>';
    }

    // Server Info
    if (LoginDao::checkPermissions(Permission::TechnicalView)) {
        echo '<tr><td><a href="server_info.php">'.Tr::trs('menu.admin.serverInfo', 'Server Info').'</a></td></tr>';
    }
    echo '</table>';
?>
