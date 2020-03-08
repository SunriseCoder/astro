<?php
    if (!class_exists('Tr')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/i18n.php'; }

    $browser_title = Tr::trs('page.common.browserTitle', 'Astrology - Chaitanya Academy');
    $page_title = Tr::trs('page.index.pageTitle', 'The Chaitanya Academy ASTRO-PROJECT');
    $body_content = Tr::trs('page.index.text.surveyIntroduction');

    include $_SERVER["DOCUMENT_ROOT"].'/templates/page.php';
