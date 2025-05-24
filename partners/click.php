<?php

$configPath = __DIR__ . '/../../../system/config.inc.php';
if (!file_exists($configPath)) {
    die("Fehler: Konfigurationsdatei nicht gefunden.");
}
require_once $configPath;

// Datenbankverbindung aufbauen
$_database = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $partnerId = (int)$_GET['id'];
    $today = date('Y-m-d');

    // Klick zählen
    $stmt = $_database->prepare("SELECT clicks FROM plugins_partners_clicks WHERE partner_id = ? AND click_date = ?");
    if (!$stmt) {
        die("Prepare failed: " . $_database->error);
    }
    $stmt->bind_param("is", $partnerId, $today);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        $update = $_database->prepare("UPDATE plugins_partners_clicks SET clicks = clicks + 1 WHERE partner_id = ? AND click_date = ?");
        if (!$update) {
            die("Prepare failed: " . $_database->error);
        }
        $update->bind_param("is", $partnerId, $today);
        $update->execute();
        $update->close();
    } else {
        $stmt->close();
        $insert = $_database->prepare("INSERT INTO plugins_partners_clicks (partner_id, click_date, clicks) VALUES (?, ?, 1)");
        if (!$insert) {
            die("Prepare failed: " . $_database->error);
        }
        $insert->bind_param("is", $partnerId, $today);
        $insert->execute();
        $insert->close();
    }

    // URL holen
    $stmt = $_database->prepare("SELECT url FROM plugins_partners WHERE id = ?");
    if (!$stmt) {
        die("Prepare failed: " . $_database->error);
    }
    $stmt->bind_param("i", $partnerId);
    $stmt->execute();
    $stmt->bind_result($url);
    if ($stmt->fetch()) {
        $stmt->close();
        $url = (stripos($url, 'http') === 0) ? $url : 'http://' . $url;
        header("Location: " . $url);
        exit;
    } else {
        $stmt->close();
        echo "Partner nicht gefunden.";
    }
} else {
    echo "Ungültige ID.";
}