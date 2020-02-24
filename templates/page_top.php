<div class="header">
    <script type="text/javascript">
        function setLanguage(element) {
            var value = element.value;
            document.cookie = "language=" + value + "; expires=Fri, 31 Dec 9999 23:59:59 GMT";
            location.reload(true);
        }
    </script>
    <?php
        include $_SERVER["DOCUMENT_ROOT"].'/utils/i18n.php';

        // Choose Language Dropdown list
        echo '<select id="language" onchange="setLanguage(this);">';
        $languages = Tr::getLanguages();
        foreach ($languages as $language) {
            echo '<option value="'.$language->code.'"';
            if (isset($_COOKIE['language']) && $_COOKIE['language'] == $language->code) {
                Tr::setCurrentLanguage($language->code);
                echo ' selected="selected"';
            }
            echo '>'.$language->nameNative.'</option>';
        }
        echo '</select><br />';

        print $page_title;
    ?><br />
    <br />
    <?php include $_SERVER["DOCUMENT_ROOT"].'/templates/login.php'; ?>
</div>
