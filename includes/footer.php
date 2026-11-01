</main>

<footer class="site-footer">
    <div class="footer-inner">
        <div class="footer-col">
            <p class="footer-name"><?= e($site['name']) ?></p>
            <?php if ($site['address']): ?>
                <p><?= e($site['address']) ?></p>
            <?php endif; ?>
            <?php if ($site['maps_url']): ?>
                <p><a href="<?= e($site['maps_url']) ?>" target="_blank" rel="noopener">Get directions</a></p>
            <?php endif; ?>
        </div>

        <div class="footer-col">
            <?php if ($site['phone']): ?>
                <p><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $site['phone'])) ?>"><?= e($site['phone']) ?></a></p>
            <?php endif; ?>
            <?php if ($site['email']): ?>
                <p><a href="mailto:<?= e($site['email']) ?>"><?= e($site['email']) ?></a></p>
            <?php endif; ?>
            <p><a href="<?= e($site['instagram']) ?>" target="_blank" rel="noopener">Instagram</a>
                <?php if (!empty($site['facebook'])): ?>
                    · <a href="<?= e($site['facebook']) ?>" target="_blank" rel="noopener">Facebook</a>
                <?php endif; ?>
            </p>
            <p><a href="order.php">Order online</a> · <a href="booking.php">Book a table</a></p>
            <?php if (!empty($wholesale['enabled'])): ?>
                <p><a href="wholesale.php">Wholesale for cafés &amp; shops</a></p>
            <?php endif; ?>
        </div>

        <?php if (!empty($site['hours'])): ?>
            <div class="footer-col">
                <p class="footer-heading">Opening hours</p>
                <dl class="hours">
                    <?php foreach ($site['hours'] as $days => $time): ?>
                        <div><dt><?= e($days) ?></dt><dd><?= e($time) ?></dd></div>
                    <?php endforeach; ?>
                </dl>
            </div>
        <?php endif; ?>
    </div>
    <p class="footer-legal">
        &copy; <?= date('Y') ?> <?= e($site['name']) ?>
        · <a href="privacy.php">Privacy</a>
        · <a href="menu.php#allergens">Allergens</a>
        · <button type="button" class="link-button" data-cookie-settings>Cookie settings</button>
    </p>
</footer>

<?php
require_once __DIR__ . '/cookie-banner.php';
render_cookie_banner();
?>

<script src="<?= e(asset('js/main.js')) ?>" defer></script>
</body>
</html>
