<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />

    <title>
        <?php
            if (isset($browser_title)) {
                echo $browser_title;
            }
        ?>
    </title>

    <link rel="stylesheet" type="text/css" href="/css/styles.css">
    <?
        if (isset($css_includes)) {
            foreach ($css_includes as $css_include) {
                echo '<link rel="stylesheet" type="text/css" href="'.$css_include.'">';
            }
        }
        if (isset($js_includes)) {
            foreach($js_includes as $js_include) {
                echo '<script type="text/javascript" src="'.$js_include.'"></script>';
            }
        }
    ?>
</head>
