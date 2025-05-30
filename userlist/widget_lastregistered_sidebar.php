<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use webspell\LanguageService;

global $languageService;

$lang = $languageService->detectLanguage();
$languageService->readPluginModule('userlist');

$tpl = new Template();
$config = mysqli_fetch_array(safe_query("SELECT selected_style FROM settings_headstyle_config WHERE id=1"));
$class = htmlspecialchars($config['selected_style']);

// Header-Daten
$data_array = [
    'class'    => $class,
    'title' => $languageService->get('lastregistered'),
    'subtitle' => 'Userlist'
];

echo $tpl->loadTemplate("userlist","head", $data_array, 'plugin');

// Abrufen der letzten 5 registrierten Benutzer
$result = safe_query("SELECT * FROM users ORDER BY registerdate DESC LIMIT 0,5");

// Template: Header des Widgets ausgeben
echo $tpl->loadTemplate("userlist","widget_lastregistered_head", $data_array, 'plugin');

// Durchlauf durch Benutzerliste
while ($row = mysqli_fetch_array($result)) {
    
    $username = '<a href="index.php?site=profile&amp;id=' . (int)$row['userID'] . '">' . htmlspecialchars($row['username']) . '</a>';

    // Registrierung als DateTime-Objekt
    $register_timestamp = (int)$row['registerdate'];
    $register_date = new DateTime();
    $register_date->setTimestamp($register_timestamp);

    $today = new DateTime();
    $today->setTime(0, 0); // auf Mitternacht setzen

    $interval = (int)$register_date->diff($today)->format('%R%a'); // +/- Tage Differenz

    // Menschlich lesbares Anmeldedatum bestimmen
    if ($interval === 0) {
        $register = $languageService->get('today');
    } elseif ($interval === -1) {
        $register = $languageService->get('tomorrow');
    } elseif ($interval > 1) {
        $register = date('d.m.y', $register_timestamp);
    } elseif ($interval === 1) {
        $register = $languageService->get('yesterday');
    } else {
        $register = date('d.m.y', $register_timestamp);
    }

    // Avatar prüfen
    $avatar = '';
    if ($getavatar = getavatar($row['userID'])) {
        $avatar = './images/avatars/' . htmlspecialchars($getavatar);
    }

    // Benutzer-Daten für Template
    $data_array = [
        'username' => $username,
        'register' => $register,
        'avatar' => $avatar
    ];

    echo $tpl->loadTemplate("userlist","widget_lastregistered_content", $data_array, "plugin");

}

echo $tpl->loadTemplate("userlist","widget_lastregistered_foot", $data_array, "plugin");
?>
