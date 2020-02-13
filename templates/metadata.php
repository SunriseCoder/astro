<head>
    <link rel="stylesheet" type="text/css" href="styles/styles.css">
    <?
        if (isset($js_includes)) {
            foreach($js_includes as $js_include) {
                echo '<script type="text/javascript" src="'.$js_include.'"></script>';
            }
        }
    ?>
</head>
