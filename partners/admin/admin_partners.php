<?php
use webspell\LanguageService;

// Session absichern
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Sprache setzen, falls nicht vorhanden
$_SESSION['language'] = $_SESSION['language'] ?? 'de';

// LanguageService initialisieren
global $languageService;
$languageService = new LanguageService($_database);

// Admin-Modul-Sprache laden
$languageService->readPluginModule('partners');

use webspell\AccessControl;
// Den Admin-Zugriff für das Modul überprüfen
AccessControl::checkAdminAccess('partners');

$title = $languageService->get('title');

$filepath = $plugin_path."images/";

function normalizeUrl($url) {
    return (!empty($url) && !str_starts_with($url, 'http://') && !str_starts_with($url, 'https://'))
        ? 'http://' . $url
        : $url;
}

function handlePartnerImageUpload($upload, $filepath, $id, $plugin_language) {
    if (!$upload->hasFile()) {
        return $languageService->get('no_file_uploaded') ?? 'Keine Datei hochgeladen.';
    }

    if ($upload->hasError()) {
        return $upload->translateError();
    }

    $mime_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (!$upload->supportedMimeType($mime_types)) return $languageService->get('unsupported_image_type');

    $info = getimagesize($upload->getTempFile());
    if (!is_array($info)) return $languageService->get('broken_image');

    if ($info[0] > 1000 || $info[1] > 500) return sprintf($languageService->get('image_too_big'), 1000, 500);

    $extension = match ($info[2]) {
        IMAGETYPE_GIF => '.gif',
        IMAGETYPE_PNG => '.png',
        default => '.jpg'
    };

    $filename = $id . $extension;
    foreach (['.gif', '.jpg', '.png'] as $ext) {
        $oldfile = $filepath . $id . $ext;
        if (file_exists($oldfile)) unlink($oldfile);
    }

    if ($upload->saveAs($filepath . $filename)) {
        chmod($filepath . $filename, 0777);
        safe_query("UPDATE plugins_partners SET banner='" . $filename . "' WHERE id='" . $id . "'");
        return true;
    }
    return $languageService->get('upload_failed');
}

if (isset($_GET['action'])) {
    $action = $_GET['action'];
} else {
    $action = '';
}

if (isset($_GET['delete'])) {
    $CAPCLASS = new \webspell\Captcha;
    if ($CAPCLASS->checkCaptcha(0, $_GET['captcha_hash'])) {
        $id = (int)$_GET['id'];
        safe_query("DELETE FROM plugins_partners WHERE id='" . $id . "'");
        $filepath = "../images/partners/";
        foreach (['.gif', '.jpg', '.png'] as $ext) {
            if (file_exists($filepath . $id . $ext)) {
                unlink($filepath . $id . $ext);
            }
        }
    } else {
        echo $languageService->get('transaction_invalid');
    }
} elseif (isset($_POST['sortieren'])) {
    $CAPCLASS = new \webspell\Captcha;
    if ($CAPCLASS->checkCaptcha(0, $_POST['captcha_hash'])) {
        foreach ($_POST['sort'] as $sortstring) {
            $sorter = explode("-", $sortstring);
            safe_query("UPDATE plugins_partners SET sort='" . (int)$sorter[1] . "' WHERE id='" . (int)$sorter[0] . "'");
        }
    } else {
        echo $languageService->get('transaction_invalid');
    }
} elseif (isset($_POST['save']) || isset($_POST['saveedit'])) {
    $isEdit = isset($_POST['saveedit']);
    $CAPCLASS = new \webspell\Captcha;
    if ($CAPCLASS->checkCaptcha(0, $_POST['captcha_hash'])) {
        $name = htmlspecialchars($_POST['name']);
        $url = normalizeUrl(htmlspecialchars($_POST['url']));
        $info = htmlspecialchars($_POST['message']);
        $displayed = isset($_POST['displayed']) ? 1 : 0;

        if ($isEdit) {
            $id = (int)$_POST['id'];
            safe_query("UPDATE plugins_partners SET name='$name', url='$url', info='$info', displayed='$displayed' WHERE id='$id'");
            $id = $id;
        } else {
            safe_query("INSERT INTO plugins_partners (name, url, displayed, date, info, sort) VALUES ('$name', '$url', '$displayed', '" . time() . "', '$info', '1')");
            $id = mysqli_insert_id($_database);
        }

        $_language->readModule('formvalidation', true, true);
        $filepath = $plugin_path . "/images/";
        $upload = new \webspell\HttpUpload('banner');
        $uploadResult = handlePartnerImageUpload($upload, $filepath, $id, $plugin_language);

        if (is_string($uploadResult)) {
            echo generateErrorBox($uploadResult);
        }
    } else {
        echo $languageService->get('transaction_invalid');
    }
} elseif (isset($_POST['partners_settings_save'])) {
    $CAPCLASS = new \webspell\Captcha;
    if ($CAPCLASS->checkCaptcha(0, $_POST['captcha_hash'])) {
        safe_query("UPDATE plugins_partners_settings SET partners='" . htmlspecialchars($_POST['partners']) . "'");
        redirect("admincenter.php?site=admin_partners&action=admin_partners_settings", "", 0);
    } else {
        redirect("admincenter.php?site=admin_partners&action=admin_partners_settings", $languageService->get('transaction_invalid'), 3);
    }
}


