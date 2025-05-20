<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// Konfigurationsdatei sicher einbinden
$configPath = __DIR__ . '/../../../system/config.inc.php';
if (!file_exists($configPath)) {
    die("Fehler: Konfigurationsdatei nicht gefunden.");
}
require_once $configPath;

// Datenbankverbindung aufbauen
$_database = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Fehlerprüfung
if ($_database->connect_error) {
    die("Verbindung zur Datenbank fehlgeschlagen: " . $_database->connect_error);
}
$data = json_decode(file_get_contents("php://input"), true);

file_put_contents(__DIR__ . '/debug.log', print_r($data, true), FILE_APPEND); // Debug-Ausgabe in eine Datei

if (is_array($data)) {
    $stmt = $_database->prepare("UPDATE plugins_gallery SET position = ? WHERE id = ?");
    foreach ($data as $item) {
    $stmt->bind_param("ii", $item['position'], $item['id']);
    if (!$stmt->execute()) {
        file_put_contents(__DIR__ . '/debug.log', "DB Fehler bei ID {$item['id']}: " . $stmt->error . "\n", FILE_APPEND);
    }
}
    http_response_code(200);
    echo "OK";
} else {
    http_response_code(400);
    echo "Ungültige Daten";
}