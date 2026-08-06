<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/cart.php';

$pageTitle = 'Vintage Collection';

$make = trim((string) ($_GET['make'] ?? ''));
$model = trim((string) ($_GET['model'] ?? ''));
$year = trim((string) ($_GET['year'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));

$fallbackCars = [
    [
        'id' => 1,
        'make' => 'Ford',
        'model' => 'Mustang Boss 302',
        'year' => 1969,
        'description' => 'A muscular icon from Detroit engineered for racing homologation.',
        'history' => 'Built to challenge the Camaro on the Trans-Am circuit, the Boss 302 became a motorsport legend.',
        'image_url' => 'Ford Mustang boss 302.jpeg',
    ],
    [
        'id' => 2,
        'make' => 'Jaguar',
        'model' => 'XJ40',
        'year' => 1986,
        'description' => 'British grand touring with refined lines and executive confidence.',
        'history' => 'The XJ40 marked a major technical shift for Jaguar, introducing modern electronics and engineering.',
        'image_url' => 'Jaguar XJ40.jpeg',
    ],
    [
        'id' => 3,
        'make' => 'Cadillac',
        'model' => 'Seville',
        'year' => 1975,
        'description' => 'American luxury tailored for a changing generation of drivers.',
        'history' => 'Cadillac launched the Seville as a compact luxury response to European competition in the 1970s.',
        'image_url' => 'Cadillac Seville.jpeg',
    ],
    [
        'id' => 4,
        'make' => 'Ford',
        'model' => 'GT40',
        'year' => 1966,
        'description' => 'A purpose-built endurance weapon that rewrote Le Mans history.',
        'history' => 'The GT40 famously ended Ferrari dominance at Le Mans with consecutive victories.',
        'image_url' => 'Ford GT40.jpeg',
    ],
    [
        'id' => 5,
        'make' => 'Lincoln',
        'model' => 'Continental',
        'year' => 1961,
        'description' => 'Suicide doors and timeless American presidential style.',
        'history' => 'The slab-sided Continental became one of the most recognizable luxury sedans of its era.',
        'image_url' => 'Lincoln continental.jpeg',
    ],
    [
        'id' => 6,
        'make' => 'Tucker',
        'model' => '48',
        'year' => 1948,
        'description' => 'A rare innovator packed with safety ideas years ahead of production norms.',
        'history' => 'Though short-lived, Tucker changed automotive thinking with pioneering safety features.',
        'image_url' => 'Tucker 48.jpeg',
    ],
];

$galleryCars = [];
$featuredCars = [];
$pagination = ['page' => 1, 'total_pages' => 1, 'total_items' => 0, 'offset' => 0, 'per_page' => ITEMS_PER_PAGE];
$dbError = null;

try {
    $pdo = get_pdo();

    $conditions = [];
    $params = [];

    if ($make !== '') {
        $conditions[] = 'make LIKE :make';
        $params['make'] = '%' . $make . '%';
    }

    if ($model !== '') {
        $conditions[] = 'model LIKE :model';
        $params['model'] = '%' . $model . '%';
    }

    if ($year !== '' && ctype_digit($year)) {
        $conditions[] = 'year = :year';
        $params['year'] = (int) $year;
    }

    $whereSql = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM cars {$whereSql}");
    $countStmt->execute($params);
    $totalItems = (int) $countStmt->fetchColumn();

    $pagination = paginate($totalItems, $page, ITEMS_PER_PAGE);

    $carsSql = "SELECT id, make, model, year, description, history, image_url, price FROM cars {$whereSql} ORDER BY year DESC, make ASC, model ASC LIMIT :limit OFFSET :offset";
    $carsStmt = $pdo->prepare($carsSql);

    foreach ($params as $key => $value) {
        $carsStmt->bindValue(':' . $key, $value);
    }

    $carsStmt->bindValue(':limit', $pagination['per_page'], PDO::PARAM_INT);
    $carsStmt->bindValue(':offset', $pagination['offset'], PDO::PARAM_INT);
    $carsStmt->execute();

    $galleryCars = $carsStmt->fetchAll();

    $featuredStmt = $pdo->query('SELECT id, make, model, year, description, image_url, price FROM cars ORDER BY created_at DESC LIMIT 3');
    $featuredCars = $featuredStmt->fetchAll();
} catch (Throwable $throwable) {
    $dbError = 'Database not connected yet. Showing curated local gallery.';

    $filtered = array_values(array_filter($fallbackCars, static function (array $car) use ($make, $model, $year): bool {
        $matchesMake = $make === '' || stripos($car['make'], $make) !== false;
        $matchesModel = $model === '' || stripos($car['model'], $model) !== false;
        $matchesYear = $year === '' || ((string) $car['year'] === $year);

        return $matchesMake && $matchesModel && $matchesYear;
    }));

    $pagination = paginate(count($filtered), $page, ITEMS_PER_PAGE);
    $galleryCars = array_slice($filtered, $pagination['offset'], $pagination['per_page']);
    $featuredCars = array_slice($fallbackCars, 0, 3);
}

require __DIR__ . '/includes/site_header.php';
$flash_success = get_flash('success');
?>

<?php if ($flash_success): ?>
<div id="cart-toast" style="position: fixed; top: 1.5rem; right: 1.5rem; z-index: 9999; background: linear-gradient(135deg, #1b5a34, #245e39); color: #fff; padding: 1rem 1.5rem; border-radius: 10px; box-shadow: 0 8px 32px rgba(0,0,0,0.25); font-weight: 600; font-size: 1rem; display: flex; align-items: center; gap: 0.75rem; animation: toastSlideIn 0.4s ease-out; max-width: 360px;">
    <span style="font-size: 1.4rem;">✅</span>
    <span><?= esc($flash_success) ?></span>
</div>
<style>
@keyframes toastSlideIn { from { opacity: 0; transform: translateX(100%); } to { opacity: 1; transform: translateX(0); } }
@keyframes toastFadeOut { from { opacity: 1; transform: translateX(0); } to { opacity: 0; transform: translateX(100%); } }
</style>
<script>
setTimeout(function() {
    var t = document.getElementById('cart-toast');
    if (t) { t.style.animation = 'toastFadeOut 0.5s ease-in forwards'; setTimeout(function(){ t.remove(); }, 500); }
}, 3000);
</script>
<?php endif; ?>

<section class="hero" aria-labelledby="hero-title">
    <div class="hero__overlay"></div>
    <img src="/slide_01.jpg" alt="Classic coupe parked in warm evening light" class="hero__bg">
    <div class="container hero__content">
        <p class="kicker">Curated Since 1924</p>
        <h1 id="hero-title">Vintage Cars, Stories, and Craftsmanship That Endure</h1>
        <p class="hero__lead">Explore a living archive of rare classics, motorsport legends, and hand-built automotive icons from around the world.</p>
        <a href="#gallery" class="btn btn-primary">Browse Collection</a>
    </div>
</section>

<section id="featured" class="section featured" aria-labelledby="featured-heading">
    <div class="container">
        <div class="section-heading">
            <p class="kicker">Featured Collections</p>
            <h2 id="featured-heading">Spotlight Classics</h2>
        </div>
        <div class="featured-grid">
            <?php foreach ($featuredCars as $car): ?>
                <article class="featured-card" aria-label="<?= esc($car['make']) ?> <?= esc($car['model']) ?>">
                    <img src="/<?= esc($car['image_url']) ?>" alt="<?= esc($car['year'] . ' ' . $car['make'] . ' ' . $car['model']) ?>" loading="lazy">
                    <div class="featured-card__body">
                        <h3><?= esc($car['make']) ?> <?= esc($car['model']) ?></h3>
                        <p><?= esc((string) $car['year']) ?></p>
                        <p><?= esc($car['description'] ?? '') ?></p>
                        <p style="font-weight: bold; color: #28a745; margin: 0.5rem 0;">$<?= number_format((float) $car['price'], 2) ?></p>
                        <a href="/process_cart.php?action=add&car_id=<?= $car['id'] ?>&referrer=/index.php" style="display: inline-block; background: #333; color: white; padding: 0.5rem 1rem; text-decoration: none; border-radius: 4px; margin-top: 0.5rem;">Add to Cart</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section id="gallery" class="section" aria-labelledby="gallery-heading">
    <div class="container">
        <div class="section-heading">
            <p class="kicker">Archive</p>
            <h2 id="gallery-heading">Search the Garage</h2>
        </div>

        <?php if ($dbError !== null): ?>
            <p class="notice" role="status" aria-live="polite"><?= esc($dbError) ?></p>
        <?php endif; ?>

        <form class="filter-form" method="get" action="/index.php" aria-label="Filter vintage cars">
            <label>
                <span>Make</span>
                <input type="text" name="make" value="<?= esc($make) ?>" placeholder="Ford, Jaguar...">
            </label>
            <label>
                <span>Model</span>
                <input type="text" name="model" value="<?= esc($model) ?>" placeholder="Mustang, Seville...">
            </label>
            <label>
                <span>Year</span>
                <input type="number" name="year" value="<?= esc($year) ?>" min="1886" max="2099" placeholder="1969">
            </label>
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>

        <div class="gallery-grid">
            <?php if (count($galleryCars) === 0): ?>
                <p class="empty-state">No cars matched your filter. Try a broader search.</p>
            <?php else: ?>
                <?php foreach ($galleryCars as $car): ?>
                    <article class="car-card" tabindex="0">
                        <img src="/<?= esc($car['image_url']) ?>" alt="<?= esc($car['year'] . ' ' . $car['make'] . ' ' . $car['model']) ?>" loading="lazy">
                        <div class="car-card__body">
                            <h3><?= esc($car['make']) ?> <?= esc($car['model']) ?></h3>
                            <p class="car-card__year"><?= esc((string) $car['year']) ?></p>
                            <p><?= esc($car['description']) ?></p>
                            <?php if (!empty($car['history'])): ?>
                                <p class="car-card__history"><?= esc($car['history']) ?></p>
                            <?php endif; ?>
                            <p style="font-weight: bold; color: #28a745; margin: 0.75rem 0;">$<?= number_format((float) $car['price'], 2) ?></p>
                            <a href="/process_cart.php?action=add&car_id=<?= $car['id'] ?>&referrer=/index.php" style="display: inline-block; background: #333; color: white; padding: 0.5rem 1rem; text-decoration: none; border-radius: 4px;">Add to Cart</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($pagination['total_pages'] > 1): ?>
            <nav class="pagination" role="navigation" aria-label="Gallery pagination">
                <?php for ($p = 1; $p <= $pagination['total_pages']; $p++): ?>
                    <a href="?<?= esc(http_build_query(['make' => $make, 'model' => $model, 'year' => $year, 'page' => $p])) ?>" class="<?= $p === $pagination['page'] ? 'active' : '' ?>" aria-current="<?= $p === $pagination['page'] ? 'page' : 'false' ?>">
                        <?= esc((string) $p) ?>
                    </a>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    </div>
</section>

<section id="timeline" class="section timeline" aria-labelledby="timeline-heading">
    <div class="container">
        <div class="section-heading">
            <p class="kicker">Chronology</p>
            <h2 id="timeline-heading">Evolution of Vintage Cars</h2>
        </div>
        <ol class="timeline-list">
            <li>
                <h3>1900s - Brass Era Beginnings</h3>
                <p>Hand-built touring cars introduce motoring to the elite, establishing foundational engineering principles.</p>
            </li>
            <li>
                <h3>1930s - Art Deco Luxury</h3>
                <p>Coachbuilders blend streamlined forms with opulent interiors, turning cars into rolling architecture.</p>
            </li>
            <li>
                <h3>1950s - Post-War Optimism</h3>
                <p>Chrome-heavy silhouettes and V8 power redefine automotive ambition across America and Europe.</p>
            </li>
            <li>
                <h3>1960s - Motorsport Golden Age</h3>
                <p>Manufacturers race to homologate road-going legends inspired by endurance and rally competition.</p>
            </li>
            <li>
                <h3>1970s & 1980s - Innovation Meets Regulation</h3>
                <p>Design and engineering adapt to emissions, safety, and fuel efficiency while preserving performance spirit.</p>
            </li>
        </ol>
    </div>
</section>

<section id="contact" class="section contact" aria-labelledby="contact-heading">
    <div class="container contact-grid">
        <div>
            <div class="section-heading">
                <p class="kicker">Connect</p>
                <h2 id="contact-heading">Contact the Archive Team</h2>
            </div>
            <p>Have a restoration story or a rare model to share? Send a message and our curators will reply.</p>
            <?php if ($contactSuccess = get_flash('contact_success')): ?>
                <p class="notice" role="status"><?= esc($contactSuccess) ?></p>
            <?php endif; ?>
            <form method="post" action="/process_contact.php" class="stack-form" aria-label="Contact form">
                <?= csrf_field() ?>
                <label>
                    <span>Name</span>
                    <input type="text" name="name" required maxlength="120">
                </label>
                <label>
                    <span>Email</span>
                    <input type="email" name="email" required maxlength="150">
                </label>
                <label>
                    <span>Message</span>
                    <textarea name="message" rows="5" required maxlength="2000"></textarea>
                </label>
                <button type="submit" class="btn btn-secondary">Send Message</button>
            </form>
        </div>

        <aside class="newsletter" aria-labelledby="newsletter-heading">
            <h3 id="newsletter-heading">Newsletter</h3>
            <p>Receive monthly highlights from newly indexed cars, museum events, and collector stories.</p>
            <?php if ($newsletterSuccess = get_flash('newsletter_success')): ?>
                <p class="notice" role="status"><?= esc($newsletterSuccess) ?></p>
            <?php endif; ?>
            <form method="post" action="/process_newsletter.php" class="stack-form" aria-label="Newsletter signup">
                <?= csrf_field() ?>
                <label>
                    <span>Email address</span>
                    <input type="email" name="email" required maxlength="150">
                </label>
                <button type="submit" class="btn btn-primary">Subscribe</button>
            </form>
        </aside>
    </div>
</section>

<?php require __DIR__ . '/includes/site_footer.php'; ?>
