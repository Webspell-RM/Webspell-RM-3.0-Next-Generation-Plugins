<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use webspell\LanguageService;

global $_database,$languageService;

$lang = $languageService->detectLanguage();
$languageService->readPluginModule('sponsors');

$config = mysqli_fetch_array(safe_query("SELECT selected_style FROM settings_headstyle_config WHERE id=1"));
$class = htmlspecialchars($config['selected_style']);

    // Header-Daten
    $data_array = [
        'class'    => $class,
        'title' => $languageService->get('title'),
        'subtitle' => 'Sponsors'
    ];
    
    echo $tpl->loadTemplate("sponsors", "head", $data_array, 'plugin');



$result = safe_query("SELECT * FROM plugins_sponsors");





$result = safe_query("SELECT * FROM plugins_sponsors WHERE active = 1 ORDER BY sort_order ASC");

// Farbzuordnung für Sponsoren-Level
$levelColors = [
    'Platin Sponsor'       => '#00bcd4',
    'Gold Sponsor'         => '#ffc107',
    'Silber Sponsor'       => '#adb5bd',
    'Bronze Sponsor'       => '#cd7f32',
    'Partner'              => '#6c757d',
    'Unterstützer'         => '#999'
];

$imagePath = '/includes/plugins/sponsors/images/';
?>

<section class="py-5">
  <div class="container">
    <div class="row align-items-center">
      
      <!-- Linker Textblock -->
      <div class="col-lg-5 mb-4 mb-lg-0">
        <h2 class="fw-bold">Unsere Sponsoren & Partner</h2>
        <p class="lead">
          Unsere Sponsoren und Partner unterstützen Webspell-RM als modernes, modulares Content-Management-System für Clans, Vereine und Projekte.
          Sie tragen dazu bei, dass wir kontinuierlich neue Features entwickeln und die Software frei und offen für alle bereitstellen können.
          Vielen Dank für eure wertvolle Unterstützung!
        </p>
      </div>

      <!-- Rechter Sponsorenbereich -->
      <div class="col-lg-7">
        <div class="row g-4">
          <?php while ($ds = mysqli_fetch_array($result)):

    $sponsorId = (int)$ds['id'];
    $name      = htmlspecialchars($ds['name']);
    $logo      = htmlspecialchars($ds['logo']);
    $level     = htmlspecialchars($ds['level']);
    $color     = $levelColors[$level] ?? '#000';

    $logoSrc   = $imagePath . $logo;
    $clickUrl  = "/includes/plugins/sponsors/click.php?id=" . $sponsorId;

?>
  <div class="col-4 text-center">
    <!-- Bild als klickbarer Link mit Tracking -->
    <a href="<?= $clickUrl ?>" target="_blank" rel="nofollow">
  <img src="<?= $logoSrc ?>" alt="<?= $name ?>" class="img-fluid" style="max-height: 80px;">
</a>
    <div class="mt-2 fw-bold text-uppercase" style="color: <?= $color ?>;"><?= $level ?></div>
  </div>
<?php endwhile; ?>
        </div>
      </div>

    </div>
  </div>
</section>
