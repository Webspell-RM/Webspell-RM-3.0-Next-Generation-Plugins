<script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.6.0/slick.js"></script>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Sprachdateien aus dem Plugin-Ordner laden
$pm = new plugin_manager(); 
$plugin_language = $pm->plugin_language("sc_partners", $plugin_path);

$tpl = new Template();

$filepath = $plugin_path . "images/";

// Partner-Daten aus der DB abfragen
$query = "SELECT * FROM plugins_partners WHERE displayed = '1' ORDER BY sort";
$ergebnis = safe_query($query);

if (mysqli_num_rows($ergebnis)) {

    // Slider Section starten
    echo '<section id="partners-bar" class="partner-logos slider">';

    while ($db = mysqli_fetch_array($ergebnis)) {

        $id = $db['id'];
        $alt = htmlspecialchars($db['name']);
        $title = htmlspecialchars($db['name']);

        // Bild HTML mit Tooltip
        $img_str = '<img src="' . $filepath . $db['banner'] . '" alt="' . $alt . '" data-toggle="tooltip" data-bs-html="true" title="' . $title . '">';

        // Link prüfen und erstellen
        if (!empty($db['url'])) {
            $url = htmlspecialchars($db['url']);
            $href = (stristr($url, "https://")) ? $url : "http://" . $url;

            $link = '<a href="./includes/plugins/partners/click.php?id=' . $id . '" target="_blank" rel="nofollow">' . $img_str . '</a>';
        } else {
            $link = $_language->module['n_a'];
        }

        // Daten für Template vorbereiten
        $data_array = [
            'id' => $id,
            'link'      => $link,
            'title'     => $title
        ];

        // Template ausgeben
        echo $tpl->loadTemplate("partners", "widget_slider", $data_array, "plugin");
    }

    // Section schließen
    echo '</section>';
}
?>
