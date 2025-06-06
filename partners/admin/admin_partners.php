<?php

use webspell\LanguageService;
use webspell\AccessControl;

// Session absichern
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Sprache setzen, falls nicht vorhanden
$_SESSION['language'] = $_SESSION['language'] ?? 'de';

// LanguageService initialisieren
global $languageService;
$lang = $languageService->detectLanguage();
$languageService = new LanguageService($_database);

// Admin-Modul-Sprache laden
$languageService->readPluginModule('partners');

// Admin-Zugriff prüfen
AccessControl::checkAdminAccess('partners');

// Einfaches Routing: action aus GET/POST
$action = $_GET['action'] ?? ($_POST['action'] ?? null);

// Pfad zu Logo-Uploads
$uploadDir = dirname(__DIR__) . '/images/';

// Helper Funktion: Datei-Upload verarbeiten
function handleLogoUpload($file, $oldFile = null) {
    global $uploadDir;

    if ($file && $file['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($ext, $allowed)) {
            return ['error' => 'Nur JPG, PNG, GIF erlaubt'];
        }

        $filename = uniqid('partner_') . '.' . $ext;
        $target = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $target)) {
            // Altes Logo löschen
            if ($oldFile && file_exists($uploadDir . $oldFile)) {
                unlink($uploadDir . $oldFile);
            }
            return ['filename' => $filename];
        } else {
            return ['error' => 'Fehler beim Hochladen'];
        }
    }
    return ['filename' => $oldFile]; // Kein Upload -> altes behalten
}

// POST: Löschen
if (isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    // Logo-Datei holen
    $res = $_database->query("SELECT logo FROM plugins_partners WHERE id = $id");
    $row = $res->fetch_assoc();
    if ($row && $row['logo']) {
        @unlink($uploadDir . $row['logo']);
    }
    $_database->query("DELETE FROM plugins_partners WHERE id = $id");
    header("Location: admincenter.php?site=admin_partners");
    exit;
}

// POST: Add/Edit partner speichern
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_partner'])) {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = $_database->real_escape_string(trim($_POST['name']));
    $url = $_database->real_escape_string(trim($_POST['url']));
    $active = isset($_POST['active']) ? 1 : 0;

    $oldLogo = '';
    if ($id > 0) {
        $res = $_database->query("SELECT logo FROM plugins_partners WHERE id = $id");
        $row = $res->fetch_assoc();
        $oldLogo = $row['logo'] ?? '';
    }

    $uploadResult = handleLogoUpload($_FILES['logo'] ?? null, $oldLogo);

    if (isset($uploadResult['error'])) {
        $error = $uploadResult['error'];
    } else {
        $logo = $uploadResult['filename'];

        if ($id > 0) {
            // Update
            $stmt = $_database->prepare("UPDATE plugins_partners SET name=?, url=?, logo=?, active=? WHERE id=?");
            $stmt->bind_param("sssii", $name, $url, $logo, $active, $id);
            $stmt->execute();
            $stmt->close();
        } else {
            // Insert
            $stmt = $_database->prepare("INSERT INTO plugins_partners (name, url, logo, active) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssi", $name, $url, $logo, $active);
            $stmt->execute();
            $stmt->close();
        }

        header("Location: admincenter.php?site=admin_partners");
        exit;
    }
}


