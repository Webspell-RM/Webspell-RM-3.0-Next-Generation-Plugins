<?php
// Sprachdateien aus dem Plugin-Ordner laden
$pm = new plugin_manager(); 
$plugin_language = $pm->plugin_language("partners", $plugin_path);

$tpl = new Template();

// Überschriften-Daten vorbereiten
$data_array = [
    'title'    => $plugin_language['title'],
    'subtitle' => 'Partners'
];

// Head-Templates ausgeben
echo $tpl->loadTemplate("partners", "head", $data_array, "plugin");
echo $tpl->loadTemplate("partners", "widget_head_head", $data_array, "plugin");

// Abfrage aller angezeigten Partner
$query = "SELECT * FROM plugins_partners WHERE displayed = '1' ORDER BY sort";
$ergebnis = safe_query($query);

if (mysqli_num_rows($ergebnis)) {
    // Durch alle Partner iterieren
    while ($db = mysqli_fetch_array($ergebnis)) {
        $filepath = $plugin_path . "images/";
        $partnerID = $db['partnerID'];
        $banner = $db['banner'];
        $alt = htmlspecialchars($db['name']);
        $title = htmlspecialchars($db['name']);
        $name = $db['name'];

        // Pfad zum Bild
        $img = '/includes/plugins/partners/images/' . $banner;

        // Bild HTML mit Tooltip
        $img_str = '<img class="img-fluid" style="height: 35px" src="' . $filepath . $banner . '" alt="' . $alt . '" data-toggle="tooltip" data-bs-html="true" title="' . $title . '">';

        // Bild prüfen - existiert Datei?
        if (is_file($img) && file_exists($img)) {
            $text = $img_str;
        } else {
            $text = $name;
        }

        // Link generieren
        if (!empty($db['url'])) {
            $url = htmlspecialchars($db['url']);
            $href = (stristr($url, "https://")) ? $url : "http://" . $url;

            $link = '<a href="' . $href . '" onclick="setTimeout(function(){window.location.href=\'../includes/modules/out.php?partnerID=' . $partnerID . '\', 1000})" target="_blank" rel="nofollow">' . $img_str . '</a>';
        } else {
            $link = $_language->module['n_a'];
        }

        // Touch-Script für mobiles Verhalten
        $script = '<script> 
            window.addEventListener("load", function() {
                var box' . $partnerID . ' = document.getElementById("box_' . $partnerID . '");
                box' . $partnerID . '.addEventListener("touchstart", function(e) {
                    setTimeout(function() { window.location.href = "out.php?partnerID=' . $partnerID . '"; }, 200);
                    e.preventDefault();
                }, false);
                box' . $partnerID . '.addEventListener("touchmove", function(e) {
                    e.preventDefault();
                }, false);
                box' . $partnerID . '.addEventListener("touchend", function(e) {
                    window.open("' . $db['url'] . '", "_blank");
                    e.preventDefault();
                }, false);
            }, false);
        </script>';

        // Daten für Template vorbereiten
        $data_array = [
            'partnerID' => $partnerID,
            'link'      => $link,
            'script'    => $script,
            'title'     => $title
        ];

        // Template ausgeben
        echo $tpl->loadTemplate("partners", "widget_content", $data_array, 'plugin');
    }

    // Footer-Template ausgeben
    echo $tpl->loadTemplate("partners", "widget_foot_foot", $data_array, 'plugin');
}
?>
