<?php
    if (!class_exists('Tr')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/i18n.php'; }

    $browser_title = Tr::trs('page.common.browserTitle', 'Astrology - Chaitanya Academy');
    $page_title = Tr::trs('page.contacts.pageTitle', 'Contacts');

    $body_content = '<p>'.Tr::trs('contacts.roles.administration', 'Project and survey administration').'Vilas Manjari Dasi<br />';
    $body_content .= Tr::trs('word.email', 'E-Mail').': <a href="mailto:vilasmanjari108@gmail.com">vilasmanjari108@gmail.com</a><br />';
    $body_content .= Tr::trs('word.whatsapp', 'WhatsApp').': +905534440889</p>';

    $body_content .= '<p>'.Tr::trs('contacts.roles.techical', 'Technical questions').': Giridhari Das<br />';
    $body_content .= Tr::trs('word.email', 'E-Mail').': <a href="mailto:sunrisecoder@gmail.com">sunrisecoder@gmail.com</a><br />';
    $body_content .= Tr::trs('word.whatsapp', 'WhatsApp').': +79256237577</p>';

    include $_SERVER["DOCUMENT_ROOT"].'/templates/page.php';
