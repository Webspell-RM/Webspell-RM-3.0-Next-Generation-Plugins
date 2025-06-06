<?php

/**
 *¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯*
 *                  Webspell-RM      /                        /   /                                          *
 *                  -----------__---/__---__------__----__---/---/-----__---- _  _ -                         *
 *                   | /| /  /___) /   ) (_ `   /   ) /___) /   / __  /     /  /  /                          *
 *                  _|/_|/__(___ _(___/_(__)___/___/_(___ _/___/_____/_____/__/__/_                          *
 *                               Free Content / Management System                                            *
 *                                           /                                                               *
 *¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯*
 * @version         webspell-rm                                                                              *
 *                                                                                                           *
 * @copyright       2018-2023 by webspell-rm.de                                                              *
 * @support         For Support, Plugins, Templates and the Full Script visit webspell-rm.de                 *
 * @website         <https://www.webspell-rm.de>                                                             *
 * @forum           <https://www.webspell-rm.de/forum.html>                                                  *
 * @wiki            <https://www.webspell-rm.de/wiki.html>                                                   *
 *                                                                                                           *
 *¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯*
 * @license         Script runs under the GNU GENERAL PUBLIC LICENCE                                         *
 *                  It's NOT allowed to remove this copyright-tag                                            *
 *                  <http://www.fsf.org/licensing/licenses/gpl.html>                                         *
 *                                                                                                           *
 *¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯*
 * @author          Code based on WebSPELL Clanpackage (Michael Gruber - webspell.at)                        *
 * @copyright       2005-2011 by webspell.org / webspell.info                                                *
 *                                                                                                           *
 *¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯*
 */

# Sprachdateien aus dem Plugin-Ordner laden
$pm = new plugin_manager();
$_lang = $pm->plugin_language("news", $plugin_path);

// -- COMMENTS INFORMATION -- //
include_once("news_functions.php");


if (isset($_GET['action'])) {
    $action = $_GET['action'];
} else {
    $action = '';
}


