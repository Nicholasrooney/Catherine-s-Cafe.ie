<?php
/*
 * Tests for the website stats behind staff/stats.php: what is accepted, what
 * is thrown away, how visits are attributed, and the ROI sums.
 *
 *     php db/test-stats.php
 *
 * SQLite in memory, like the other suites: no database, no network.
 */

require_once __DIR__ . '/test-helpers.php';
require_once __DIR__ . '/../includes/stats.php';

$phone   = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148 Safari/604.1';
$desktop = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/130.0 Safari/537.36';

// ---------------------------------------------------------------- sources

section('Where a visit came from');

check('no referrer is direct',          stats_source(''), 'direct');
check('Google search',                  stats_source('https://www.google.com/'), 'google');
check('Google in Ireland',              stats_source('https://www.google.ie/search?q=cafe'), 'google');
check('Google Maps',                    stats_source('https://maps.google.com/'), 'google_maps');
check('Instagram app links',            stats_source('https://l.instagram.com/?u=x'), 'instagram');
check('Facebook mobile',                stats_source('https://m.facebook.com/'), 'facebook');
check('Tripadvisor',                    stats_source('https://www.tripadvisor.ie/Restaurant'), 'tripadvisor');
check('somewhere else',                 stats_source('https://example.org/page'), 'other');
check('our own pages are internal',     stats_source('https://catherinescafe.ie/menu.php', 'catherinescafe.ie'), 'internal');
check('www. on our own host too',       stats_source('https://www.catherinescafe.ie/', 'catherinescafe.ie'), 'internal');
check('a utm_source wins',              stats_source('https://www.google.com/', '', 'Flyer'), 'flyer');
check('a utm_source is tidied',         stats_source('', '', 'qr code!'), 'qrcode');

// ---------------------------------------------------------------- cleaning

section('What is accepted');

$e = stats_clean_event(['event' => 'click_order', 'label' => 'justeat', 'location' => 'order-band', 'page' => '/index.php?x=1'], $phone);
check('a known event is kept',          $e['event'], 'click_order');
check('the platform label is kept',     $e['label'], 'justeat');
check('the query string is dropped',    $e['page'], '/index.php');
check('a phone is a phone',             $e['device'], 'mobile');
check('only page views carry a source', $e['source'], '');

check('an unknown event is refused',    stats_clean_event(['event' => 'drop_table'], $phone), null);
check('a crawler is refused',           stats_clean_event(['event' => 'page_view'], 'Googlebot/2.1 (+http://www.google.com/bot.html)'), null);
check('no user agent is refused',       stats_clean_event(['event' => 'page_view'], ''), null);
check('a desktop is a desktop',         stats_clean_event(['event' => 'page_view'], $desktop)['device'], 'desktop');

$long = stats_clean_event(['event' => 'click_menu', 'label' => str_repeat('a', 300) . '<script>'], $desktop);
check('labels are cut to length',       mb_strlen($long['label']), 80);
check('markup is stripped from labels', strpos($long['label'], '<'), false);

$pv = stats_clean_event(['event' => 'page_view', 'ref' => 'https://www.instagram.com/'], $phone, 'catherinescafe.ie');
check('a page view carries its source', $pv['source'], 'instagram');

// ---------------------------------------------------------------- visitors

section('Daily visitors, with nothing personal kept');

fresh_database();
settings_forget();

$a1 = stats_visitor_key('203.0.113.7', $phone, '2026-11-02');
$a2 = stats_visitor_key('203.0.113.7', $phone, '2026-11-02');
$b1 = stats_visitor_key('203.0.113.8', $phone, '2026-11-02');
check('same person, same day, same key', $a1, $a2);
check('another person, another key',     $a1 !== $b1, true);
check('the key is not the IP address',   strpos($a1, '203.0') === false, true);

settings_forget();
$a3 = stats_visitor_key('203.0.113.7', $phone, '2026-11-03');
check('next day the salt changes, so the key does too', $a1 !== $a3, true);

// ---------------------------------------------------------------- totals

section('Counting it back up');

fresh_database();
settings_forget();
$record = function (string $event, string $label = '', string $visitor = '', string $day = '2026-11-02', string $source = '') {
    db()->prepare('INSERT INTO stats_events (created_at, day, event, label, location, page, source, device, visitor) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)')
        ->execute(["$day 10:00:00", $day, $event, $label, 'hero', '/', $source, 'mobile', $visitor]);
};
$record('page_view', '', 'v1', '2026-11-02', 'google');
$record('page_view', '', 'v1', '2026-11-02', 'google');   // same visitor, same day
$record('page_view', '', 'v2', '2026-11-02', 'instagram');
$record('page_view', '', 'v1', '2026-11-03', 'direct');   // back the next day
$record('click_directions', 'google_maps');
$record('click_directions', 'apple_maps');
$record('click_order', 'flipdish');
$record('click_order', 'flipdish');
$record('click_order', 'justeat');
$record('click_phone', '', '', '2026-10-01');             // outside the period

