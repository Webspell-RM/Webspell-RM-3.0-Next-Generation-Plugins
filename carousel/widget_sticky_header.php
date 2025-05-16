<?php
$pm = new plugin_manager(); 
$plugin_language = $pm->plugin_language("carousel", $plugin_path);
$tpl = new Template();

GLOBAL $theme_name;

$filepath = $plugin_path . "images/";

$ds = mysqli_fetch_array(safe_query("SELECT * FROM plugins_carousel_settings"));

$ergebnis = safe_query("SELECT * FROM plugins_carousel_sticky");
if (mysqli_num_rows($ergebnis)) {
    while ($db = mysqli_fetch_array($ergebnis)) {
        $sticky_pic = $filepath . $db['sticky_pic'];
        $sticky_height = $ds['sticky_height'];
        $description = $db['description'];
        $link_url = $db['link'];
        $title = $db['title'];

        if ($link_url != '') {
            if (stristr($link_url, "https://")) {
                $link = '<a data-aos="fade-up" data-aos-delay="200" href="' . htmlspecialchars($link_url) . '" class="btn-get-started scrollto"><i class="bi bi-chevron-double-down"></i></a>';
            } else {
                $link = '<a data-aos="fade-up" data-aos-delay="200" href="' . htmlspecialchars($link_url) . '" class="btn-get-started scrollto">' . $plugin_language['read_more'] . '</a>';
            }
        } else {
            $link = '';
        }

        $translate = new multiLanguage(detectCurrentLanguage());
        $translate->detectLanguages($title);
        $title = $translate->getTextByLanguage($title);
        $translate->detectLanguages($description);
        $description = $translate->getTextByLanguage($description);

        $replaces = [
            'sticky_pic'     => $sticky_pic,
            'sticky_height'  => $sticky_height,
            'title'          => $title,
            'link'           => $link,
            'description'    => $description,
            'theme_name'     => $theme_name
        ];

        echo $tpl->loadTemplate("sticky_header", "content", $replaces, 'plugin');
    }
} else {
    echo '<div class="alert alert-danger" role="alert">' . $plugin_language['no_header'] . '</div>';
}
