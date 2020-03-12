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
        $languages = LanguageDao::getAll();
        $statistics = TranslationDao::getStatisticMapByLanguageIds();

        $tableModel = new TableModel();
        $tableModel->title = 'Translation Statistic';
        $tableModel->header = ['Language', 'Amount', 'Percentage'];

        foreach ($languages as $language) {
            $amount = isset($statistics[$language->id]) ? $statistics[$language->id] : 0;
            $tableModel->data []= [$language->nameEnglish, $amount.'/'.$keywordsCount, round(100 * $amount / $keywordsCount).'%'];
        }

        $body_content .= HTMLRender::renderTable($tableModel, 'admin-table');
    }

    include $_SERVER["DOCUMENT_ROOT"].'/admin/templates/page.php';
