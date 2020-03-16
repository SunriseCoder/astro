<?php
    if (!class_exists('Tr')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/i18n.php'; }

    echo '<table>';
    echo '<tr><td><a href="../">'.Tr::trs('menu.main', 'Main').'</a></td></tr>';
    echo '<tr><td><a href="./">'.Tr::trs('menu.admin', 'Admin').'</a></td></tr>';
    if (LoginDao::checkPermissions(Permission::AnswerSessionsView)) {
        echo '<tr><td><a href="answer_sessions_list.php">'.Tr::trs('menu.admin.answerSessions', 'Answer Sessions').'</a></td></tr>';
    }
    if (LoginDao::checkPermissions(Permission::QuestionsView)) {
        echo '<tr><td><a href="questionnaires_list.php">'.Tr::trs('menu.admin.questionnaires', 'Questionnaires').'</a></td></tr>';
        echo '<tr><td><a href="questions_list.php">'.Tr::trs('menu.admin.questions', 'Questions').'</a></td></tr>';
        echo '<tr><td><a href="question_types_list.php">'.Tr::trs('menu.admin.questionTypes', 'Question Types').'</a></td></tr>';
    }
    if (LoginDao::checkPermissions(Permission::TranslationsView)) {
        echo '<tr><td><a href="translation.php">'.Tr::trs('menu.admin.translation', 'Translation').'</a></td></tr>';
    }
    if (LoginDao::checkPermissions(Permission::UsersView)) {
        echo '<tr><td><a href="users.php">'.Tr::trs('menu.admin.users', 'Users').'</a></td></tr>';
    }
    if (LoginDao::checkPermissions(Permission::DatabaseBackup)) {
        echo '<tr><td><a href="database_backups.php">'.Tr::trs('menu.admin.database', 'Databases').'</a></td></tr>';
    }
    echo '</table>';
?>
