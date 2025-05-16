<?php
// Sprachdateien aus dem Plugin-Ordner laden
$pm = new plugin_manager(); 
$plugin_language = $pm->plugin_language("partners", $plugin_path);

$filepath = $plugin_path . "images/";
$tpl = new Template();

$action = $_GET['action'] ?? '';

if ($action === "show") {
    $partnerID = $_GET['partnerID'] ?? null;

    if ($partnerID !== null) {

        // Überschriften-Daten
        $plugin_data = [
            'title'    => $plugin_language['partners'],
            'subtitle' => 'Partners'
        ];

        echo $tpl->loadTemplate("partners", "head", $plugin_data, $plugin_path);

        // Partnerdaten abrufen
        $get = safe_query("SELECT * FROM plugins_partners WHERE partnerID='" . intval($partnerID) . "' ORDER BY `sort` LIMIT 1");
        $db = mysqli_fetch_array($get);

        $partnerID = $db['partnerID'];
        $alt = htmlspecialchars($db['name']);
        $title = htmlspecialchars($db['name']);

        // Bild prüfen und setzen
        if (!empty($db['banner'])) {
            $pic = '<img class="img-fluid" src="../' . $filepath . $db['banner'] . '" alt="">';
        } else {
            $pic = '<img class="img-thumbnail" style="width: 100%; max-width: 150px" src="../' . $filepath . 'no-image.jpg" alt="">';
        }

        $name = $db['name'];

        // Link erzeugen
        if (!empty($db['url'])) {
            $url = htmlspecialchars($db['url']);
            $href = str_starts_with($url, "https://") ? $url : "http://" . $url;

            $link = '<a class="url-link" href="' . $href . '" onclick="setTimeout(function(){window.location.href=\'../includes/modules/out.php?partnerID=' . $partnerID . '\', 1000})" target="_blank" rel="nofollow"><i class="bi bi-house" style="font-size: 2rem;"></i></a>';
        } else {
            $link = $_language->module['n_a'];
        }

        // Touchscreen-Script
        $script = '<script>
        window.addEventListener("load", function(){
            var box = document.getElementById("box_' . $partnerID . '");
            if(box){
                box.addEventListener("touchstart", function(e){
                    setTimeout(function(){
                        window.location.href="../includes/modules/out.php?partnerID=' . $partnerID . '";
                    }, 200);
                    e.preventDefault();
                }, false);
                box.addEventListener("touchmove", function(e){
                    e.preventDefault();
                }, false);
                box.addEventListener("touchend", function(e){
                    window.open("' . $db['url'] . '", "_blank");
                    e.preventDefault();
                }, false);
            }
        }, false);
        </script>';

        $info = $db['info'];

        // Soziale Medien Links vorbereiten
        $facebook = !empty($db['facebook']) 
            ? '<a class="facebook" href="' . $db['facebook'] . '" target="_blank"><i class="bi bi-facebook" style="font-size: 2rem;"></i></a>'
            : '';

        $twitter = !empty($db['twitter']) 
            ? '<a class="twitter" href="' . $db['twitter'] . '" target="_blank"><i class="bi bi-twitter-x" style="font-size: 2rem;"></i></a>'
            : '';

        $data_array = [
            'partnerID' => $partnerID,
            'link'      => $link,
            'script'    => $script,
            'title'     => $title,
            'pic'       => $pic,
            'info'      => $info,
            'facebook'  => $facebook,
            'twitter'   => $twitter
        ];

        echo $tpl->loadTemplate("partners", "content", $data_array, 'plugin');
    }
} else {
    // Startseite bzw. Partnerliste anzeigen
    if ($action === "") {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

        // Header-Daten
        $plugin_data = [
            'title'    => $plugin_language['partners'],
            'subtitle' => 'Partners'
        ];

        echo $tpl->loadTemplate("partners", "head", $plugin_data, 'plugin');

        // Anzahl aller angezeigten Partner ermitteln
        $alle = safe_query("SELECT partnerID FROM plugins_partners WHERE displayed = '1'");
        $gesamt = mysqli_num_rows($alle);

        // Einstellungen laden (max. Partner pro Seite)
        $settings = safe_query("SELECT * FROM plugins_partners_settings");
        $dn = mysqli_fetch_array($settings);
        $max = !empty($dn['partners']) ? (int)$dn['partners'] : 1;

        // Pagination berechnen
        $pages = ceil($gesamt / $max);
        $page = max(1, min($page, $pages));
        $start = ($page - 1) * $max;

        // Partner-Daten abfragen
        $ergebnis = safe_query("SELECT * FROM plugins_partners WHERE displayed = '1' ORDER BY `sort` LIMIT $start, $max");

        if (mysqli_num_rows($ergebnis) > 0) {
            while ($db = mysqli_fetch_array($ergebnis)) {
                $partnerID = $db['partnerID'];
                $title = htmlspecialchars($db['name']);
                $info = $db['info'];

                // Bild prüfen
                $pic = !empty($db['banner'])
                    ? '<img class="img-fluid" src="../' . $filepath . $db['banner'] . '" alt="">'
                    : '<img class="img-thumbnail" style="width: 100%; max-width: 150px" src="../' . $filepath . 'no-image.jpg" alt="">';

                // Link erzeugen
                if (!empty($db['url'])) {
                    $url = htmlspecialchars($db['url']);
                    $href = str_starts_with($url, "https://") ? $url : "http://" . $url;

                    $link = '<a class="url-link" href="' . $href . '" onclick="setTimeout(function(){window.location.href=\'../includes/modules/out.php?partnerID=' . $partnerID . '\'}, 1000)" target="_blank" rel="nofollow"><i class="bi bi-house" style="font-size: 2rem;"></i></a>';
                } else {
                    $link = $_language->module['n_a'];
                }

                // Touchscreen-Script
                $script = '<script>
                window.addEventListener("load", function(){
                    var box = document.getElementById("box_' . $partnerID . '");
                    if(box){
                        box.addEventListener("touchstart", function(e){
                            setTimeout(function(){
                                window.location.href="../includes/modules/out.php?partnerID=' . $partnerID . '";
                            }, 200);
                            e.preventDefault();
                        }, false);
                        box.addEventListener("touchmove", function(e){
                            e.preventDefault();
                        }, false);
                        box.addEventListener("touchend", function(e){
                            window.open("' . $db['url'] . '", "_blank");
                            e.preventDefault();
                        }, false);
                    }
                }, false);
                </script>';

                // Soziale Medien Links
                $facebook = !empty($db['facebook'])
                    ? '<a class="facebook" href="' . $db['facebook'] . '" target="_blank"><i class="bi bi-facebook" style="font-size: 2rem;"></i></a>'
                    : '';

                $twitter = !empty($db['twitter'])
                    ? '<a class="twitter" href="' . $db['twitter'] . '" target="_blank"><i class="bi bi-twitter-x" style="font-size: 2rem;"></i></a>'
                    : '';

                $data_array = [
                    'partnerID' => $partnerID,
                    'link'      => $link,
                    'script'    => $script,
                    'title'     => $title,
                    'pic'       => $pic,
                    'info'      => $info,
                    'facebook'  => $facebook,
                    'twitter'   => $twitter
                ];

                echo $tpl->loadTemplate("partners", "content", $data_array, "plugin");
            }

            // Pagination anzeigen
            echo $tpl->renderPagination("index.php?site=partners", $page, $pages);
        } else {
            echo '<div class="alert alert-warning">' . $plugin_language['no_partners'] . '</div>';
        }
    }
}
?>
