<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

# Sprachdateien aus dem Plugin-Ordner laden
$pm = new plugin_manager(); 
$plugin_language = $pm->plugin_language("articles", $plugin_path);

use webspell\AccessControl;

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$config = mysqli_fetch_array(safe_query("SELECT selected_style FROM settings_headstyle_config WHERE id=1"));
$class = htmlspecialchars($config['selected_style']);

    // Header-Daten
    $data_array = [
        'class'    => $class,
        'title' => $plugin_language['title'],
        'subtitle' => 'About'
    ];
    
    echo $tpl->loadTemplate("articles", "head", $data_array, 'plugin');

if (isset($_GET[ 'action' ])) {
    $action = $_GET[ 'action' ];
} else {
    $action = '';
}

if ($action == "show" && is_numeric($_GET['articlecatID'])) {
    $articlecatID = (int)$_GET['articlecatID'];

    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    if ($page < 1) $page = 1;

    $limit = 6;
    $offset = ($page - 1) * $limit;

    $getcat = safe_query("SELECT * FROM plugins_articles_categories WHERE articlecatID='$articlecatID'");
    $ds = mysqli_fetch_array($getcat);
    $articlecatname = $ds['articlecatname'];

    // Artikelanzahl für Pagination
    $total_articles_query = safe_query("SELECT COUNT(*) as total FROM plugins_articles WHERE articlecatID='$articlecatID' AND displayed = '1'");
    $total_articles_result = mysqli_fetch_array($total_articles_query);
    $total_articles = (int)$total_articles_result['total'];
    $total_pages = ceil($total_articles / $limit);

    // Lade Artikel
    $ergebnis = safe_query("SELECT * FROM plugins_articles WHERE articlecatID='$articlecatID' AND displayed = '1' ORDER BY date DESC LIMIT $offset, $limit");

    $data_array = [
        'articlecatname'    => $articlecatname,
        'title' => $plugin_language['title'],
        'title_categories' => $plugin_language['title_categories'],
        'categories' => $plugin_language['categories'],
        'category' => $plugin_language['category'],
    ];

    echo $tpl->loadTemplate("articles", "details_head", $data_array, 'plugin');
    // Head-Bereich
    echo $tpl->loadTemplate("articles", "content_all_head", $data_array, 'plugin');

    if (mysqli_num_rows($ergebnis)) {
        while ($ds = mysqli_fetch_array($ergebnis)) {
            $question = $ds['question'];
            $answer = $ds['answer'];
            $timestamp = strtotime($ds['date']);
            $tag = date("d", $timestamp);
            $monat = date("n", $timestamp);
            $year = date("Y", $timestamp);

            $monate = array(
                1 => $plugin_language['jan'], 2 => $plugin_language['feb'],
                3 => $plugin_language['mar'], 4 => $plugin_language['apr'],
                5 => $plugin_language['may'], 6 => $plugin_language['jun'],
                7 => $plugin_language['jul'], 8 => $plugin_language['aug'],
                9 => $plugin_language['sep'], 10 => $plugin_language['oct'],
                11 => $plugin_language['nov'], 12 => $plugin_language['dec']
            );

            $monatname = $monate[$monat];
            $username = getusername($ds['poster']);

            $banner = $ds['banner'];
            $image = $banner ? "/includes/plugins/articles/images/article/".$banner : "/includes/plugins/articles/images/no-image.jpg";

            $question = $ds['question'];
                $answer = $ds['answer'];
                // Übersetzung
                $translate = new multiLanguage(detectCurrentLanguage());
                $translate->detectLanguages($ds['question']);
                $question = $translate->getTextByLanguage($ds['question']);
                $translate->detectLanguages($ds['answer']);
                $answer = $translate->getTextByLanguage($ds['answer']);

                // Optional kürzen
                $maxblogchars = 15; // oder dein gewünschtes Limit
                $short_question = $ds['question'];
                if (mb_strlen($short_question) > $maxblogchars) {
                    $short_question = mb_substr($short_question, 0, $maxblogchars) . '...';
                }

            $data_array = [
                'articlecatname'    => $articlecatname,
                'question'       => $short_question,
                'answer' => $answer,
                'username' => $username,
                'image' => $image,
                'tag'            => $tag,
                'monat'          => $monatname,
                'year'          => $year,
                'lang_rating' => $plugin_language['rating'],
                'lang_votes' => $plugin_language['votes'],
                'link' => $plugin_language['link'],
                'info' => $plugin_language['info'],
                'stand' => $plugin_language['stand'],
                'by' => $plugin_language['by'],
                'on' => $plugin_language['on'],
            ];

            echo $tpl->loadTemplate("articles", "details", $data_array, 'plugin');
        }
        // Footer
        echo $tpl->loadTemplate("articles", "content_all_foot", $data_array, 'plugin');

        // Pagination
        if ($total_pages > 1) {
            echo '<nav aria-label="Page navigation"><ul class="pagination justify-content-center mt-4">';
            for ($i = 1; $i <= $total_pages; $i++) {
                $active = ($i == $page) ? ' active' : '';
                echo '<li class="page-item' . $active . '">
                    <a class="page-link" href="index.php?site=articles&action=show&articlecatID=' . $articlecatID . '&page=' . $i . '">' . $i . '</a>
                </li>';
            }
            echo '</ul></nav>';
        }

    } else {
        echo $plugin_language['no_articles'] . '<br><br>[ <a href="index.php?site=articles" class="alert-article">' . $plugin_language['go_back'] . '</a> ]';
    }
}





