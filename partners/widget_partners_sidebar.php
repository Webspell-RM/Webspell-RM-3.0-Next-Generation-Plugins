<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Sprachdateien aus dem Plugin-Ordner laden
$pm = new plugin_manager(); 
$plugin_language = $pm->plugin_language("partners", $plugin_path);

$tpl = new Template();

$config = mysqli_fetch_array(safe_query("SELECT selected_style FROM settings_headstyle_config WHERE id=1"));
$class = htmlspecialchars($config['selected_style']);

// Header-Daten
$data_array = [
    'class'    => $class,
    'title'    => $plugin_language['title'],
    'subtitle' => 'Partners'
];

// Head-Templates ausgeben
echo $tpl->loadTemplate("partners", "head", $data_array, "plugin");
echo $tpl->loadTemplate("partners", "widget_head_head", $data_array, "plugin");


// Basis-Pfad für Bannerbilder (relativ zum Webroot)
$filepath = '/includes/plugins/partners/images/';

// SQL-Abfrage: alle sichtbaren Partner sortiert ausgeben
$query = "SELECT * FROM plugins_partners WHERE displayed = 1 ORDER BY sort";
$result = safe_query($query);

if ($result && mysqli_num_rows($result) > 0) {
    while ($partner = mysqli_fetch_assoc($result)) {

        $id = (int)$partner['id'];
        $name = htmlspecialchars($partner['name']);
        $urlRaw = trim($partner['url']);
        $banner = $partner['banner'];

        // Bildpfad absolut
        $imgPath = $_SERVER['DOCUMENT_ROOT'] . $filepath . $banner;

        // Prüfen, ob Banner-Bild existiert, ansonsten Fallback auf Text (Name)
        if (!empty($banner) && file_exists($imgPath)) {
            $imgTag = '<img class="img-fluid" style="height:35px" src="' . $filepath . htmlspecialchars($banner) . '" alt="' . $name . '" data-bs-toggle="tooltip" data-bs-html="true" title="' . $name . '">';
        } else {
            $imgTag = $name;
        }

        // URL validieren / auf https prüfen und ggf. http ergänzen
        if (!empty($urlRaw)) {
            $urlSafe = htmlspecialchars($urlRaw);
            if (!preg_match('#^https?://#i', $urlSafe)) {
                $urlSafe = 'http://' . $urlSafe;
            }
        } else {
            $urlSafe = '';
        }

        // Button-Klasse (optional, hier Beispielwert)
        $btnClass = 'partner-link btn btn-primary btn-sm';

        // Link generieren: Wenn URL vorhanden, dann Link auf click.php mit id und Target _blank
        if (!empty($urlSafe)) {
            // Link mit Klick-Tracking über click.php
            $link = '<a href="./includes/plugins/partners/click.php?id=' . $id . '" target="_blank" rel="nofollow">' . $imgTag . '</a>';
        } else {
            $link = '<span class="text-muted">Kein gültiger Link vorhanden</span>';
        }

        // Touch-Event-Script (modifiziert, um doppelte event.preventDefault() zu vermeiden)
        $script = <<<SCRIPT
<script>
window.addEventListener("load", function() {
    var box = document.getElementById("box_$id");
    if(box) {
        var touchTimeout;
        box.addEventListener("touchstart", function(e) {
            touchTimeout = setTimeout(function() {
                window.location.href = "out.php?id=$id";
            }, 200);
            e.preventDefault();
        }, false);

        box.addEventListener("touchmove", function(e) {
            clearTimeout(touchTimeout);
        }, false);

        box.addEventListener("touchend", function(e) {
            clearTimeout(touchTimeout);
            window.open("$urlSafe", "_blank");
            e.preventDefault();
        }, false);
    }
});
</script>
SCRIPT;

        // Daten-Array für Template
        $data_array = [
            'id'     => $id,
            'link'   => $link,
            'script' => $script,
            'title'  => $name
        ];

        // Template ausgeben (dein Template-System)
        echo $tpl->loadTemplate("partners", "widget_content", $data_array, 'plugin');
    }

    // Footer-Template einmal ausgeben (z.B. Schließung der Box etc.)
    echo $tpl->loadTemplate("partners", "widget_foot_foot", [], 'plugin');

} else {
    // Kein Partner gefunden - evtl. Hinweis ausgeben
    echo '<p>Keine Partner gefunden.</p>';
}
