<?php
$pageTitle       = "Gallery | Catherine's Café & Bakery, Meath Street Dublin 8";
$pageDescription = "Photos from Catherine's Café & Bakery on Meath Street, Dublin 8: full Irish breakfasts, pancakes, waffles, lunches, cream cakes and the bakery counter.";
$pageKeywords    = "Catherine's Café photos, breakfast Dublin 8 photos, bakery Liberties, cream cakes Dublin";
$activePage      = 'gallery';

require_once __DIR__ . '/includes/seo.php';
$pageSchema = [seo_breadcrumbs(['Gallery' => 'gallery.php'])];

require __DIR__ . '/includes/header.php';

// To add a photo: upload it to /images and add a line here.
$photos = [
    ['full-irish-breakfast.jpg',               'Full Irish breakfast'],
    ['eclairs-and-cream-slices-selection.jpg', 'Eclairs, cream slices and pastries'],
    ['shopfront-entrance.jpg',                 "The café on Meath Street"],
    ['pancakes-berries-tray.jpg',              'Pancakes with berries and syrup'],
    ['eggs-florentine-tray.jpg',               'Eggs florentine'],
    ['jam-cream-donut-fingers.jpg',            'Jam and cream doughnut fingers'],
    ['waffles-strawberries.jpg',               'Waffles with strawberries and cream'],
    ['interior-bakery-counter.jpg',            'The bakery counter'],
    ['french-toast-bacon-coffee.jpg',          'French toast with bacon'],
    ['chicken-avocado-salad.jpg',              'Chicken and avocado salad'],
    ['cream-donut-with-latte.jpg',             'A cream doughnut and a latte'],
    ['veggie-breakfast-tray.jpg',              'Veggie breakfast'],
    ['breakfast-fry-with-coffee-table.jpg',    'A fry and a pot of tea'],
    ['white-chocolate-cream-slices.jpg',       'Chocolate cream slices'],
    ['cheese-omelette.jpg',                    'Cheese omelette with spinach'],
    ['lunch-special-wrap-and-soup.jpg',        'Wrap, chips and soup'],
    ['interior-scones-and-breads-display.jpg', 'Scones, buns and fresh bread'],
    ['jam-donuts-and-branded-cup.jpg',         "Jam doughnuts and a Catherine's coffee"],
    ['pancakes-chocolate-drizzle.jpg',         'Pancakes with chocolate'],
    ['chicken-wings-and-chips.jpg',            'Chicken wings and chips'],
    ['traditional-fruit-slices.jpg',           'Fruit slices'],
    ['avocado-poached-egg-toast.jpg',          'Avocado and poached egg'],
    ['interior-cake-display-cabinet.jpg',      'The cake cabinet'],
    ['shopfront-bakery-door.jpg',              "Catherine's Bakery"],
    ['blt-sandwich-and-chips.jpg',             'BLT with chips'],
    ['branded-cups-and-chocolate-pastry.jpg',  'Hot and iced coffee with a chocolate pastry'],
    ['waffles-berries-cream.jpg',              'Waffles with berries'],
    ['drink-cappuccino-chocolate-dusted.jpg',  'Cappuccino'],
];
?>

<section class="page-intro">
    <div class="container">
        <h1>Gallery</h1>
        <p>More on <a class="text-link" href="<?= e($site['instagram']) ?>" target="_blank" rel="noopener">Instagram</a>.</p>
    </div>
</section>

<div class="container">
    <ul class="gallery">
        <?php foreach ($photos as $i => [$file, $caption]): ?>
            <li>
                <button type="button" class="gallery-item" data-index="<?= $i ?>"
                        data-src="images/<?= e($file) ?>" data-caption="<?= e($caption) ?>"
                        aria-label="Open photo: <?= e($caption) ?>">
                    <img src="images/<?= e($file) ?>" alt="<?= e($caption) ?>" loading="lazy">
                </button>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<dialog class="lightbox" aria-label="Photo viewer">
    <figure>
        <img src="" alt="">
        <figcaption></figcaption>
    </figure>
    <button type="button" class="lb-btn lb-prev" aria-label="Previous photo">&#8249;</button>
    <button type="button" class="lb-btn lb-next" aria-label="Next photo">&#8250;</button>
    <button type="button" class="lb-btn lb-close" aria-label="Close">&times;</button>
</dialog>

<?php require __DIR__ . '/includes/footer.php'; ?>
