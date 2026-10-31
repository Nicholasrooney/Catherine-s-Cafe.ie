<?php
$pageTitle       = "Order Online | Catherine's Café & Bakery, Meath Street Dublin 8";
$pageDescription = "Order breakfast, lunch and coffee from Catherine's Café & Bakery on Meath Street, Dublin 8, for collection or delivery. Order direct, or through Just Eat or Deliveroo.";
$pageKeywords    = 'order breakfast Dublin 8, food delivery Liberties, takeaway Meath Street, breakfast delivery Dublin';
$activePage      = 'order';

require_once __DIR__ . '/includes/seo.php';
$pageSchema = [
    seo_breadcrumbs(['Order online' => 'order.php']),
];

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/order-links.php';
?>

<section class="page-intro">
    <div class="container">
        <h1>Order online</h1>
        <p>The full café menu, for collection from Meath Street or delivered to your door. Choose whichever you normally use.</p>
    </div>
</section>

<section class="section order-page">
    <div class="container">
        <?php render_order_options(); ?>
        <p class="order-help">
            Rather ring it in? Call us on
            <a class="text-link" href="tel:<?= e(preg_replace('/[^0-9+]/', '', $site['phone'])) ?>"><?= e($site['phone']) ?></a>
            and collect it at the counter.
        </p>
    </div>
</section>

<section class="section inside">
    <div class="container inside-grid">
        <img class="inside-photo" src="images/breakfast-roll-plate.jpg" alt="A breakfast roll cut in half" width="1534" height="920" loading="lazy">
        <div class="inside-text">
            <h2>Not sure what to get?</h2>
            <p>The full Irish and the breakfast bap come up again and again in our reviews. The whole menu, with prices, is on the menu page.</p>
            <a class="btn btn-green" href="menu.php">See the menu</a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