$t = stats_totals('2026-11-01', '2026-11-30');
check('page views counted',               $t['page_view'], 4);
check('a visitor counts once a day',      $t['visitors'], 3);
check('directions counted',               $t['click_directions'], 2);
check('orders counted',                   $t['click_order'], 3);
check('outside the period is left out',   $t['click_phone'], 0);

check('orders split by platform', stats_breakdown('2026-11-01', '2026-11-30', 'click_order', 'label'),
    ['flipdish' => 2, 'justeat' => 1]);
check('sources split, biggest first', array_keys(stats_breakdown('2026-11-01', '2026-11-30', 'page_view', 'source')),
    ['google', 'instagram', 'direct']);
check('an unknown column is refused', stats_breakdown('2026-11-01', '2026-11-30', 'click_order', 'visitor; DROP'), []);

$daily = stats_daily('2026-11-01', '2026-11-03', ['click_order']);
check('one row per day, gaps included', array_keys($daily), ['2026-11-01', '2026-11-02', '2026-11-03']);
check('a quiet day is zero',            $daily['2026-11-01']['click_order'], 0);
check('visitors per day',               $daily['2026-11-02']['visitors'], 2);

// ---------------------------------------------------------------- bookings

section('Bookings and enquiries come from their own tables');

$now = '2026-11-02 12:00:00';
db()->exec("INSERT INTO customers (name, phone, phone_key, email, created_at, updated_at) VALUES ('Test', '0871234567', '871234567', 't@example.com', '$now', '$now')");
foreach ([[2, 'website', 'confirmed'], [4, 'website', 'requested'], [3, 'website', 'cancelled'], [6, 'staff', 'confirmed']] as [$g, $src, $st]) {
    db()->exec("INSERT INTO bookings (customer_id, booking_date, booking_time, guests, seating, status, source, created_at, updated_at)
                VALUES (1, '2026-11-09', '10:00', $g, 'Inside', '$st', '$src', '$now', '$now')");
}
$b = stats_bookings('2026-11-01', '2026-11-30');
check('website bookings, cancelled left out', $b['bookings'], 2);
check('their guests',                         $b['guests'], 6);

require_once __DIR__ . '/../includes/catering.php';
catering_save(['source' => 'full_form', 'name' => 'A', 'guests' => 20, 'package' => 'breakfast_rolls', 'estimate_total' => 160]);
$c = stats_enquiries('2000-01-01', '2100-01-01');
check('enquiries counted', $c['enquiries'], 1);

// ---------------------------------------------------------------- money

section('The ROI estimate');

$a = ['avg_spend_per_head' => 10.0, 'avg_order_value' => 20.0, 'order_tap_conversion' => 50,
      'directions_visit' => 50, 'call_conversion' => 50, 'monthly_cost' => 30.0];
$roi = stats_roi(
    array_merge($t, ['click_order' => 10, 'click_directions' => 10, 'click_phone' => 2]),
    ['bookings' => 2, 'guests' => 6], ['enquiries' => 1, 'estimate' => 100], $a, 30
);
check('bookings: 6 guests × €10',             $roi['lines']['bookings']['value'], 60.0);
check('orders: 10 taps × 50% × €20',          $roi['lines']['orders']['value'], 100.0);
check('directions: 10 taps × 50% × €10',      $roi['lines']['directions']['value'], 50.0);
check('calls: 2 × 50% × €20',                 $roi['lines']['calls']['value'], 20.0);
check('total value',                          $roi['value'], 330.0);
check('a month of cost',                      $roi['cost'], 30.0);
check('return per €1',                        $roi['roi'], 11.0);
check('no cost means no ratio, not infinity', stats_roi($t, $b, $c, array_merge($a, ['monthly_cost' => 0.0]), 30)['roi'], null);

section('Monthly figures typed in by hand');

check('a month saves',                 stats_monthly_set('2026-10', ['flipdish_orders' => '120', 'flipdish_sales' => '2,640', 'nonsense' => '5']), true);
$m = stats_monthly_get('2026-10');
check('a number reads back',           $m['flipdish_orders'], 120.0);
check('unknown metrics are ignored',   isset($m['nonsense']), false);
check('€ signs and thousands commas are understood', $m['flipdish_sales'], 2640.0);
stats_monthly_set('2026-10', ['flipdish_orders' => '']);
check('blanking a value removes it',   isset(stats_monthly_get('2026-10')['flipdish_orders']), false);
check('a bad month is refused',        stats_monthly_set('Oct', ['flipdish_orders' => '1']), false);

section('Every stats event the browser sends is one the server accepts');

$js = (string) file_get_contents(__DIR__ . '/../js/main.js');
preg_match_all("/name: '([a-z_]+)'/", $js, $m);
$server = array_keys(stats_events());
$ignored = ['click_privacy', 'click_order_page'];   // GA only, not worth a dashboard line
check('no browser event is silently dropped', array_values(array_diff(array_unique($m[1]), $server, $ignored)), []);

finish();
