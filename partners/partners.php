<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use webspell\LanguageService;

global $_database,$languageService;

$lang = $languageService->detectLanguage();
$languageService->readPluginModule('partners');

$config = mysqli_fetch_array(safe_query("SELECT selected_style FROM settings_headstyle_config WHERE id=1"));
$class = htmlspecialchars($config['selected_style']);

    // Header-Daten
    $data_array = [
        'class'    => $class,
        'title' => $languageService->get('title'),
        'subtitle' => 'Partners'
    ];
    
    echo $tpl->loadTemplate("partners", "head", $data_array, 'plugin');


$alertColors = ['primary', 'secondary', 'success', 'warning', 'danger', 'info'];
$filepath = "/includes/plugins/partners/images/";

$query = "SELECT * FROM plugins_partners WHERE displayed = 1 ORDER BY `sort`";
$result = $_database->query($query);

$colorIndex = 0;
?>

<title>Partnerliste mit wechselnden Alert-Farben</title>
<style>
  .card-box {
    border-radius: 0.75rem;
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1);
    overflow: hidden;
    margin-bottom: 2rem;
  }
  .img-wrapper {
    border-top-left-radius: 0.75rem;
    border-top-right-radius: 0.75rem;
    overflow: hidden;
  }
  .card .badge {
    position: absolute;
    top: 1rem;
    left: 1rem;
    padding: 0.4rem 0.9rem;
    font-weight: 600;
    border-radius: 0.5rem;
    user-select: none;
    pointer-events: none;
    text-transform: uppercase;
    z-index: 10;
    color: #000;
  }
</style>
</head>

<div class="card">
    <div class="card-body">
        <h2 class="mb-4 text-center">Unsere Partnerressourcen</h2>
        <div class="row g-4">
<?php
if ($result && $result->num_rows > 0) {
    while ($partner = $result->fetch_assoc()) {
        $name = htmlspecialchars($partner['name']);
        $banner = !empty($partner['banner']) ? $filepath . $partner['banner'] : $filepath . "no-image.jpg";

        $colorKey = $alertColors[$colorIndex];
        $colorIndex = ($colorIndex + 1) % count($alertColors);

        $urlRaw = trim($partner['url']);
        $url = '';
        if (!empty($urlRaw)) {
            $urlCandidate = (stripos($urlRaw, 'http') === 0) ? $urlRaw : 'http://' . $urlRaw;
            if (filter_var($urlCandidate, FILTER_VALIDATE_URL)) {
                $url = $urlCandidate;
            }
        }

        $btnClass = "btn btn-outline-$colorKey btn-sm";
        $partnerId = isset($partner['id']) ? (int)$partner['id'] : 0;
        ?>
        <div class="col-md-6 col-lg-4">
            <div class="card-box position-relative">
                <div class="img-wrapper alert alert-<?php echo $colorKey; ?>">
                    <img src="<?php echo htmlspecialchars($banner); ?>" alt="Banner von <?php echo $name; ?>" class="card-img-top" loading="lazy">
                </div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo $name; ?></h5>
                    <p class="card-text"><?php echo $partner['info']; ?></p>

                    <?php if (!empty($url)): ?>
                        <a href="./includes/plugins/partners/click.php?id=<?php echo $partnerId; ?>"
       target="_blank"
       rel="nofollow"
       class="<?php echo $btnClass; ?>">
        Mehr erfahren
    </a>
                    <?php else: ?>
                        <span class="text-muted">Kein gültiger Link vorhanden</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
<?php
    }
} else {
    echo '<div class="alert alert-warning">Keine Partner gefunden.</div>';
}
// Hinweis: Kein $_database->close(), wenn danach noch DB-Zugriffe erfolgen
?>
        </div>
    </div>
</div>

