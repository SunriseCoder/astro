<?php
    if (!class_exists('LoginDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php'; }
    LoginDao::checkPermissionsAndRedirect(Permission::DatabaseBackup, './');

    if (!class_exists('HTMLRender')) { include $_SERVER["DOCUMENT_ROOT"].'/render/html.php'; }

    $browser_title = 'Chaitanya Academy - Questionnaires';
    $page_title = 'Database - Administration';
    $body_content = '';

    if (isset($_GET['action'])) {
        if ($_GET['action'] == 'backup') {
            // Database Real Backup
            Db::backup($output, $status);
            if ($status == 0) {
                $body_content .= '<font color="green">Backup created successfully</font><br />';
            } else {
                $body_content .= '<font color="red">An Error occured during the Backup: '.$status.'</font><br />';
            }

            $body_content .= 'Command execution log:<br />';
            echo implode($output);
        } else if ($_GET['action'] == 'restore' && isset($_GET['file']) && !empty($_GET['file']) && file_exists($_GET['file'])) {
            // Restore Database
            $file = $_GET['file'];
            Db::backup(); // Just in case
            Db::restore($file, $output, $status);
            if ($status == 0) {
                $testUsersFile = $_SERVER["DOCUMENT_ROOT"].'/config/add_test_users.php';
                if (file_exists($testUsersFile)) {
                    include $testUsersFile;
                }
                $body_content .= '<font color="green">Database restored successfully</font><br />';
            } else {
                $body_content .= '<font color="red">An Error occured during the Restore Database ('.$file.'): '.$status.'</font><br />';
            }

            $body_content .= 'Command execution log:<br />';
            echo implode($output);
        }
    }

    // Create Backup
    $body_content .= '<form action="" method="GET">
                        <input type="hidden" name="action" value="backup" />
                        <input type="submit" value="Create Backup" />
                    </form>';

    // Backup File Tables
    foreach (Config::DB_BACKUP_LOAD as $env => $path) {
        // Scan folder
        $files = glob($_SERVER["DOCUMENT_ROOT"].$path.'/*.sql.gz');
        if ($files) {
            $tableModel = new TableModel();
            $tableModel->title = $env;
            $tableModel->header = ['File', 'Size', 'Actions'];
            foreach ($files as $file) {
                $size = NumberUtils::humanReadableSize(filesize($file)).'b';
                $actions = '<a href="?action=restore&file='.$file.'" onclick="return confirm(\'Are you sure to restore '.$file.'?\');">Restore</a>';
                $tableModel->data []= [$file, $size, $actions];
            }
            $body_content .= HTMLRender::renderTable($tableModel, 'admin-table');
        }
    }

    include $_SERVER["DOCUMENT_ROOT"].'/admin/templates/page.php';
?>
