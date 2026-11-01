<?php
$pageTitle       = "Catherine's Café & Bakery | Breakfast, Lunch & Cakes on Meath Street, Dublin 8";
$pageDescription = "Café and bakery on Meath Street in the Liberties, Dublin 8. Full Irish breakfasts, pancakes, hot lunches, fresh cream cakes and coffee. Eat in, order online, or book a table.";
$pageKeywords    = 'breakfast Dublin 8, café Liberties Dublin, Meath Street café, full Irish breakfast Dublin, bakery Dublin 8, cream cakes Dublin, brunch Liberties';
$activePage      = 'home';

// Answers here are all true of the café — everything comes from the menu, the
// address or the booking form. A wrong answer in a rich result is worse than
// no rich result at all.
require_once __DIR__ . '/includes/seo.php';
$pageSchema = [
    seo_faq([
        "Where is Catherine's Café & Bakery?" =>
            "Catherine's is at 16/17 Meath Street in the Liberties, Dublin 8, D08 K2E9, near the Liberty Market.",
        'Do you take bookings?' =>
            'Yes, you can book a table through the website. For a bigger group, get in touch directly.',
        'Can I order online?' =>
            'Yes. Order for collection or delivery through our own ordering site, Just Eat or Deliveroo. The links are on our Order online page.',
        'Do you serve a full Irish breakfast?' =>
            'Yes. The full Irish has 2 bacon, 2 sausages, 2 eggs, black and white pudding, hash brown, tomato and mushrooms with brown or white bread. There is a veggie version and a mini breakfast too.',
        'Do you make cakes to order?' =>
            'Yes. Celebration cakes for birthdays, communions and christenings, plus breakfast and sandwich platters for offices. Send an enquiry from the Cakes & catering page.',
    ]),
];
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/directions.php';
require __DIR__ . '/includes/order-links.php';
require __DIR__ . '/includes/reviews-data.php';

$featured = [
    ['img' => 'full-irish-breakfast-plate.jpg', 'name' => 'Full Irish Breakfast', 'price' => '13.50', 'alt' => 'A full Irish breakfast with eggs, bacon, sausages and pudding'],
    ['img' => 'eggs-florentine-tray.jpg',       'name' => 'Eggs Florentine',      'price' => '12.95', 'alt' => 'Eggs florentine with hollandaise and a hash brown on a slate'],
    ['img' => 'waffles-strawberries.jpg',       'name' => 'Waffle with Fresh Fruit', 'price' => '10.50', 'alt' => 'Waffles with strawberries and cream'],
];
?>

<section class="hero">
    <div class="hero-text">
        <p class="hero-eyebrow">Meath Street · The Liberties · Dublin 8</p>
        <h1>Breakfast, lunch and fresh bakes.</h1>
        <p class="hero-lede">A proper Irish fry, pancakes and hot lunches in the café, and cream cakes filled every morning in the bakery next door.</p>
        <div class="hero-actions">
            <a class="btn btn-light" href="menu.php">See the menu</a>
            <a class="btn btn-outline-light" href="order.php">Order online</a>
            <a class="btn btn-outline-light" href="booking.php">Book a table</a>
        </div>
        <?php if (!empty($reviewSummary)): ?>
            <p class="hero-rating">
                <span class="stars" aria-hidden="true">★★★★★</span>
                <?= e($reviewSummary['rating']) ?> on <?= e($reviewSummary['source']) ?> from <?= number_format($reviewSummary['count']) ?> reviews
            </p>
        <?php endif; ?>
    </div>
    <div class="hero-photo">
        <img src="images/shopfront-entrance.jpg" alt="The yellow Catherine's Café shopfront on Meath Street, door open and tables outside" width="684" height="853" fetchpriority="high">
    </div>
</section>

<section class="section order-band" id="order" aria-labelledby="order-title">
    <div class="container">
        <div class="section-head">
            <h2 id="order-title">Order online</h2>
            <p class="section-sub">Collection or delivery. Pick whichever you use.</p>
        </div>
        <?php render_order_options(); ?>
    </div>
</section>

<section class="section featured">
    <div class="container">
        <div class="section-head">
            <h2>From the menu</h2>
            <a class="text-link" href="menu.php">Full menu</a>
        </div>
        <ul class="dish-row">
            <?php foreach ($featured as $dish): ?>
                <li class="dish">
                    <a href="menu.php">
                        <img src="images/<?= e($dish['img']) ?>" alt="<?= e($dish['alt']) ?>" loading="lazy">
                        <span class="dish-name"><?= e($dish['name']) ?></span>
                        <?php if (show_prices()): ?>
                            <span class="dish-price">€<?= e($dish['price']) ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>

