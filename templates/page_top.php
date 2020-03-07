<div class="header">
    <script type="text/javascript">
        function setLanguage(element) {
            var value = element.value;
            document.cookie = "language=" + value + "; expires=Fri, 31 Dec 9999 23:59:59 GMT";
            location.reload(true);
        }
    </script>
    <table id="page-top-table">
        <tr>
            <td id="page-top-logo-cell">
                <img id="logo" alt="Logo" src="/images/ca-logo.png">
            </td>
            <td id="page-top-content-cell">
                <?php
                    if (!class_exists('Tr')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/i18n.php'; }

                    // Choose Language Dropdown list
                    echo '<div id="settings-div">';
                    echo '<div id="choose-language-div">';
                    echo '<select id="language" onchange="setLanguage(this);">';
                    $languages = Tr::getLanguages();
                    foreach ($languages as $language) {
                        echo '<option value="'.$language->code.'"';
                        if (isset($_COOKIE['language']) && $_COOKIE['language'] == $language->code) {
                            echo ' selected="selected"';
                        }
                        echo '>'.$language->nameNative.'</option>';
                    }
                    echo '</select></div>';

                    include $_SERVER["DOCUMENT_ROOT"].'/templates/login.php';
                    echo '</div>';

                    echo '<p class="page-title">'.$page_title.'</p>';
                ?>
            </td>
        </tr>
    </table>
</div>
