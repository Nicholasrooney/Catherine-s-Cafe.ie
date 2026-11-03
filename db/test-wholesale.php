<?php
/*
 * Tests for wholesale enquiries: how they are stored beside the catering
 * ones, how an older live table grows the new columns, how they read back,
 * and how they show up (separately) in the stats.
 *
 *     php db/test-wholesale.php
 *
 * SQLite in memory, like the other suites: no database, no network.
 */

require_once __DIR__ . '/test-helpers.php';
require_once __DIR__ . '/../includes/wholesale.php';
require_once __DIR__ . '/../includes/stats.php';

$trade = [
    'source' => 'wholesale', 'occasion' => 'wholesale', 'guests' => 0,
    'name' => 'Aoife Byrne', 'phone' => '0871234567', 'email' => 'aoife@example.com',
    'business' => 'Thomas St Coffee', 'business_type' => 'cafe', 'location' => 'Thomas Street, Dublin 8',
    'products' => 'cream_cakes,doughnuts', 'frequency' => 'daily',
    'notes' => "Would like to try samples first.\nAbout 30 a day.",
];

section('The config makes sense');

check('every range item has a photo on disk', array_values(array_filter(
    $wholesale['range'], fn($i) => !is_file(__DIR__ . '/../images/' . $i['photo'])
)), []);
check('there is an "other" business type', isset($wholesale['business_types']['other']), true);

section('Labels read like people talk');

check('business type',          wholesale_type_label('cafe'), 'Café or coffee shop');
check('frequency',              wholesale_frequency_label('few_weekly'), 'A few times a week');
check('products, in order',     wholesale_products_label('cream_cakes,doughnuts'), 'Fresh cream cakes, Doughnuts');
check('an unknown key survives', wholesale_type_label('food_truck'), 'Food truck');

$lines = wholesale_summary_lines($trade);
check('summary leads with the business', $lines[0], 'Business: Thomas St Coffee');
check('summary says how often',           in_array('How often: Every day', $lines, true), true);

section('Stored beside catering, listed apart');

fresh_database();
$id = catering_save($trade);
check('a wholesale enquiry saves', $id > 0, true);
$row = catering_get($id);
check('the business name is kept',  $row['business'], 'Thomas St Coffee');
check('the products are kept',      $row['products'], 'cream_cakes,doughnuts');
check('it starts as new',           $row['status'], 'new');

catering_save(['source' => 'full_form', 'name' => 'Party', 'guests' => 20, 'occasion' => 'communion', 'estimate_total' => 300]);
check('everything lists both',       count(catering_list(null, 200)), 2);
check('wholesale lists one',         array_column(catering_list(null, 200, 'wholesale'), 'business'), ['Thomas St Coffee']);
check('catering leaves wholesale out', array_column(catering_list(null, 200, 'catering'), 'name'), ['Party']);

section('An older live table grows the new columns by itself');

$pdo = fresh_database();
$pdo->exec('DROP TABLE catering_enquiries');
$pdo->exec("CREATE TABLE catering_enquiries (
    id INTEGER PRIMARY KEY AUTOINCREMENT, source TEXT NOT NULL DEFAULT 'full_form',
    name TEXT NOT NULL DEFAULT '', phone TEXT NOT NULL DEFAULT '', email TEXT NOT NULL DEFAULT '',
    occasion TEXT NOT NULL DEFAULT '', event_date TEXT NULL, guests INTEGER NOT NULL,
    package TEXT NOT NULL DEFAULT '', price_per_person REAL NULL, estimate_total INTEGER NULL,
    fulfilment TEXT NOT NULL DEFAULT '', delivery_address TEXT NULL, notes TEXT NULL,
    marketing_consent INTEGER NOT NULL DEFAULT 0, status TEXT NOT NULL DEFAULT 'new',
    staff_notes TEXT NULL, created_at TEXT NOT NULL, updated_at TEXT NOT NULL)");
$id = catering_save($trade);
check('saves into a table missing five columns', catering_get($id)['frequency'] ?? null, 'daily');

section('The stats count wholesale on its own line');

fresh_database();
catering_save($trade);
$won = catering_save($trade);
catering_set_status($won, 'booked');
catering_save(['source' => 'full_form', 'name' => 'Party', 'guests' => 20, 'occasion' => 'communion', 'estimate_total' => 300]);

$w = stats_wholesale('2000-01-01', '2100-01-01');
check('wholesale enquiries counted',  $w['enquiries'], 2);
check('the won one is counted',       $w['won'], 1);
$c = stats_enquiries('2000-01-01', '2100-01-01');
check('catering leaves wholesale out', $c['enquiries'], 1);
check('and its quoted value too',     $c['estimate'], 300);

$a = ['avg_spend_per_head' => 10.0, 'avg_order_value' => 20.0, 'order_tap_conversion' => 50,
      'directions_visit' => 50, 'call_conversion' => 50, 'monthly_cost' => 0.0, 'wholesale_account' => 600.0];
$t = ['click_order' => 0, 'click_directions' => 0, 'click_phone' => 0];
$b = ['bookings' => 0, 'guests' => 0];
check('a won account is worth a month of trade', stats_roi($t, $b, $c, $a, 30, $w)['lines']['wholesale']['value'], 600.0);
check('no account value set means €0, not a guess',
    stats_roi($t, $b, $c, array_merge($a, ['wholesale_account' => 0.0]), 30, $w)['lines']['wholesale']['value'], 0.0);

section('The page is linked everywhere a business owner would look');

$header = (string) file_get_contents(__DIR__ . '/../includes/header.php');
$footer = (string) file_get_contents(__DIR__ . '/../includes/footer.php');
$home   = (string) file_get_contents(__DIR__ . '/../index.php');
$map    = (string) file_get_contents(__DIR__ . '/../sitemap.php');
check('in the main menu',  str_contains($header, "'wholesale.php'"), true);
check('in the footer',     str_contains($footer, 'wholesale.php'), true);
check('on the home page',  str_contains($home, 'wholesale.php'), true);
check('in the sitemap',    str_contains($map, "'wholesale.php'"), true);

finish();
