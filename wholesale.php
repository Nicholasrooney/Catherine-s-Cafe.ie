<?php
/*
 * Wholesale: the bakery supplying cafés, shops, restaurants and offices.
 *
 * One enquiry form, stored with the catering enquiries (source 'wholesale').
 * Trade prices, minimums and delivery days are deliberately NOT on this page:
 * they go to each business directly, once Catherine has set them.
 */
require_once __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/wholesale-handler.php';
require __DIR__ . '/includes/reviews-data.php';

$pageTitle       = "Wholesale Cakes & Pastries for Cafés and Shops, Dublin | Catherine's Bakery";
$pageDescription = "Fresh cream cakes, slices, doughnuts and scones baked every morning on Meath Street, Dublin 8, for cafés, coffee shops, delis and offices. Ask for our trade prices.";
$pageKeywords    = 'wholesale cakes Dublin, cake supplier for cafes Dublin, wholesale bakery Dublin 8, cream cakes wholesale Dublin, pastry supplier coffee shop Dublin';
$activePage      = 'wholesale';
$pageImage       = 'images/eclairs-and-cream-slices-selection.jpg';

require_once __DIR__ . '/includes/seo.php';

$ws_faq = [
    'What can you supply?' =>
        'The same fresh cream cakes, traditional slices, doughnuts, scones, buns and breads we sell in our own bakery on Meath Street, baked every morning.',
    'How much does it cost?' =>
        'We send trade prices to each business directly. Fill in the form and tell us roughly what you need.',
    'Do you deliver?' =>
        'Tell us where you are in the form and we will let you know whether we can deliver to you or whether it is a collection from Meath Street.',
    'Is there a minimum order?' =>
        'Tell us roughly what you need and how often, and we will tell you what works.',
    'What about allergens?' =>
        'We give you the allergen information for every product we supply, so you can tell your own customers.',
];

$pageSchema = [
    [
        '@type'       => 'Service',
        '@id'         => seo_url('wholesale.php') . '#wholesale',
        'name'        => 'Wholesale cakes and pastries',
        'serviceType' => 'Wholesale bakery',
        'description' => "Fresh cream cakes, slices, doughnuts and scones from Catherine's Bakery, Meath Street, Dublin 8, supplied to cafés, shops, restaurants and offices.",
        'provider'    => ['@id' => seo_base_url() . '/#restaurant'],
        'areaServed'  => ['Dublin 8', 'The Liberties', 'Dublin'],
        'audience'    => ['@type' => 'BusinessAudience', 'audienceType' => 'Cafés, coffee shops, delis, restaurants and offices'],
    ],
    seo_faq($ws_faq),
    seo_breadcrumbs(['Wholesale' => 'wholesale.php']),
];

require __DIR__ . '/includes/header.php';

function ws_field_error(array $errors, string $field): string
{
    return isset($errors[$field])
        ? '<p class="field-error" id="' . e($field) . '-error">' . e($errors[$field]) . '</p>'
        : '';
}
function ws_error_attrs(array $errors, string $field): string
{
    return isset($errors[$field]) ? ' aria-invalid="true" aria-describedby="' . e($field) . '-error"' : '';
}
?>

<section class="catering-hero wholesale-hero" aria-labelledby="wholesale-title">
    <div class="container catering-hero-grid">
        <div class="catering-copy">
            <span class="catering-eyebrow">Wholesale · Catherine's Bakery · Meath Street, Dublin 8</span>
            <h1 id="wholesale-title">Fresh cream cakes and bakes for your café or shop.</h1>
            <p class="catering-lede">We bake every morning on Meath Street. If you run a café, coffee shop, deli,
                restaurant or office canteen, we can supply you with the same cream cakes, slices, doughnuts and
                scones we sell in our own bakery.</p>
            <ul class="catering-proof">
                <li><strong>Baked</strong> fresh every morning</li>
                <?php if (!empty($reviewSummary)): ?>
                    <li><strong><?= e($reviewSummary['rating']) ?> ★</strong> from <?= number_format($reviewSummary['count']) ?> Google reviews</li>
                <?php endif; ?>
                <li><strong>Meath Street</strong>, Dublin 8</li>
            </ul>
            <div class="hero-actions">
                <a class="btn btn-light" href="#enquire" data-track="click_wholesale" data-track-label="hero">Ask for trade prices</a>
                <?php if ($site['phone']): ?>
                    <a class="btn btn-outline-light" href="tel:<?= e(preg_replace('/[^0-9+]/', '', $site['phone'])) ?>">Call <?= e($site['phone']) ?></a>
                <?php endif; ?>
            </div>
        </div>
        <img class="wholesale-hero-photo" src="images/eclairs-and-cream-slices-selection.jpg"
             alt="A tray of eclairs, cream slices and pastries from Catherine's Bakery" width="1400" height="815">
    </div>
