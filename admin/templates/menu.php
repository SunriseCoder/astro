<?php
    if (!class_exists('Tr')) {
        include $_SERVER["DOCUMENT_ROOT"].'/utils/i18n.php';
    }

    echo '<ul>';
    echo '<li><a href="../">'.Tr::trs('menu.main', 'Main').'</a></li>';
    echo '<li><a href="./">'.Tr::trs('menu.admin', 'Admin').'</a></li>';
    echo '<li><a href="answer_sessions_list.php">'.Tr::trs('menu.admin.answerSessions', 'Answer Sessions').'</a></li>';
    echo '<li><a href="questionnaires_list.php">'.Tr::trs('menu.admin.questionnaires', 'Questionnaires').'</a></li>';
    echo '<li><a href="questions_list.php">'.Tr::trs('menu.admin.questions', 'Questions').'</a></li>';
    echo '<li><a href="question_types_list.php">'.Tr::trs('menu.admin.questionTypes', 'Question Types').'</a></li>';
    echo '<li><a href="translation.php">'.Tr::trs('menu.admin.translation', 'Translation').'</a></li>';
    echo '<li><a href="users.php">'.Tr::trs('menu.admin.users', 'Users').'</a></li>';
    echo '</ul>';
?>
