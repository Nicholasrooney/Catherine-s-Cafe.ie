<?php
$pageTitle       = "Menu | Catherine's Café & Bakery, Meath Street Dublin 8";
$pageDescription = "The full Catherine's menu with prices: full Irish breakfasts, pancakes, French toast, eggs, sandwiches, hot lunches, bakery, coffee and drinks.";
$pageKeywords    = 'café menu Dublin 8, full Irish breakfast price Dublin, breakfast menu Liberties, Meath Street café menu, bakery Dublin 8';
$activePage      = 'menu';

require_once __DIR__ . '/includes/menu-data.php';
require_once __DIR__ . '/includes/seo.php';
$pageSchema = [
    seo_menu_node($menu),
    seo_breadcrumbs(['Menu' => 'menu.php']),
];

require __DIR__ . '/includes/header.php';
?>

<section class="page-intro">
    <div class="container">
        <h1>Menu</h1>
        <p>Everything is cooked to order. If you have an allergy or any dietary need, please tell us when you order and we will tell you exactly what is in each dish.</p>
        <nav class="menu-jump" aria-label="Menu sections">
            <?php foreach ($menu as $section): ?>
                <a href="#<?= e($section['id']) ?>"><?= e($section['title']) ?></a>
            <?php endforeach; ?>
        </nav>
    </div>
</section>

<div class="container menu-layout">
    <?php foreach ($menu as $section): ?>
        <section class="menu-section" id="<?= e($section['id']) ?>" aria-labelledby="<?= e($section['id']) ?>-title">
            <h2 id="<?= e($section['id']) ?>-title">
                <?= e($section['title']) ?>
                <?php if ($section['note']): ?><span class="menu-note">(<?= e($section['note']) ?>)</span><?php endif; ?>
            </h2>
            <?php if (!empty($section['photo'])): ?>
                <img class="menu-photo" src="images/<?= e($section['photo'][0]) ?>" alt="<?= e($section['photo'][1]) ?>" loading="lazy">
            <?php endif; ?>
            <ul class="menu-items">
                <?php foreach ($section['items'] as $item): ?>
                    <li class="menu-item">
                        <div class="menu-item-top">
                            <h3>
                                <?= e($item['name']) ?>
                                <?php if (!empty($item['allergens'])): ?>
                                    <span class="allergens">
                                        <?php foreach ($item['allergens'] as $code): ?>
                                            <abbr title="<?= e($allergenKey[$code] ?? $code) ?>"><?= e($code) ?></abbr>
                                        <?php endforeach; ?>
                                    </span>
                                <?php endif; ?>
                            </h3>
                            <?php if (show_prices() && $item['price'] !== ''): ?>
                                <span class="price">€<?= e($item['price']) ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if ($item['desc']): ?>
                            <p class="menu-desc"><?= e($item['desc']) ?></p>
                        <?php endif; ?>
                        <?php foreach ($item['extra'] as $extra): ?>
                            <p class="menu-extra"><?= e(show_prices() ? $extra : strip_prices($extra)) ?></p>
                        <?php endforeach; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endforeach; ?>

    <section class="allergen-key" id="allergens" aria-labelledby="allergen-title">
        <h2 id="allergen-title">Allergens</h2>
        <p>Full allergen information for every dish and bake is available from our staff. Please ask before you order, and tell us about any allergy or intolerance.</p>
    </section>
</div>

<section class="section cta-band">
    <div class="container cta-inner">
        <h2>Hungry already?</h2>
        <div class="cta-actions">
            <a class="btn btn-light" href="order.php">Order online</a>
            <a class="btn btn-outline-light" href="booking.php">Book a table</a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