$plugin_name = 'articles'; // Plugin-Name für die globale Tabelle

// Rating speichern
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['submit_rating'], $_POST['rating'], $_POST['itemID'])
) {
    $plugin = 'articles'; // Name des Plugins, hier 'articles'
    $rating = (int)$_POST['rating'];
    $itemID = (int)$_POST['itemID'];

    if (!$userID) {
        die('Du musst eingeloggt sein, um zu bewerten.');
    }

    if ($rating < 0 || $rating > 10) {
        die('Bitte gib eine Bewertung zwischen 0 und 10 ab.');
    }

    // Prüfen, ob User schon bewertet hat
    $check = safe_query("SELECT * FROM ratings WHERE plugin = '$plugin' AND itemID = $itemID AND userID = $userID");
    if (mysqli_num_rows($check) === 0) {
        // Insert neue Bewertung
        safe_query("INSERT INTO ratings (plugin, itemID, userID, rating, date) VALUES ('$plugin', $itemID, $userID, $rating, NOW())");
    } else {
        // Optional: Bewertung updaten
        safe_query("UPDATE ratings SET rating = $rating, date = NOW() WHERE plugin = '$plugin' AND itemID = $itemID AND userID = $userID");
    }

    // Durchschnitt neu berechnen und speichern (optional, falls du eine Summe brauchst)
    $res = safe_query("SELECT AVG(rating) AS avg_rating FROM ratings WHERE plugin = '$plugin' AND itemID = $itemID");
    if ($row = mysqli_fetch_assoc($res)) {
        $avg_rating = round($row['avg_rating'], 1);  // Beispiel 1 Dezimalstelle
        // Falls du eine Tabelle hast, wo der Durchschnitt gespeichert wird, z.B. articles
        safe_query("UPDATE plugins_articles SET rating = $avg_rating WHERE articleID = $itemID");
    }

    header("Location: index.php?site=articles&action=watch&articleID=$itemID");
    exit();
}