// === Anzeige abhängig von action ===
if ($action === 'add' || $action === 'edit') {

    $editpartner = null;

    if ($action === 'edit' && isset($_GET['edit']) && is_numeric($_GET['edit'])) {
        $id = (int)$_GET['edit'];
        $res = $_database->query("SELECT * FROM plugins_partners WHERE id = $id");
        $editpartner = $res->fetch_assoc();
    }

    ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div><i class="bi bi-journal-text"></i> Partner verwalten</div>
        <div>
            <a href="admincenter.php?site=admin_partners&action=add" class="btn btn-success btn-sm"><i class="bi bi-plus"></i> Neu</a>
            <a href="admincenter.php?site=admin_partners_settings" class="btn btn-primary btn-sm"><i class="bi bi-tags"></i> Partner Setting</a>
        </div>
    </div>

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb t-5 p-2 bg-light">
            <li class="breadcrumb-item"><a href="admincenter.php?site=admin_partners">Partner verwalten</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= ($action === 'add' ? 'Partner hinzufügen' : 'Partner bearbeiten') ?></li>
        </ol>
    </nav>

    <div class="card-body p-0">
        <div class="container py-5">

        <h1><?= $editpartner ? 'partner bearbeiten' : 'Neuen partner hinzufügen' ?></h1>

        <?php if ($error): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" action="admincenter.php?site=admin_partners&action=<?= $action ?><?= $editpartner ? '&edit=' . (int)$editpartner['id'] : '' ?>">
            <input type="hidden" name="id" value="<?= htmlspecialchars($editpartner['id'] ?? '') ?>">

            <div class="mb-3">
                <label for="name" class="form-label">Name *</label>
                <input type="text" class="form-control" id="name" name="name" required value="<?= htmlspecialchars($editpartner['name'] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label for="url" class="form-label">URL</label>
                <input type="url" class="form-control" id="url" name="url" value="<?= htmlspecialchars($editpartner['url'] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label for="logo" class="form-label">Logo (JPG, PNG, GIF)</label>
                <input type="file" class="form-control" id="logo" name="logo" <?= $editpartner ? '' : 'required' ?>>
                <?php if (!empty($editpartner['logo'])): ?>
                    <div class="mt-2">
                        <img src="/includes/plugins/partners/images/<?= htmlspecialchars($editpartner['logo']) ?>" alt="Logo" style="max-height:80px;">
                    </div>
                <?php endif; ?>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="active" name="active" <?= (!isset($editpartner['active']) || $editpartner['active'] == 1) ? 'checked' : '' ?>>
                <label for="active" class="form-check-label">Aktiv</label>
            </div>

            <button type="submit" name="save_partner" class="btn btn-primary"><?= $editpartner ? 'Speichern' : 'Hinzufügen' ?></button>
            <a href="admincenter.php?site=admin_partners" class="btn btn-secondary">Zurück zur Liste</a>
        </form>

   

    <?php
} else {
    // Standard: Liste aller partneren anzeigen
    $respartners = $_database->query("
    SELECT s.*, 
           COALESCE(k.click_count, 0) AS clicks
    FROM plugins_partners s
    LEFT JOIN (
        SELECT itemID, COUNT(*) AS click_count
        FROM link_clicks
        WHERE plugin = 'partners'
        GROUP BY itemID
    ) k ON s.id = k.itemID
    ORDER BY s.sort_order ASC
");
    ?>

    <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div><i class="bi bi-journal-text"></i> Partneren verwalten</div>
        <div>
            <a href="admincenter.php?site=admin_partners&action=add" class="btn btn-success btn-sm"><i class="bi bi-plus"></i> Neu</a>
            <a href="admincenter.php?site=admin_partners_settings" class="btn btn-primary btn-sm"><i class="bi bi-tags"></i> Partner Setting</a>
        </div>
    </div>

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb t-5 p-2 bg-light">
            <li class="breadcrumb-item"><a href="admincenter.php?site=admin_partners">Partneren verwalten</a></li>
            <li class="breadcrumb-item active" aria-current="page">Übersicht</li>
        </ol>
    </nav>

    <div class="card-body p-0">
        <div class="container py-5">

        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>Logo</th>
                    <th>Name</th>
                    <th>URL</th>
                    <th>Klicks (pro Tag)</th>
                    <th>Aktiv</th>
                    <th>Aktionen</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($partner = $respartners->fetch_assoc()):
                    $createdTimestamp = isset($partner['created_at']) ? strtotime($partner['created_at']) : time();
                    $days = max(1, round((time() - $createdTimestamp) / (60 * 60 * 24))); 
                    $perday = round($partner['clicks'] / $days, 2);
                ?>
                <tr>
                    <td>
                        <?php if ($partner['logo'] && file_exists($uploadDir . $partner['logo'])): ?>
                            <img src="/includes/plugins/partners/images/<?= htmlspecialchars($partner['logo']) ?>" alt="Logo" style="max-height:40px;">
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($partner['name']) ?></td>
                    <td>
                        <?php if ($partner['url']): ?>
                            <a href="<?= htmlspecialchars($partner['url']) ?>" target="_blank" rel="nofollow"><?= htmlspecialchars($partner['url']) ?></a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td>
                        <?= (int)$partner['clicks'] ?> (Ø <?= $perday ?>/Tag)
                    </td>
                    <td><?= $partner['active'] ? 'Ja' : 'Nein' ?></td>
                    <td>
                        <a href="admincenter.php?site=admin_partners&action=edit&edit=<?= $partner['id'] ?>" class="btn btn-sm btn-warning">Bearbeiten</a>
                        <form method="post" style="display:inline-block" onsubmit="return confirm('Wirklich löschen?');">
                            <input type="hidden" name="delete_id" value="<?= $partner['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Löschen</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if ($respartners->num_rows === 0): ?>
                    <tr><td colspan="7" class="text-center">Keine partneren gefunden.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

    </div></div></div>

    <?php
}