</section>

<section class="section" aria-labelledby="range-title">
    <div class="container">
        <div class="section-head">
            <h2 id="range-title">What we can supply</h2>
            <p class="section-sub">Baked in our own bakery on Meath Street every morning.</p>
        </div>
        <ul class="wholesale-range">
            <?php foreach ($wholesale['range'] as $item): ?>
                <li class="wholesale-item">
                    <img src="images/<?= e($item['photo']) ?>" alt="<?= e($item['name']) ?> from Catherine's Bakery" loading="lazy">
                    <h3><?= e($item['name']) ?></h3>
                    <p><?= e($item['desc']) ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>

<section class="section catering-steps-section" aria-labelledby="how-title">
    <div class="container">
        <h2 id="how-title">How it works</h2>
        <ol class="catering-steps">
            <li>
                <span class="catering-step-num">1</span>
                <h3>Tell us about your business</h3>
                <p>Fill in the short form below: what you'd like and roughly how often.</p>
            </li>
            <li>
                <span class="catering-step-num">2</span>
                <h3>We send you our trade prices</h3>
                <p>With the allergen information and how ordering works.</p>
            </li>
            <li>
                <span class="catering-step-num">3</span>
                <h3>You choose what to stock</h3>
                <p>Order what sells for you, and change it as you go.</p>
            </li>
        </ol>
    </div>
</section>