if ($action == "add") {

    $CAPCLASS = new \webspell\Captcha;
    $CAPCLASS->createTransaction();
    $hash = $CAPCLASS->getHash();

    echo '<div class="card">
        <div class="card-header">
            <i class="bi bi-person-vcard"></i> ' . $languageService->get('partners') . '
        </div>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb t-5 p-2 bg-light">
                <li class="breadcrumb-item"><a href="admincenter.php?site=admin_partners">' . $languageService->get('partners') . '</a></li>
                <li class="breadcrumb-item active" aria-current="page">' . $languageService->get('add_partner') . '</li>
            </ol>
        </nav>

        <div class="card-body">
        <div class="container py-5">
        <form class="form-horizontal" method="post" action="admincenter.php?site=admin_partners" enctype="multipart/form-data">

            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label">' . $languageService->get('partner_name') . ':</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="name" placeholder="Name"  required />
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label">' . $languageService->get('banner') . ':</label>
                <div class="col-sm-10">
                    <input class="form-control" name="banner" type="file" />
                    <small class="form-text text-muted">(max. 1000x500)</small>
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label">' . $languageService->get('homepage_url') . ':</label>
                <div class="col-sm-10">
                    <input type="url" class="form-control" name="url" placeholder="https://" required />
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label">Info:</label>
                <div class="col-sm-10">
                    <textarea class="ckeditor" id="ckeditor" name="message" rows="10"></textarea>
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label">' . $languageService->get('is_displayed') . ':</label>
                <div class="col-sm-10 form-check form-switch" style="padding-left: 43px;">
                    <input class="form-check-input" type="checkbox" name="displayed" value="1" checked="checked" />
                </div>
            </div>

            <div class="mb-3 row">
                <div class="col-sm-10 offset-sm-2">
                    <input type="hidden" name="captcha_hash" value="' . $hash . '" />
                    <button class="btn btn-success btn-sm" type="submit" name="save">' . $languageService->get('add_partner') . '</button>
                </div>
            </div>

        </form>
        </div>
        </div>
    </div>';
}
 elseif ($action == "edit") {

    $CAPCLASS = new \webspell\Captcha;
    $CAPCLASS->createTransaction();
    $hash = $CAPCLASS->getHash();

    echo '<div class="card">
        <div class="card-header">
            <i class="bi bi-person-vcard"></i> ' . $languageService->get('partners') . '
        </div>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb t-5 p-2 bg-light">
                <li class="breadcrumb-item"><a href="admincenter.php?site=admin_partners">' . $languageService->get('partners') . '</a></li>
                <li class="breadcrumb-item active" aria-current="page">' . $languageService->get('edit_partner') . '</li>
            </ol>
        </nav>

        <div class="card-body">';

    $id = (int) $_GET['id'];
    $ergebnis = safe_query("SELECT * FROM plugins_partners WHERE id='$id'");
    $ds = mysqli_fetch_array($ergebnis);

    $pic = '<img id="img-upload" class="img-thumbnail" style="width: 100%; max-width: 150px" src="../' . $filepath . (!empty($ds['banner']) ? $ds['banner'] : 'no-image.jpg') . '" alt="">';

    $displayed = '<input class="form-check-input" type="checkbox" name="displayed" value="1"' . ($ds['displayed'] == '1' ? ' checked' : '') . ' />';

    echo '<div class="container py-5">
        <form class="form-horizontal" method="post" action="admincenter.php?site=admin_partners" enctype="multipart/form-data">
        <div class="mb-3 row">
            <label class="col-sm-2 col-form-label">' . $languageService->get('current_banner') . ':</label>
            <div class="col-sm-10">' . $pic . '</div>
        </div>

        <div class="mb-3 row">
            <label class="col-sm-2 col-form-label">' . $languageService->get('partner_name') . ':</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="name" value="' . htmlspecialchars($ds['name']) . '" />
            </div>
        </div>

        <div class="mb-3 row">
            <label class="col-sm-2 col-form-label">' . $languageService->get('banner') . ':</label>
            <div class="col-sm-10">
                <input class="form-control" type="file" name="banner" /> <small class="form-text text-muted">(max. 1000x500)</small>
            </div>
        </div>

        <div class="mb-3 row">
            <label class="col-sm-2 col-form-label">' . $languageService->get('homepage_url') . ':</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="url" value="' . htmlspecialchars($ds['url']) . '" />
            </div>
        </div>

        <div class="mb-3 row">
            <label class="col-sm-2 col-form-label">Info:</label>
            <div class="col-sm-10">
                <textarea class="ckeditor" id="ckeditor" name="message" rows="10">' . htmlspecialchars($ds['info']) . '</textarea>
            </div>
        </div>

        <div class="mb-3 row">
            <label class="col-sm-2 col-form-label">' . $languageService->get('is_displayed') . ':</label>
            <div class="col-sm-10 form-check form-switch" style="padding-left: 43px;">
                ' . $displayed . '
            </div>
        </div>

        <div class="mb-3 row">
            <div class="col-sm-10 offset-sm-2">
                <input type="hidden" name="captcha_hash" value="' . $hash . '" />
                <input type="hidden" name="id" value="' . $id . '" />
                <button class="btn btn-warning btn-sm" type="submit" name="saveedit">' . $languageService->get('edit_partner') . '</button>
            </div>
        </div>
    </form>
    </div>
    </div>
</div>';
}
 elseif ($action == "admin_partners_settings") {

    $CAPCLASS = new \webspell\Captcha;
    $CAPCLASS->createTransaction();
    $hash = $CAPCLASS->getHash();

    $settings = safe_query("SELECT * FROM plugins_partners_settings");
    $ds = mysqli_fetch_array($settings);

    echo '<div class="card">
            <div class="card-header"><i class="bi bi-gear"></i> '.$languageService->get('partners_settings').'</div>

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb t-5 p-2 bg-light">
                    <li class="breadcrumb-item"><a href="admincenter.php?site=admin_partners">' . $languageService->get( 'partners') . '</a></li>
                    <li class="breadcrumb-item active" aria-current="page">'.$languageService->get('partners_settings').'</li>
                </ol>
            </nav>  
            <div class="card-body">
            <div class="container py-5">
                <form class="form-horizontal" method="post" action="admincenter.php?site=admin_partners">
                    <div class="mb-3 row">
                        <label class="col-sm-2 control-label">'.$languageService->get('max_partners_displayed').':</label>
                        <div class="col-sm-1">
                            <input type="number" class="form-control" name="partners" value="' . (int)$ds['partners'] . '" min="1" />
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <div class="col-sm-offset-2 col-sm-10">
                            <input type="hidden" name="captcha_hash" value="' . $hash . '" />
                            <button class="btn btn-primary btn-sm" type="submit" name="partners_settings_save">'.$languageService->get('save_settings').'</button>
                        </div>
                    </div>
                </form>
            </div>
            </div>
        </div>';
}
 else {

echo '<div class="card">
    <div class="card-header">
        <i class="bi bi-person-vcard"></i> ' . $languageService->get('partners') . '
    </div>

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb t-5 p-2 bg-light">
            <li class="breadcrumb-item"><a href="admincenter.php?site=admin_partners">' . $languageService->get('partners') . '</a></li>
            <li class="breadcrumb-item active" aria-current="page">New / Edit</li>
        </ol>
    </nav>

    <div class="card-body">
        <div class="form-group row">
            <label class="col-md-1 control-label">' . $languageService->get('options') . ':</label>
            <div class="col-md-8">
                <a href="admincenter.php?site=admin_partners&amp;action=add" class="btn btn-primary btn-sm">' . $languageService->get('new_partner') . '</a>
                <a href="admincenter.php?site=admin_partners&amp;action=admin_partners_settings" class="btn btn-primary btn-sm">' . $languageService->get('partners_settings') . '</a>
            </div>
        </div>

        <div class="container py-5">
            <form method="post" action="admincenter.php?site=admin_partners">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>' . $languageService->get('partners') . '</th>
                            <th>' . $languageService->get('clicks') . '</th>
                            <th>' . $languageService->get('is_displayed') . '</th>
                            <th>' . $languageService->get('actions') . '</th>
                            <th width="10%">' . $languageService->get('sort') . '</th>
                        </tr>
                    </thead>
                    <tbody>';

$partners = safe_query("
    SELECT p.*, COALESCE(SUM(c.clicks), 0) AS total_clicks
    FROM plugins_partners p
    LEFT JOIN plugins_partners_clicks c ON p.id = c.partner_id
    GROUP BY p.id
    ORDER BY p.sort
");

$tmp = mysqli_fetch_assoc(safe_query("SELECT count(id) as cnt FROM plugins_partners"));
$anzpartners = $tmp['cnt'];

$CAPCLASS = new \webspell\Captcha;
$CAPCLASS->createTransaction();
$hash = $CAPCLASS->getHash();
$CAPCLASS->createTransaction();
$hash_2 = $CAPCLASS->getHash();

$i = 1;
while ($db = mysqli_fetch_array($partners)) {
    $td = ($i % 2) ? 'td1' : 'td2';
    $displayed = ($db['displayed'] == 1)
        ? '<span class="text-success fw-bold">' . $languageService->get('yes') . '</span>'
        : '<span class="text-danger fw-bold">' . $languageService->get('no') . '</span>';

    $days = round((time() - $db['date']) / (60 * 60 * 24));
    $perday = $days ? round($db['total_clicks'] / $days, 2) : $db['total_clicks'];

    $modal_id = 'confirm-delete-' . (int)$db['id'];

    echo '<tr>
        <td><a href="' . htmlspecialchars($db['url']) . '" target="_blank">' . htmlspecialchars($db['name']) . '</a></td>
        <td>' . (int)$db['total_clicks'] . ' (' . $perday . ')</td>
        <td>' . $displayed . '</td>
        <td>
            <a href="admincenter.php?site=admin_partners&amp;action=edit&amp;id=' . (int)$db['id'] . '" class="btn btn-warning btn-sm">' . $languageService->get('edit') . '</a>

            <!-- Delete Button -->
            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#' . $modal_id . '" data-href="admincenter.php?site=admin_partners&amp;delete=true&amp;id=' . (int)$db['id'] . '&amp;captcha_hash=' . $hash . '">
                ' . $languageService->get('delete') . '
            </button>

            <!-- Modal -->
            <div class="modal fade" id="' . $modal_id . '" tabindex="-1" aria-labelledby="modalLabel-' . $modal_id . '" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalLabel-' . $modal_id . '">' . $languageService->get('partners') . '</h5>
                            <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal" aria-label="' . $languageService->get('close') . '"></button>
                        </div>
                        <div class="modal-body">
                            <p>' . $languageService->get('really_delete') . '</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">' . $languageService->get('close') . '</button>
                            <a class="btn btn-danger btn-ok btn-sm" href="admincenter.php?site=admin_partners&amp;delete=true&amp;id=' . (int)$db['id'] . '&amp;captcha_hash=' . $hash . '">' . $languageService->get('delete') . '</a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Modal END -->
        </td>
        <td width="8%" class="' . $td . '" align="center">
            <select name="sort[]">';
    for ($j = 1; $j <= $anzpartners; $j++) {
        $selected = ($db['sort'] == $j) ? 'selected="selected"' : '';
        echo '<option value="' . $db['id'] . '-' . $j . '" ' . $selected . '>' . $j . '</option>';
    }
    echo '</select>
        </td>
    </tr>';

    $i++;
}

echo '
    <tr>
        <td colspan="5" class="text-end">
            <input type="hidden" name="captcha_hash" value="' . $hash_2 . '" />
            <button class="btn btn-primary btn-sm" type="submit" name="sortieren">' . $languageService->get('to_sort') . '</button>
        </td>
    </tr>
    </tbody>
    </table>
    </form>
    </div>
    </div>
    </div>';


}
