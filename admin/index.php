<?php
    if (!class_exists('LoginDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php'; }
    LoginDao::checkPermissionsAndRedirect(Permission::AdminMenuVisible, '../');

    if (!class_exists('HTMLRender')) { include $_SERVER["DOCUMENT_ROOT"].'/render/html.php'; }

    $browser_title = 'Chaitanya Academy - Astrology';
    $page_title = 'Astrology - Administration';
    $body_content = '<div class="centered-content">Hello!<br />Welcome to the Administration page</div>';

    // Translation Statistic Table
    if (LoginDao::checkPermissions(Permission::TranslationsView)) {
        $keywordsCount = KeywordDao::getCountAll();
        $statistics = TranslationDao::getStatisticMapByLanguageIds();

        $tableModel = new TableModel();
        $tableModel->title = 'Translation Statistic';
        $tableModel->header = ['Language', '24 Hours', '3 Days', '7 Days', '30 Days', 'Total'];

        function mkCellData($amount, $total) {
            $value = $amount.'/'.$total.' ('.round(100 * $amount / $total).'%)';
            return $value;
        }
        foreach ($statistics as $row) {

            $tableModel->data []= [$row['language_name'], mkCellData($row['1day_count'], $keywordsCount), mkCellData($row['3days_count'], $keywordsCount),
                mkCellData($row['7days_count'], $keywordsCount), mkCellData($row['30days_count'], $keywordsCount), mkCellData($row['total_count'], $keywordsCount)];
        }

        $body_content .= HTMLRender::renderTable($tableModel, 'admin-table');
    }

    include $_SERVER["DOCUMENT_ROOT"].'/admin/templates/page.php';