<section class="section catering-enquiry-section" id="enquire" aria-labelledby="enquire-title">
    <div class="container catering-enquiry">
        <div class="catering-enquiry-side">
            <h2 id="enquire-title">Ask for our trade prices</h2>
            <p>Takes a minute. We read every enquiry ourselves and come back to you directly.</p>
            <ul class="catering-next">
                <li>Trade prices sent to you, not published</li>
                <li>Allergen information for every product</li>
                <li>No commitment until you order</li>
            </ul>
            <img class="catering-side-photo" src="images/interior-bakery-counter.jpg" alt="The bakery counter full of bread, buns and tarts" loading="lazy">
        </div>

        <div class="catering-enquiry-main">
            <?php if ($ws_sent): ?>
                <div class="confirmation" role="status"
                     data-track-view="wholesale_enquiry_submitted"
                     data-type="<?= e($ws_sent['business_type'] ?? '') ?>">
                    <h2>Enquiry sent</h2>
                    <p>Thanks. We have your details and will be in touch with our trade prices.
                        A copy is on its way to your email.</p>
                    <a class="btn btn-green" href="menu.php#bakery">See the bakery menu</a>
                </div>
            <?php else: ?>

                <?php if (isset($ws_errors['form'])): ?>
                    <div class="form-alert" role="alert"><?= e($ws_errors['form']) ?></div>
                <?php elseif ($ws_errors): ?>
                    <div class="form-alert" role="alert">Some details need fixing. Check the highlighted fields below.</div>
                <?php endif; ?>

                <form class="booking-form wholesale-form" method="post" action="wholesale.php#enquire" novalidate>
                    <input type="hidden" name="token" value="<?= e($_SESSION['wholesale_token']) ?>">
                    <div class="hp" aria-hidden="true">
                        <label for="website">Leave this empty</label>
                        <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    <fieldset>
                        <legend>Your business</legend>
                        <div class="field-row">
                            <div class="field">
                                <label for="business">Business name</label>
                                <input type="text" id="business" name="business" required autocomplete="organization" maxlength="120"
                                       value="<?= e($ws_old['business']) ?>"<?= ws_error_attrs($ws_errors, 'business') ?>>
                                <?= ws_field_error($ws_errors, 'business') ?>
                            </div>
                            <div class="field">
                                <label for="business_type">Kind of business</label>
                                <select id="business_type" name="business_type" required<?= ws_error_attrs($ws_errors, 'business_type') ?>>
                                    <option value="">Choose one</option>
                                    <?php foreach ($wholesale['business_types'] as $key => $label): ?>
                                        <option value="<?= e($key) ?>" <?= $ws_old['business_type'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?= ws_field_error($ws_errors, 'business_type') ?>
                            </div>
                        </div>
                        <div class="field">
                            <label for="location">Where are you?</label>
                            <input type="text" id="location" name="location" required maxlength="200"
                                   placeholder="Area or street, e.g. Thomas Street, Dublin 8"
                                   value="<?= e($ws_old['location']) ?>"<?= ws_error_attrs($ws_errors, 'location') ?>>
                            <?= ws_field_error($ws_errors, 'location') ?>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>What you'd like</legend>
                        <div class="field">
                            <span class="field-label">Interested in <span class="optional">(tick any)</span></span>
                            <div class="choice-row">
                                <?php foreach ($wholesale['range'] as $key => $item): ?>
                                    <label class="choice">
                                        <input type="checkbox" name="products[]" value="<?= e($key) ?>" <?= in_array($key, $ws_products, true) ? 'checked' : '' ?>>
                                        <span><?= e($item['name']) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="field">
                            <label for="frequency">How often? <span class="optional">(roughly)</span></label>
                            <select id="frequency" name="frequency">
                                <option value="">Choose one</option>
                                <?php foreach ($wholesale['frequency'] as $key => $label): ?>
                                    <option value="<?= e($key) ?>" <?= $ws_old['frequency'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field consent">
                            <label class="consent-label" for="samples">
                                <input type="checkbox" id="samples" name="samples" value="1" <?= $ws_samples ? 'checked' : '' ?>>
                                <span>I'd like to try some samples first.</span>
                            </label>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>Your details</legend>
                        <div class="field">
                            <label for="name">Your name</label>
                            <input type="text" id="name" name="name" required autocomplete="name" maxlength="80"
                                   value="<?= e($ws_old['name']) ?>"<?= ws_error_attrs($ws_errors, 'name') ?>>
                            <?= ws_field_error($ws_errors, 'name') ?>
                        </div>
                        <div class="field-row">
                            <div class="field">
                                <label for="phone">Phone</label>
                                <input type="tel" id="phone" name="phone" required autocomplete="tel" maxlength="20"
                                       value="<?= e($ws_old['phone']) ?>"<?= ws_error_attrs($ws_errors, 'phone') ?>>
                                <?= ws_field_error($ws_errors, 'phone') ?>
                            </div>
                            <div class="field">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" required autocomplete="email" maxlength="120"
                                       value="<?= e($ws_old['email']) ?>"<?= ws_error_attrs($ws_errors, 'email') ?>>
                                <?= ws_field_error($ws_errors, 'email') ?>
                            </div>
                        </div>
                        <div class="field">
                            <label for="notes">Anything else? <span class="optional">(optional)</span></label>
                            <textarea id="notes" name="notes" rows="4" maxlength="1000"
                                      placeholder="Roughly how many a day or week, delivery times, anything you're looking for"<?= ws_error_attrs($ws_errors, 'notes') ?>><?= e($ws_old['notes']) ?></textarea>
                            <?= ws_field_error($ws_errors, 'notes') ?>
                        </div>
                    </fieldset>

                    <p class="privacy-note">We use your details to answer your enquiry. See our <a href="privacy.php">privacy notice</a>.</p>
                    <button class="btn btn-green btn-block" type="submit">Send my enquiry</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="section catering-faq-section" aria-labelledby="faq-title">
    <div class="container catering-faq">
        <h2 id="faq-title">Wholesale questions</h2>
        <?php foreach ($ws_faq as $q => $a): ?>
            <details class="faq-item">
                <summary><?= e($q) ?></summary>
                <p><?= e($a) ?></p>
            </details>
        <?php endforeach; ?>
    </div>
</section>

<section class="section cta-band">
    <div class="container cta-inner">
        <h2>Run a café or shop?</h2>
        <a class="btn btn-light" href="#enquire">Ask for trade prices</a>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