<section class="section inside bakery-feature" id="bakery">
    <div class="container inside-grid">
        <img class="inside-photo" src="images/eclairs-and-cream-slices-selection.jpg" alt="A tray of eclairs, cream slices and pastries from Catherine's Bakery" width="1400" height="815" loading="lazy">
        <div class="inside-text">
            <h2>Catherine's Bakery, next door</h2>
            <p>Eclairs, cream slices, doughnuts, fruit slices, scones and fresh bread, all on the counter from the morning. Take a box home, or have one with your coffee.</p>
            <p>Birthday, communion or christening coming up? We make celebration cakes to order.</p>
            <a class="btn btn-green" href="event-catering.php">Order a cake</a>
        </div>
    </div>
</section>

<?php if (!empty($reviews)): ?>
<section class="section reviews" aria-labelledby="reviews-title">
    <div class="container">
        <div class="section-head">
            <h2 id="reviews-title">What people say</h2>
            <a class="text-link" href="<?= e($site['maps_url']) ?>" target="_blank" rel="noopener">
                <?= e($reviewSummary['rating']) ?> ★ from <?= number_format($reviewSummary['count']) ?> Google reviews
            </a>
        </div>
        <ul class="review-grid">
            <?php foreach ($reviews as $r): ?>
                <li class="review">
                    <span class="stars" aria-label="<?= (int) $r['stars'] ?> out of 5 stars"><?= str_repeat('★', (int) $r['stars']) ?><span class="stars-off"><?= str_repeat('★', 5 - (int) $r['stars']) ?></span></span>
                    <blockquote><p><?= e($r['text']) ?></p></blockquote>
                    <p class="review-name"><?= e($r['name']) ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
<?php endif; ?>

<section class="section inside">
    <div class="container inside-grid inside-grid-flip">
        <img class="inside-photo" src="images/breakfast-fry-with-coffee-table.jpg" alt="A full fry with tea and coffee on a table in the café" width="2000" height="1509" loading="lazy">
        <div class="inside-text">
            <h2>Come in and sit down</h2>
            <p>Tables inside and a few out front on Meath Street when the weather allows, a few steps from the Liberty Market. Everything is cooked to order.</p>
            <a class="btn btn-green" href="booking.php">Book a table</a>
        </div>
    </div>
</section>

<?php if (!empty($catering['enabled'])): ?>
<section class="section catering-teaser" aria-labelledby="catering-teaser-title">
    <div class="container catering-teaser-inner">
        <div>
            <h2 id="catering-teaser-title">Cakes and catering</h2>
            <p>Celebration cakes, breakfast roll platters for the office, sandwich platters and hot lunches. Collect from Meath Street.</p>
        </div>
        <a class="btn btn-light" href="event-catering.php">Get a quote</a>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($wholesale['enabled'])): ?>
<section class="section wholesale-teaser" aria-labelledby="wholesale-teaser-title">
    <div class="container catering-teaser-inner">
        <div>
            <h2 id="wholesale-teaser-title">Run a café or shop?</h2>
            <p>Stock our fresh cream cakes, slices, doughnuts and scones, baked every morning on Meath Street.</p>
        </div>
        <a class="btn btn-green" href="wholesale.php">Wholesale &amp; trade prices</a>
    </div>
</section>
<?php endif; ?>

<section class="section visit">
    <div class="container visit-grid">
        <div>
            <h2>Visit us</h2>
            <?php render_directions_actions($site); ?>
            <?php if (!empty($site['hours'])): ?>
                <dl class="hours hours-large">
                    <?php foreach ($site['hours'] as $days => $time): ?>
                        <div><dt><?= e($days) ?></dt><dd><?= e($time) ?></dd></div>
                    <?php endforeach; ?>
                </dl>
            <?php endif; ?>
            <?php if (!empty($site['phone'])): ?>
                <p>Call us on <a class="text-link" href="tel:<?= e(preg_replace('/[^0-9+]/', '', $site['phone'])) ?>"><?= e($site['phone']) ?></a>.</p>
            <?php endif; ?>
            <p>See what's fresh today on <a class="text-link" href="<?= e($site['instagram']) ?>" target="_blank" rel="noopener">Instagram</a>.</p>
        </div>
        <a class="visit-photo" href="gallery.php">
            <img src="images/interior-bakery-counter.jpg" alt="The bakery counter full of bread, buns and tarts" loading="lazy">
            <span>View the gallery</span>
        </a>
    </div>
</section>

<?php render_map_section($site); ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
