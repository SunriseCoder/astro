<div class="header">
    <script type="text/javascript">
        function setLanguage(value) {
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
                    echo '<div class="dropdown">';
                    echo '<button class="dropdownbtn">'.Tr::getCurrentLanguage()->nameNative.' <img src="/images/arrow-down.png" /></button>';
                    echo '<div id="dropdown-language-form-content" class="dropdown-content">';

                    //echo '<select id="language" onchange="setLanguage(this);">';
                    $languages = Tr::getLanguages();
                    foreach ($languages as $language) {
                        echo '<a class="drop-down" onclick="setLanguage(\''.$language->code.'\');">'.$language->nameNative.'</a>';
                    }
                    echo '</div></div></div>';

                    include $_SERVER["DOCUMENT_ROOT"].'/templates/login.php';
                    echo '</div>';
                ?>
            </td>
        </tr>
    </table>
</div>