// Kommentar speichern
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (isset($_POST['submit_comment'])) {
    if (
        $loggedin &&
        !empty($_POST['comment']) &&
        is_numeric($_POST['articleID']) &&
        isset($_POST['csrf_token']) &&
        hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        $comment = htmlspecialchars($_POST['comment']);
        $itemID = (int)$_POST['articleID'];

        safe_query("INSERT INTO comments (plugin, itemID, userID, comment, date, parentID) VALUES ('$plugin_name', $itemID, $userID, '$comment', NOW(), 0)");

        header("Location: index.php?site=articles&action=watch&articleID=$itemID");
        exit;
    } else {
        die("Ungültiger CSRF-Token oder fehlende Eingaben.");
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'deletecomment' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    session_start();  // falls noch nicht gestartet

    $commentID = (int)$_GET['id'];
    $referer = isset($_GET['ref']) ? urldecode($_GET['ref']) : 'index.php?site=articles';

    if (!isset($_GET['token']) || !hash_equals($_SESSION['csrf_token'], $_GET['token'])) {
        die('Ungültiger CSRF-Token.');
    }

    // Kommentar aus deiner comments Tabelle löschen
    $res = safe_query("DELETE FROM comments WHERE commentID = $commentID");

    if ($res) {
        header("Location: $referer");
        exit();
    } else {
        die('Fehler beim Löschen des Kommentars.');
    }
}





// Bewertung speichern (falls in deinem Originalcode noch da, sonst ergänzen)

if ($action == "watch" && is_numeric($_GET['articleID'])) {
    $articleID = (int)$_GET['articleID'];
    $pluginName = 'articles';

    $settings = safe_query("SELECT * FROM plugins_articles WHERE articleID = $articleID");
    if (mysqli_num_rows($settings)) {
        $ds = mysqli_fetch_array($settings);

        $category = safe_query("SELECT * FROM plugins_articles_categories WHERE articlecatID = '{$ds['articlecatID']}'");
        $cat = mysqli_fetch_array($category);

        $data_array = [
            'articlecatname' => $cat['articlecatname'],
            'question' => $ds['question'],
            'articlecatID' => $ds['articlecatID'],
            'title_categories' => $plugin_language['title_categories'],
            'categories' => $plugin_language['categories'],
            'category' => $plugin_language['category'],
        ];

        echo $tpl->loadTemplate("articles", "content_details_head", $data_array, 'plugin');

        safe_query("UPDATE plugins_articles SET views = views + 1 WHERE articleID = $articleID");

        // Bewertung laden
        $hasRated = false;
        if ($loggedin) {
            $check = safe_query("SELECT * FROM ratings WHERE plugin = '$pluginName' AND itemID = $articleID AND userID = $userID");
            $hasRated = mysqli_num_rows($check) > 0;
        }

        if ($loggedin) {
            if ($hasRated) {
                $rateform = '<p><em>' . $plugin_language['you_have_already_rated'] . '</em></p>';
            } else {
                $rateform = '<form method="post" action="" class="row g-2 align-items-center">
                    <div class="col-auto">
                        <label for="rating" class="form-label">' . $plugin_language['rate_now'] . '</label>
                    </div>
                    <div class="col-auto">
                        <select name="rating" class="form-select">';
                for ($i = 0; $i <= 10; $i++) {
                    $rateform .= '<option value="' . $i . '">' . $i . '</option>';
                }
                $rateform .= '</select>
                        <input type="hidden" name="plugin" value="' . $pluginName . '">
                        <input type="hidden" name="itemID" value="' . $articleID . '">
                        <input type="hidden" name="submit_rating" value="1">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">' . $plugin_language['rate'] . '</button>
                    </div>
                </form>';
            }
        } else {
            $rateform = '<p><em>' . $plugin_language['rate_have_to_reg_login'] . '</em></p>';
        }

        // Bewertung anzeigen
        // Rating aus der globalen Tabelle holen
        $r = safe_query("SELECT AVG(rating) AS avg_rating, COUNT(ratingID) AS votes FROM ratings WHERE plugin = 'articles' AND itemID = $articleID");
        $r = mysqli_fetch_assoc($r);

        // Absicherung gegen NULL-Werte bei AVG und COUNT
        $avg_rating = round($r['avg_rating'] ?? 0);
        $votes = (int)($r['votes'] ?? 0);

        // Rating-Bilder erzeugen (voll / leer)
        $ratingpic = str_repeat('<img src="/includes/plugins/articles/images/rating_1.png" width="21" height="21" alt="">', $avg_rating)
                   . str_repeat('<img src="/includes/plugins/articles/images/rating_0.png" width="21" height="21" alt="">', 10 - $avg_rating);

        $image = $ds['banner'] ? "includes/plugins/articles/images/article/{$ds['banner']}" : "includes/plugins/articles/images/no-image.jpg";
        $poster = '<a href="index.php?site=profile&amp;id=' . $ds['poster'] . '"><strong>' . getusername($ds['poster']) . '</strong></a>';
        $link = $ds['url'] ? '<a href="' . $ds['url'] . '" target="_blank">' . $ds['url'] . '</a>' : $plugin_language['no_link'];

        $data_array = [
            'question' => $ds['question'],
            'answer' => $ds['answer'],
            'poster' => $poster,
            'date' => date('d.m.Y H:i', strtotime($ds['date'])),
            'ratingpic' => $ratingpic,
            'votes' => $votes,
            'rateform' => $rateform,
            'views' => $ds['views'],
            'image' => $image,
            'link' => $link,
            'lang_rating' => $plugin_language['rating'],
            'lang_votes' => $plugin_language['votes'],
            'lang_link' => $plugin_language['link'],
            'info' => $plugin_language['info'],
            'stand' => $plugin_language['stand'],
            'lang_views' => $plugin_language['views'],
        ];

        echo $tpl->loadTemplate("articles", "content_details", $data_array, 'plugin');

        // Kommentare anzeigen
        $comments = safe_query("
            SELECT c.*, u.username 
            FROM comments c 
            JOIN users u ON c.userID = u.userID 
            WHERE c.plugin = '$pluginName' AND c.itemID = $articleID 
            ORDER BY c.date DESC
        ");
        echo '<div class="mt-5"><h5>' . $plugin_language['comments'] . '</h5><ul class="list-group">';
        while ($row = mysqli_fetch_array($comments)) {
            $deleteLink = '';

            // Nur anzeigen, wenn aktueller User = Autor oder Admin
            if ($userID == $row['userID']) {
            $canDelete = ($userID == $row['userID'] || has_role($userID, 'Admin'));
            
                if ($canDelete) {    
                    $deleteLink = '<a href="index.php?site=articles&action=deletecomment&id=' . $row['commentID'] . '&ref=' . urlencode($_SERVER['REQUEST_URI']) . '&token=' . $_SESSION['csrf_token'] . '" class="btn btn-sm btn-danger ms-2" onclick="return confirm(\'Kommentar wirklich löschen?\')">' . $plugin_language['delete'] . '</a>';
                } else {
                    $deleteLink = '';
                }
            }    
            echo '<li class="list-group-item">
                    <strong>' . htmlspecialchars($row['username']) . '</strong><br>
                    ' . nl2br(htmlspecialchars($row['comment'])) . '
                    <div class="text-muted small">' . date('d.m.Y H:i', strtotime($row['date'])) . '</div>
                    <div>' . $deleteLink . '</div>
                  </li>';
        }
        echo '</ul></div>';


        // Kommentarformular
        if ($loggedin) {
            echo '<form method="POST" action="index.php?site=articles&action=watch&articleID='. $articleID .'" class="mt-4">
                <textarea class="form-control" name="comment" rows="4" required></textarea>
                <input type="hidden" name="articleID" value="' .$articleID .'">
                <input type="hidden" name="csrf_token" value="' .$_SESSION['csrf_token'] .'">
                <button type="submit" name="submit_comment" class="btn btn-success">Kommentar abschicken</button>
            </form>';
        } else {
            echo '<p><em>' . $plugin_language['must_login_comment'] . '</em></p>';
        }
    }
























} elseif ($action == "") {
    
   

    function getArticleCategoryName($catID) {
        $cat = mysqli_fetch_assoc(safe_query("SELECT articlecatname, description FROM plugins_articles_categories WHERE articlecatID = '$catID'"));
        return '<a data-toggle="tooltip" title="'.$cat['description'].'" href="index.php?site=articles&action=show&articlecatID=' . $catID . '"><strong style="font-size: 16px">' . $cat['articlecatname'] . '</strong></a>';
    }

    // Kategorien anzeigen
    $cats = safe_query("SELECT * FROM plugins_articles_categories ORDER BY articlecatname");

    if (mysqli_num_rows($cats)) {

        $data_array = [
                'title_categories'       => $plugin_language['title_categories'],
            ];

        echo $tpl->loadTemplate("articles", "category", $data_array, 'plugin');

        // Head-Bereich
        echo $tpl->loadTemplate("articles", "content_all_head", $data_array, 'plugin');

        // Pagination vorbereiten
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;
        $limit = 6;
        $offset = ($page - 1) * $limit;

        $total_articles = mysqli_num_rows(safe_query("SELECT articleID FROM plugins_articles"));
        $total_pages = ceil($total_articles / $limit);

        // Artikel laden
        $ergebnis = safe_query("SELECT * FROM `plugins_articles` ORDER BY `date` DESC LIMIT $offset, $limit");

        if (mysqli_num_rows($ergebnis)) {
            while ($db = mysqli_fetch_array($ergebnis)) {
                $articleID = $db['articleID'];
                $timestamp = strtotime($db['date']);
                $tag = date("d", $timestamp);
                $monat = date("n", $timestamp);
                $year = date("Y", $timestamp);

                $monate = array(
                    1 => $plugin_language['jan'], 2 => $plugin_language['feb'],
                    3 => $plugin_language['mar'], 4 => $plugin_language['apr'],
                    5 => $plugin_language['may'], 6 => $plugin_language['jun'],
                    7 => $plugin_language['jul'], 8 => $plugin_language['aug'],
                    9 => $plugin_language['sep'], 10 => $plugin_language['oct'],
                    11 => $plugin_language['nov'], 12 => $plugin_language['dec']
                );

                $monatname = $monate[$monat];
                $banner = $db['banner'];
                $image = $banner ? "/includes/plugins/articles/images/article/".$banner : "/includes/plugins/articles/images/no-image.jpg";

                $username = getusername($db['poster']);
                $question = $db['question'];
                $answer = $db['answer'];
                // Übersetzung
                $translate = new multiLanguage(detectCurrentLanguage());
                $translate->detectLanguages($db['question']);
                $question = $translate->getTextByLanguage($db['question']);
                $translate->detectLanguages($db['answer']);
                $answer = $translate->getTextByLanguage($db['answer']);

                // Optional kürzen
                $maxblogchars = 15; // oder dein gewünschtes Limit
                $short_question = $db['question'];
                if (mb_strlen($short_question) > $maxblogchars) {
                    $short_question = mb_substr($short_question, 0, $maxblogchars) . '...';
                }

                

                $article_catname = getArticleCategoryName($db['articlecatID']);

                $data_array = [
                    'articlecatname' => $article_catname,
                    'question'       => $short_question,
                    'answer'         => $answer,
                    'tag'            => $tag,
                    'monat'          => $monatname,
                    'year'          => $year,
                    'image'          => $image,
                    'username'       => $username,
                    'articleID'      => $articleID,
                    'by'             => $plugin_language['by'],
                ];

                echo $tpl->loadTemplate("articles", "content_all", $data_array, 'plugin');
            }
        }

        // Footer
        echo $tpl->loadTemplate("articles", "content_all_foot", $data_array, 'plugin');

        // Pagination Links
        if ($total_pages > 1) {
            echo '<nav><ul class="pagination justify-content-center">';
            for ($i = 1; $i <= $total_pages; $i++) {
                $active = ($i == $page) ? 'active' : '';
                echo '<li class="page-item '.$active.'"><a class="page-link" href="index.php?site=articles&page='.$i.'">'.$i.'</a></li>';
            }
            echo '</ul></nav>';
        }

    } else {
        echo $plugin_language['no_categories'];
    }

}
