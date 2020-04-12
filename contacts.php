<?php
    if (!class_exists('Tr')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/i18n.php'; }

    $browser_title = Tr::trs('page.common.browserTitle', 'Astrology - Chaitanya Academy');
    $page_title = Tr::trs('page.contacts.pageTitle', 'Contacts');

    $body_content = '<div class="centered-content">';

    $body_content .= '<table><tr><td>'.Tr::trs('contacts.roles.administration', 'Administration').':</td><td>Vilas Manjari Dasi</td></tr>';
    $body_content .= '<tr><td>'.Tr::trs('word.email', 'E-Mail').':</td><td><a href="mailto:vilasmanjari108@gmail.com">vilasmanjari108@gmail.com</a></td></tr>';
    $body_content .= '<tr><td>'.Tr::trs('word.whatsapp', 'WhatsApp').':</td><td>+90 553 444 08 89</td></tr>';
    $body_content .= '<tr><td>&nbsp;</td></tr>';

    $body_content .= '<tr><td>'.Tr::trs('contacts.roles.techical', 'Technical questions').':</td><td>Giridhari Das</td></tr>';
    $body_content .= '<tr><td>'.Tr::trs('word.email', 'E-Mail').':</td><td><a href="mailto:sunrisecoder@gmail.com">sunrisecoder@gmail.com</a></td></tr>';
    $body_content .= '<tr><td>'.Tr::trs('word.whatsapp', 'WhatsApp').':</td><td>+7 925 623 75 77</td></tr></table>';

    $body_content .= '</div>';

    include $_SERVER["DOCUMENT_ROOT"].'/templates/page.php';