if (isset($_POST['saveeditcomment'])) {

    if (!isfeedbackadmin($userID) && !isvideocommentposter($userID, $_POST['commentID'])) {
        die('No access');
    }

    $message = $_POST['message'];
    $author = $_POST['authorID'];
    $referer = urldecode($_POST['referer']);

    // check if any admin edited the post
    if (safe_query(
        "UPDATE
                `plugins_news_comments`
            SET
                newscomments='" . $message . "'
            WHERE
                commentID='" . (int)$_POST['commentID'] . "'"
    )) {
        header("Location: " . $referer);
    }
} elseif ($action == "editcomment") {

    # Sprachdateien aus dem Plugin-Ordner laden
    $pm = new plugin_manager();
    $plugin_language = $pm->plugin_language("comments", $plugin_path);

    $id = $_GET['id'];
    $referer = $_GET['ref'];

    if (isfeedbackadmin($userID) || isvideocommentposter($userID, $id)) {
        if (!empty($id)) {
            $dt = safe_query("SELECT * FROM plugins_news_comments WHERE commentID='" . (int)$id . "'");
            if (mysqli_num_rows($dt)) {
                $ds = mysqli_fetch_array($dt);
                $poster = '<a href="index.php?site=profile&amp;id=' . $ds['userID'] . '"><b>' .
                    getnickname($ds['userID']) . '</b></a>';
                $message = htmlspecialchars($ds['newscomments']);
                $message = preg_replace("#\n\[br\]\[br\]\[hr]\*\*(.+)#si", '', $message);
                $message = preg_replace("#\n\[br\]\[br\]\*\*(.+)#si", '', $message);

                $data_array = [
                    'message' => $message,
                    'authorID' => $ds['userID'],
                    'id' => $id,
                    'userID' => $userID,
                    'referer' => $referer,
                    'title_editcomment' => $plugin_language['title_editcomment'],
                    'edit_comment' => $plugin_language['edit_comment']
                ];

                #$template = $GLOBALS["_template"]->loadTemplate("comments", "edit", $data_array, $plugin_path);
                #echo $template;
                echo $tpl->loadTemplate("comments", "edit", $data_array, "plugin");
            } else {
                redirect($referer, $plugin_language['no_database_entry'], 2);
            }
        } else {
            redirect($referer, $plugin_language['no_commentid'], 2);
        }
    } else {
        redirect($referer, $plugin_language['access_denied'], 2);
    }
} elseif ($action == "") {

    if (isset($_GET['page'])) {
        $page = (int)$_GET['page'];
    } else {
        $page = 1;
    }

    $data_array = [
        'title' => $_lang['title'],
        'subtitle' => 'News',
    ];

    #$template = $GLOBALS["_template"]->loadTemplate("news", "head", $data_array, $plugin_path);
    #echo $template;
    echo $tpl->loadTemplate("news", "head", $data_array, "plugin");

    $alle = safe_query("SELECT newsID FROM plugins_news WHERE displayed = '1'");
    $gesamt = mysqli_num_rows($alle);
    $pages = 1;

    $settings = safe_query("SELECT * FROM plugins_news_settings");
    $dn = mysqli_fetch_array($settings);

    $max = $dn['news'];
    if (empty($max)) {
        $max = 10;
    }

    for ($n = $max; $n <= $gesamt; $n += $max) {
        if ($gesamt > $n) $pages++;
    }


    if ($pages > 1) $page_link = makepagelink("index.php?site=news", $page, $pages);
    else $page_link = '';

    if ($page == "1") {
        $ergebnis = safe_query("SELECT * FROM plugins_news WHERE displayed = '1' ORDER BY date DESC LIMIT 0,$max");
        $n = 1;
    } else {
        $start = $page * $max - $max;
        $ergebnis = safe_query("SELECT * FROM plugins_news WHERE displayed = '1' ORDER BY date DESC LIMIT $start,$max");
        $n = ($gesamt + 1) - $page * $max + $max;
    }

    $ds = safe_query("SELECT * FROM `plugins_news` ORDER BY `date`");
    $anzcats = mysqli_num_rows($ds);
    if ($anzcats) {

        #$data_array = array();
        #$template = $GLOBALS["_template"]->loadTemplate("news", "content_area_head", $data_array, $plugin_path);
        #echo $template;

        echo $tpl->loadTemplate("news", "content_area_head", $data_array, "plugin");

        $n = 1;
        if (mysqli_num_rows($ergebnis)) {

            while ($ds = mysqli_fetch_array($ergebnis)) { // ÄNDERN DER MONATSAUSGABE VON ZAHLEN IN BUCHSTABEN //
                /* $date = getformatdate($ds[ 'date']); */
                $monatsnamen = array(
                    1 => $_lang['jan'],
                    2 => $_lang['feb'],
                    3 => $_lang['mar'],
                    4 => $_lang['apr'],
                    5 => $_lang['may'],
                    6 => $_lang['jun'],
                    7 => $_lang['jul'],
                    8 => $_lang['aug'],
                    9 => $_lang['sep'],
                    10 => $_lang['oct'],
                    11 => $_lang['nov'],
                    12 => $_lang['dec']
                );

                // Datum in Timestamp umwandeln (falls String)
                $timestamp = is_numeric($ds['date']) ? (int)$ds['date'] : strtotime($ds['date']);

                $monatNum = (int)date('n', $timestamp);
                $monat = $monatsnamen[$monatNum];
                $tag = date('d', $timestamp);
                $year = date('Y', $timestamp);
                $time = date('H:i', $timestamp);

                // HTML-Ausgabe mit Tag, Monat, Jahr und Zeit
                $date = '<p class="news-block news-day">' . $tag . '</p>' .
                        '<p class="news-block news-month">' . $monat . '</p>' .
                        '<p class="news-block news-year">' . $year . '</p>' .
                        '<p class="news-block news-time">' . $time . '</p>';


                // ENDE ÄNDERN DER MONATSAUSGABE VON ZAHLEN IN BUCHSTABEN //
                $rubrikname = getnewsrubricname($ds['rubric']);
                $rubrikname_link = htmlspecialchars($rubrikname);
                $rubricpic_name = getnewsrubricpic($ds['rubric']);


                $rubricpic = $plugin_path . 'images/news-rubrics/' . $rubricpic_name;
                if (!file_exists($rubricpic) || $rubricpic_name == '') {
                    $rubricpic = '<img style="min-height: 106px;" src="' . $plugin_path . 'images/news-rubrics/no-image.jpg" alt="" class="news-rubric img-fluid card-img-top">';
                } else {
                    $rubricpic = '<img style="min-height: 106px;" src="' . $rubricpic . '" alt="" class="news-rubric img-fluid card-img-top">'; // KLASSE FÜR RUBRICPIC DAMIT CONTENT DARAUF ANGEZEIGT WERDEN KANN //
                    $rubricpic = $rubricpic;
                }

                if ($ds['comments']) {
                    if ($ds['newsID']) {
                        $anzcomments = getanznewscomments($ds['newsID'], 'ne', 're');
                        $replace = array('$anzcomments', '$url', '$lastposter', '$lastdate');
                        $vars = array(
                            $anzcomments,
                            'index.php?site=news&action=news_contents&amp;newsID=' . $ds['newsID'],
                            html_entity_decode(getlastnewscommentposter($ds['newsID'], 'ne')),
                            getformatdatetime(getlastnewscommentdate($ds['newsID'], 'ne'))
                        );

                        switch ($anzcomments) {
                            case 0:
                                $comments = str_replace($replace, $vars, $_lang['no_comment']);
                                break;
                            case 1:
                                $comments = str_replace($replace, $vars, $_lang['comment']);
                                break;
                            default:
                                $comments = str_replace($replace, $vars, $_lang['comments']);
                                break;
                        }
                    }
                } else {
                    $comments = $_lang['off_comments'];
                }

                #-----------------------------

                $recomments = '';

                if ($ds['comments']) {
                    if ($ds['newsID']) {
                        $anzrecomments = getanznewsrecomments($ds['newsID'], 're');
                        $replace = array('$anzcomments', '$url', '$lastposter');
                        $vars = array(
                            $anzrecomments,
                            'index.php?site=news&action=news_contents&amp;newsID=' . $ds['newsID'],
                            html_entity_decode(getlastnewsrecommentposter($ds['newsID'], 're')) #,
                            #getformatdatetime(getlastnewsrecommentdate($ds[ 'newsID' ], 're'))
                        );

                        switch ($anzrecomments) {
                            case 0:
                                $recomments = str_replace($replace, $vars, $_lang['no_re_comment']);
                                break;
                            case 1:
                                $recomments = str_replace($replace, $vars, $_lang['re_comment']);
                                break;
                            default:
                                $recomments = str_replace($replace, $vars, $_lang['re_comments']);
                                break;
                        }
                    }
                } else {
                    $recomments = $_lang['off_comments'];
                }

                #----------------------------------
                // ERGÄNZUNG DES AVATARS VOM POSTER //
                $poster = '<a href="index.php?site=profile&amp;id=' . $ds['poster'] . '">
                     <img class="avatar_small" src="./images/avatars/' . getavatar($ds['poster']) . '">
                   </a> by <a href="index.php?site=profile&amp;id=' . $ds['poster'] . '">' . getusername($ds['poster']) . '</a>';
                // ENDE ERGÄNZUNG DES AVATARS VOM POSTER //
                $related = "";
                if ($ds['link1'] && $ds['url1'] != "http://" && $ds['window1']) {
                    $related .= '<i class="bi bi-link"></i> <a href="' . $ds['url1'] . '" target="_blank">' . $ds['link1'] . '</a> ';
                }
                if ($ds['link1'] && $ds['url1'] != "http://" && !$ds['window1']) {
                    $related .= '<i class="bi bi-link"></i> <a href="' . $ds['url1'] . '">' . $ds['link1'] . '</a> ';
                }

                if ($ds['link2'] && $ds['url2'] != "http://" && $ds['window2']) {
                    $related .= '<i class="bi bi-link"></i> <a href="' . $ds['url2'] . '" target="_blank">' . $ds['link2'] . '</a> ';
                }
                if ($ds['link2'] && $ds['url2'] != "http://" && !$ds['window2']) {
                    $related .= '<i class="bi bi-link"></i> <a href="' . $ds['url2'] . '">' . $ds['link2'] . '</a> ';
                }

                if (empty($related)) {
                    $related = "";
                }

                $settings = safe_query("SELECT * FROM plugins_news_settings");
                $dx = mysqli_fetch_array($settings);

                $maxshownnews = $dx['news'];
                if (empty($maxshownnews)) {
                    $maxshownnews = 10;
                }

                $maxnewschars = $dx['newschars'];
                if (empty($maxnewschars)) {
                    $maxnewschars = 500;
                }

                $headline = $ds['headline'];
                $content = $ds['content'];
                $switchen = $dx['switchen'];

                $translate = new multiLanguage(detectCurrentLanguage());
                $translate->detectLanguages($headline);
                $headline = $translate->getTextByLanguage($headline);
                $translate->detectLanguages($content);
                $content = $translate->getTextByLanguage($content);

                $maxheadlinechars = '35';
                if (empty($maxheadlinechars)) {
                    $maxheadlinechars = 200;
                }

                // Rimuove i tag HTML specificati
                $headline = preg_replace('/<\/?(div|p|strong|em|s|u|blockquote)>/i', '', $headline);
                // Tronca il titolo alla lunghezza massima specificata
                $headline = substr(strip_tags($headline), 0, $maxheadlinechars) . ' ...';
                $headline = '<a href="index.php?site=news&action=news_contents&amp;newsID=' . $ds['newsID'] . '" class="h4 font-primary">' . $headline . '</a>';

                // Corregge eventuali tag HTML non chiusi
                #$headline = closeOpenTags($headline);
                $headline = closeOpenTags($headline);

                // Rimuove solo i tag specificati, mantenendo gli altri intatti
                $content = preg_replace('/<\/?(div|p|strong|em|s|u|blockquote|a|b|li|td|tr|table)>/i', '', $content);

                // Applica il ridimensionamento alle Immagini
                $content = resizeImages($ds['content']);

                // Tronca il contenuto alla lunghezza massima specificata
                $content = substr(strip_tags($content), 0, $maxnewschars) . ' ...';

                // Corregge eventuali tag HTML non chiusi
                $content = closeOpenTags($content);

                $data_array = [
                    'related' => $related,
                    'headline' => $headline,
                    'rubrikname' => $rubrikname,
                    'rubric_pic' => $rubricpic,
                    'content' => $content,
                    'poster' => $poster,
                    'date' => $date,
                    'comments' => $comments,
                    'switchen' => $switchen,
                    're' => $recomments,
                ];

                #$template = $GLOBALS["_template"]->loadTemplate("news", "content_area", $data_array, $plugin_path);
                #echo $template;
                echo $tpl->loadTemplate("news", "content_area", $data_array, "plugin");

                $n++;
                unset($comments);
            }
        }
        #$template = $GLOBALS["_template"]->loadTemplate("news", "content_area_foot", $data_array, $plugin_path);
        #echo $template;
        echo $tpl->loadTemplate("news", "content_area_foot", $data_array, "plugin");

    } else {
        echo $_lang['no_news'];
    }
    if ($pages > 1) echo $page_link;
} elseif ($action == "news_archive") {

    if (isset($_GET['page'])) {
        $page = (int)$_GET['page'];
    } else {
        $page = 1;
    }

    $data_array = [
        'lang_date' => $_lang['date'],
        'lang_poster' => $_lang['poster'],
        'lang_rubric' => $_lang['rubric'],
        'lang_headline' => $_lang['headline'],
        'title' => $_lang['news'],
        'subtitle' => 'News Archive',
    ];

    #$template = $GLOBALS["_template"]->loadTemplate("news", "archive_head", $data_array, $plugin_path);
    #echo $template;
    echo $tpl->loadTemplate("news", "archive_head", $data_array, "plugin");

    $alle = safe_query("SELECT newsID FROM plugins_news WHERE displayed = '1' ");
    $gesamt = mysqli_num_rows($alle);
    $pages = 1;

    $settings = safe_query("SELECT * FROM plugins_news_settings");
    $dn = mysqli_fetch_array($settings);

    $max = $dn['newsarchiv'];
    if (empty($max)) {
        $max = 10;
    }


    for ($n = $max; $n <= $gesamt; $n += $max) {
        if ($gesamt > $n) $pages++;
    }

    if ($pages > 1) $page_link = makepagelink("index.php?site=news&action=news_archive", $page, $pages);
    else $page_link = '';

    if ($page == "1") {
        $ergebnis = safe_query("SELECT * FROM plugins_news WHERE displayed = '1' ORDER BY date DESC LIMIT 0,$max");
        $n = 1;
    } else {
        $start = $page * $max - $max;
        $ergebnis = safe_query("SELECT * FROM plugins_news WHERE displayed = '1' ORDER BY date DESC LIMIT $start,$max");
        $n = ($gesamt + 1) - $page * $max + $max;
    }

    $ds = safe_query("SELECT * FROM `plugins_news` ORDER BY `date` ");

    $n = 1;

    if (mysqli_num_rows($ergebnis)) {
        while ($ds = mysqli_fetch_array($ergebnis)) {
            $timestamp = strtotime($ds['date']); // Wandelt current_timestamp()-Wert in Unix-Timestamp um

// Uhrzeit, z. B. 14:35
$time = date('H:i', $timestamp);

// Datumsbestandteile
$tag = date('d', $timestamp);    // 16
$monat_nr = date('n', $timestamp); // 5
$year = date('Y', $timestamp);   // 25 (für 2025)

// Monatsnamen-Array (lokalisiert)
$monatsnamen = array(
    1 => $_lang['jan'],
    2 => $_lang['feb'],
    3 => $_lang['mar'],
    4 => $_lang['apr'],
    5 => $_lang['may'],
    6 => $_lang['jun'],
    7 => $_lang['jul'],
    8 => $_lang['aug'],
    9 => $_lang['sep'],
    10 => $_lang['oct'],
    11 => $_lang['nov'],
    12 => $_lang['dec']
);

$monat = $monatsnamen[$monat_nr];

// HTML-Ausgabe
$date = '<p class="news-block news-day">' . $tag . '</p> 
         <p class="news-block news-month">' . $monat . '</p> 
         <p class="news-block news-year">' . $year . '</p>';

$date2 = '<p class="news-block news-day">' . $tag . ' / ' . $monat . ' / ' . $year . '</p>';


            $rubrikname = getnewsrubricname($ds['rubric']);
            $rubrikname_link = htmlspecialchars($rubrikname);
            $rubricpic_name = getnewsrubricpic($ds['rubric']);

            // ERGÄNZUNG DES AVATARS VOM POSTER //
            $poster = '<a href="index.php?site=profile&amp;id=' . $ds['poster'] . '">
                     <img class="avatar_small" src="./images/avatars/' . getavatar($ds['poster']) . '"></a> by 
                     <a href="index.php?site=profile&amp;id=' . $ds['poster'] . '">' . getusername($ds['poster']) . '</a>';
            // ENDE ERGÄNZUNG DES AVATARS VOM POSTER //

            $rubricpic = $plugin_path . 'images/news-rubrics/' . $rubricpic_name;
            if (!file_exists($rubricpic) || $rubricpic_name == '') {
                $rubricpic = '<img style="max-height: 106px;width:100%" src="' . $plugin_path . 'images/news-rubrics/no-image.jpg" alt="" class="news_rubric avatar">';
            } else {
                $rubricpic = '<img style="max-height: 106px;width:100%" src="' . $rubricpic . '" alt="" class="news_rubric avatar">'; // KLASSE FÜR RUBRICPIC DAMIT CONTENT DARAUF ANGEZEIGT WERDEN KANN //
                $rubricpic = $rubricpic;
            }

            $settings = safe_query("SELECT * FROM plugins_news_settings");
            $dx = mysqli_fetch_array($settings);

            $maxshownnews = $dx['news'];
            if (empty($maxshownnews)) {
                $maxshownnews = 10;
            }

            $maxnewschars = $dx['newschars'];
            if (empty($maxnewschars)) {
                $maxnewschars = 500;
            }

            $maxheadlineschars = '85';
            if (empty($maxheadlineschars)) {
                $maxheadlineschars = 100;
            }

            $headlines = $ds['headline'];
            $content = $ds['content'];

            $translate = new multiLanguage(detectCurrentLanguage());
            $translate->detectLanguages($headlines);
            $headlines = $translate->getTextByLanguage($headlines);
            $translate->detectLanguages($content);
            $content = $translate->getTextByLanguage($content);

            // Rimuove i tag HTML specificati
            $headlines = preg_replace('/<\/?(div|p|strong|em|s|u|blockquote)>/i', '', $headlines);
            // Tronca il titolo alla lunghezza massima specificata
            $headlines = substr(strip_tags($headlines), 0, $maxheadlineschars) . ' ...';
            $headlines = '<a href="index.php?site=news&action=news_contents&amp;newsID=' . $ds['newsID'] . '">' . $headlines . '</a>';

            // Corregge eventuali tag HTML non chiusi
            $headlines = closeOpenTags($headlines);

            // Rimuove solo i tag specificati, mantenendo gli altri intatti
            $content = preg_replace('/<\/?(div|p|strong|em|s|u|blockquote|a|b|li|td|tr|table)>/i', '', $content);

            // Applica il ridimensionamento alle Immagini
            $content = resizeImages($ds['content']);

            // Tronca il contenuto alla lunghezza massima specificata
            $content = substr(strip_tags($content), 0, $maxnewschars) . ' ...';

            // Corregge eventuali tag HTML non chiusi
            $content = closeOpenTags($content);

            $data_array = [
                'headlines' => $headlines,
                'rubrikname' => $rubrikname,
                'rubric_pic' => $rubricpic,
                'poster' => $poster,
                'date' => $date,
                'date2' => $date2,
                'content' => $content,
                'lang_rubric' => $_lang['rubric'],
            ];

            #$template = $GLOBALS["_template"]->loadTemplate("news", "archive_content", $data_array, $plugin_path);
            #echo $template;
            echo $tpl->loadTemplate("news", "archive_content", $data_array, "plugin");


            $n++;
        }

        #$template = $GLOBALS["_template"]->loadTemplate("news", "archive_foot", $data_array, $plugin_path);
        #echo $template;
        echo $tpl->loadTemplate("news", "archive_foot", $data_array, "plugin");
    } else {
        echo $_lang['no_news'];
    }
    if ($pages > 1) echo $page_link;
} elseif ($action == "news_contents") {

    $data_array = [
        'title' => $_lang['title'],
        'subtitle' => 'News',
    ];

    #$template = $GLOBALS["_template"]->loadTemplate("news", "head", $data_array, $plugin_path);
    #echo $template;
    echo $tpl->loadTemplate("news", "head", $data_array, "plugin");

    #$template = $GLOBALS["_template"]->loadTemplate("news", "content_area_head", $data_array, $plugin_path);
    #echo $template;
    echo $tpl->loadTemplate("news", "content_area_head", $data_array, "plugin");

    if (isset($newsID)) {
        unset($newsID);
    }
    if (isset($_GET['newsID'])) {
        $newsID = $_GET['newsID'];
    }

    if (isset($newsID)) {
        $result = safe_query("SELECT * FROM plugins_news WHERE `newsID` = '" . $newsID . "'");
        $ds = mysqli_fetch_array($result); {
            $monatsnamen = array(
                1 => $_lang['jan'],
                2 => $_lang['feb'],
                3 => $_lang['mar'],
                4 => $_lang['apr'],
                5 => $_lang['may'],
                6 => $_lang['jun'],
                7 => $_lang['jul'],
                8 => $_lang['aug'],
                9 => $_lang['sep'],
                10 => $_lang['oct'],
                11 => $_lang['nov'],
                12 => $_lang['dec']
            );

            // Sicherstellen, dass das Datum als Timestamp vorliegt
            $timestamp = strtotime($ds['date']);

            // Bestandteile extrahieren
            $monat_num = date('n', $timestamp);
            $monat     = $monatsnamen[$monat_num];
            $tag       = date('d', $timestamp);
            $year      = date('Y', $timestamp);
            $time      = date('H:i', $timestamp); // z. B. "14:35"

            // Ausgabe für Template
            $date = '<p class="news-block news-day">' . $tag . '</p> 
                     <p class="news-block news-month">' . $monat . '</p> 
                     <p class="news-block news-year">' . $year . '</p>';

            $date2 = '<p class="news-block news-day">' . $tag . ' / ' . $monat . ' / ' . $year . '</p>';

            $rubrikname = getnewsrubricname($ds['rubric']);
            $rubrikname_link = htmlspecialchars($rubrikname);
            $rubricpic_name = getnewsrubricpic($ds['rubric']);
            $rubricpic = $plugin_path . 'images/news-rubrics/' . $rubricpic_name;

            if (!file_exists($rubricpic) || $rubricpic_name == '') {
                $rubricpic = '<img style="min-height: 106px;" src="' . $plugin_path . 'images/news-rubrics/no-image.jpg" alt="" class="news-rubric img-fluid card-img-top">';
            } else {
                $rubricpic = '<img style="min-height: 106px;" src="' . $rubricpic . '" alt="" class="news-rubric img-fluid card-img-top">'; // KLASSE FÜR RUBRICPIC DAMIT CONTENT DARAUF ANGEZEIGT WERDEN KANN //
                $rubricpic = $rubricpic;
            }

            $query = safe_query(
                "SELECT
                newsID
            FROM
                plugins_news 
            WHERE
                newsID='" . $newsID . "'"
            );

            $i = 0;

            $comments = '';

            if ($ds['comments']) {
                if ($ds['newsID']) {
                    $anzcomments = getanznewscomments($ds['newsID'], 'ne');
                    $replace = array('$anzcomments', '$url', '$lastposter', '$lastdate');
                    $vars = array(
                        $anzcomments,
                        'index.php?site=news&action=news_contents&amp;newsID=' . $ds['newsID'],
                        html_entity_decode(getlastnewscommentposter($ds['newsID'], 'ne')),
                        getformatdatetime(getlastnewscommentdate($ds['newsID'], 'ne'))
                    );

                    switch ($anzcomments) {
                        case 0:
                            $comments = str_replace($replace, $vars, $_lang['no_comment']);
                            break;
                        case 1:
                            $comments = str_replace($replace, $vars, $_lang['comment']);
                            break;
                        default:
                            $comments = str_replace($replace, $vars, $_lang['comments']);
                            break;
                    }
                }
            } else {
                $comments = $_lang['off_comments'];
            }

            #-----------------------------

            $recomments = '';

            if ($ds['comments']) {
                if ($ds['newsID']) {
                    $anzrecomments = getanznewsrecomments($ds['newsID'], 're');
                    $replace = array('$anzcomments', '$url', '$lastposter');
                    $vars = array(
                        $anzrecomments,
                        'index.php?site=news&action=news_contents&amp;newsID=' . $ds['newsID'],
                        html_entity_decode(getlastnewsrecommentposter($ds['newsID'], 're')),
                        #getformatdatetime(getlastnewsrecommentdate($ds[ 'newsID' ], 're'))
                    );

                    switch ($anzrecomments) {
                        case 0:
                            $recomments = str_replace($replace, $vars, $_lang['no_re_comment']);
                            break;
                        case 1:
                            $recomments = str_replace($replace, $vars, $_lang['re_comment']);
                            break;
                        default:
                            $recomments = str_replace($replace, $vars, $_lang['re_comments']);
                            break;
                    }
                }
            } else {
                $recomments = $_lang['off_comments'];
            }

            #----------------------------------
            // ERGÄNZUNG DES AVATARS VOM POSTER //
            $poster = '<a href="index.php?site=profile&amp;id=' . $ds['poster'] . '">
                     <img class="avatar_small" src="./images/avatars/' . getavatar($ds['poster']) . '">
                   </a> by <a href="index.php?site=profile&amp;id=' . $ds['poster'] . '">' . getusername($ds['poster']) . '</a>';
            // ENDE ERGÄNZUNG DES AVATARS VOM POSTER //
            $related = "";
            if ($ds['link1'] && $ds['url1'] != "http://" && $ds['window1']) {
                $related .= '&#8226; <a href="' . $ds['url1'] . '" target="_blank">' . $ds['link1'] . '</a> ';
            }
            if ($ds['link1'] && $ds['url1'] != "http://" && !$ds['window1']) {
                $related .= '&#8226; <a href="' . $ds['url1'] . '">' . $ds['link1'] . '</a> ';
            }

            if ($ds['link2'] && $ds['url2'] != "http://" && $ds['window2']) {
                $related .= '&#8226; <a href="' . $ds['url2'] . '" target="_blank">' . $ds['link2'] . '</a> ';
            }
            if ($ds['link2'] && $ds['url2'] != "http://" && !$ds['window2']) {
                $related .= '&#8226; <a href="' . $ds['url2'] . '">' . $ds['link2'] . '</a> ';
            }

            if (empty($related)) {
                $related = "";
            }

            $headline = $ds['headline'];
            $content = $ds['content'];


            $translate = new multiLanguage(detectCurrentLanguage());
            $translate->detectLanguages($headline);
            $headline = $translate->getTextByLanguage($headline);
            $translate->detectLanguages($content);
            $content = $translate->getTextByLanguage($content);

            $tags = \webspell\Tags::getTagsLinked('news', $newsID);
            // Applica il ridimensionamento alle Immagini
            $content = resizeImages($ds['content']);

            $data_array = [
                'related' => $related,
                'newsID' => $newsID,
                'headline' => $headline,
                'rubrikname' => $rubrikname,
                'rubric_pic' => $rubricpic,
                'content' => $content,
                'poster' => $poster,
                'date' => $date,
                'comments' => $comments,
                're' => $recomments,
                'switchen' => '12',
            ];


            #$news = $GLOBALS["_template"]->loadTemplate("news", "content_area", $data_array, $plugin_path);
            #echo $news;
            echo $tpl->loadTemplate("news", "content_area", $data_array, "plugin");

            #$template = $GLOBALS["_template"]->loadTemplate("news", "content_area_foot", $data_array, $plugin_path);
            #echo $template;
            echo $tpl->loadTemplate("news", "content_area_foot", $data_array, "plugin");

            $comments_allowed = $ds['comments'];
            if ($ds['newsID']) {
                $parentID = $newsID;
                $type = "ne";
            }

            $referer = "index.php?site=news&action=news_contents&newsID=$newsID";

            include("news_comments.php");
        }
    }
}
