<?php
/*
 * The "Order online" choices: every ordering platform in $ordering
 * (config.php), side by side and equal, so customers pick the one they use.
 *
 * Each card is a plain link to the platform. js/main.js recognises the
 * destination and counts the tap by platform on the stats page.
 */

function order_platforms(): array
{
    global $ordering;
    return array_filter($ordering ?? [], fn($p) => trim((string) ($p['url'] ?? '')) !== '');
}

function render_order_options(string $heading = ''): void
{
    $platforms = order_platforms();
    if (!$platforms) {
        return;
    }
    ?>
    <div class="order-options">
        <?php if ($heading !== ''): ?><h3 class="order-heading"><?= e($heading) ?></h3><?php endif; ?>
        <ul class="order-grid">
            <?php foreach ($platforms as $key => $p): ?>
                <li>
                    <a class="order-card order-<?= e($key) ?>" href="<?= e($p['url']) ?>" target="_blank" rel="noopener">
                        <span class="order-name"><?= e($p['label']) ?></span>
                        <span class="order-sub"><?= e($p['sub']) ?></span>
                        <span class="order-go" aria-hidden="true">Order &rarr;</span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php
}
