<?php
// Verbindung zur DB
require_once __DIR__ . '/../../../system/config.inc.php';
global $_database;

// Links mit Kategorie abrufen
$sql = "SELECT l.*, c.title AS category, c.icon, c.id AS category_id
        FROM plugins_links l
        LEFT JOIN plugins_links_categories c ON l.category_id = c.id
        WHERE l.visible = 1
        ORDER BY l.category_id ASC, l.title ASC";

$result = $_database->query($sql);
$links = [];
$categoriesForFilter = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        if (empty($row['image'])) {
            $row['image'] = 'assets/default_thumb.jpg';
        }

        // URL prüfen und korrigieren
        $urlRaw = trim($row['url'] ?? '');
        if (!empty($urlRaw)) {
            $urlCandidate = (stripos($urlRaw, 'http') === 0) ? $urlRaw : 'http://' . $urlRaw;
            if (filter_var($urlCandidate, FILTER_VALIDATE_URL)) {
                $row['valid_url'] = $urlCandidate;
            } else {
                $row['valid_url'] = '';
            }
        } else {
            $row['valid_url'] = '';
        }

        $links[] = $row;
        $categoriesForFilter[$row['category_id']] = $row['category'];
    }
}
?>

<!-- Isotope -->
<script src="https://unpkg.com/isotope-layout@3/dist/isotope.pkgd.min.js"></script>

<div class="container py-4">
  <h1 class="mb-4 text-center">Linkliste</h1>

  <!-- Filter Buttons -->
  <div class="mb-4 text-center">
    <button class="btn btn-outline-secondary me-2 filter-btn active" data-filter="*">Alle</button>
    <?php foreach ($categoriesForFilter as $catId => $catTitle): ?>
      <button class="btn btn-outline-secondary me-2 filter-btn" data-filter=".cat<?= $catId ?>">
        <?= htmlspecialchars($catTitle) ?>
      </button>
    <?php endforeach; ?>
  </div>

  <!-- Grid -->
  <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-4 isotope-container">
    <?php foreach ($links as $link): ?>
      <div class="col cat<?= $link['category_id'] ?>">
        <div class="card h-100 shadow-sm d-flex flex-column">
          <img src="<?= htmlspecialchars($link['image']) ?>" 
               class="card-img-top" 
               alt="<?= htmlspecialchars($link['title']) ?>" 
               onerror="this.onerror=null; this.src='assets/default_thumb.jpg';" 
               style="height: 160px; object-fit: cover;">
          
          <div class="card-body d-flex flex-column">
            <h5 class="card-title"><?= htmlspecialchars($link['title']) ?></h5>

            <?php if (!empty($link['description'])): ?>
              <p class="card-text"><?= htmlspecialchars($link['description']) ?></p>
            <?php endif; ?>

            <p class="mt-auto mb-2">
              <small class="text-muted">
                <?php if (!empty($link['icon'])): ?>
                  <i class="bi <?= htmlspecialchars($link['icon']) ?>"></i>
                <?php endif; ?>
                <?= htmlspecialchars($link['category']) ?>
              </small>
            </p>

            <!-- Button mit Klick-Tracking -->
            <?php if (!empty($link['valid_url'])): ?>
              <a href="./includes/plugins/links/click.php?id=<?= $link['id'] ?>"
                 target="_blank"
                 rel="nofollow"
                 class="btn btn-primary w-100">
                  Besuchen
              </a>
              <?php else: ?>
                <span class="text-muted">Kein gültiger Link vorhanden</span>
              <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Filter Script -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  const iso = new Isotope('.isotope-container', {
    itemSelector: '.col',
    layoutMode: 'fitRows'
  });

  document.querySelectorAll('.filter-btn').forEach(button => {
    button.addEventListener('click', function () {
      const filterValue = this.getAttribute('data-filter');
      iso.arrange({ filter: filterValue });

      document.querySelectorAll('.filter-btn').forEach(btn => 
        btn.classList.remove('active', 'btn-primary')
      );
      this.classList.add('active', 'btn-primary');
    });
  });
});
</script>
