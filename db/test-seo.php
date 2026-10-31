<?php
/*
 * Tests for the SEO helpers — mainly the opening-hours parser, which turns the
 * free text in config.php into schema.org specifications. Wrong hours in a
 * Google result send people to a locked door, so this one matters.
 *
 *     php db/test-seo.php
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/seo.php';

$passed = $failed = 0;

function check(string $label, $actual, $expected): void
{
    global $passed, $failed;
    if ($actual === $expected) {
        echo "  PASS  $label\n";
        $passed++;
    } else {
        echo "  FAIL  $label\n";
        echo "          expected: " . json_encode($expected) . "\n";
        echo "          actual:   " . json_encode($actual) . "\n";
        $failed++;
    }
}

echo "SEO helpers\n" . str_repeat('=', 64) . "\n\nOpening hours parsing\n---------------------\n";

$r = seo_opening_hours(['Mon to Fri' => '9:00 to 16:00']);
check('weekday range expands to five days', count($r[0]['dayOfWeek']), 5);
check('first day is Monday',   $r[0]['dayOfWeek'][0], 'Monday');
check('last day is Friday',    $r[0]['dayOfWeek'][4], 'Friday');
check('open time zero-padded', $r[0]['opens'],  '09:00');
check('close time kept',       $r[0]['closes'], '16:00');

$r = seo_opening_hours(['Saturday' => '09:00 - 17:00']);
check('single day',            $r[0]['dayOfWeek'], ['Saturday']);
check('dash separator works',  $r[0]['closes'], '17:00');

$r = seo_opening_hours(['Sat and Sun' => '10.00 to 18.00']);
check('two listed days',       $r[0]['dayOfWeek'], ['Saturday', 'Sunday']);
check('dots in time handled',  $r[0]['opens'], '10:00');

$r = seo_opening_hours(['Sat to Mon' => '10:00 to 16:00']);
check('range wrapping the week', $r[0]['dayOfWeek'], ['Saturday', 'Sunday', 'Monday']);

$r = seo_opening_hours(['Mon to Fri' => '9:00 to 16:00', 'Sunday' => '11:00 to 15:00']);
check('several rules kept separate', count($r), 2);

check('unparseable text is skipped, not guessed', seo_opening_hours(['Mon' => 'all day']), []);
check('no hours means no claim',                  seo_opening_hours([]), []);

echo "\nURLs and schema\n---------------\n";

check('home canonical has no index.php', seo_url('index.php'), 'https://' . $site['domain'] . '/');
check('inner page url',                  seo_url('menu.php'),  'https://' . $site['domain'] . '/menu.php');

// The hours checks run against a fixed test week, not the café's real hours,
// so changing the opening times in config.php never turns this suite red.
// Closed Monday, 9 to 4 midweek, 9 to 5 at the weekend.
$realHours = $GLOBALS['booking']['service_hours'];
$realTimes = $GLOBALS['booking']['times'];
$GLOBALS['booking']['service_hours'] = [
    'mon' => null,
    'tue' => ['09:00', '16:00'], 'wed' => ['09:00', '16:00'],
    'thu' => ['09:00', '16:00'], 'fri' => ['09:00', '16:00'],
    'sat' => ['09:00', '17:00'], 'sun' => ['09:00', '17:00'],
];
$GLOBALS['booking']['times'] = hours_all_slots($GLOBALS['booking']['service_hours']);

$node = seo_restaurant_node();
check('typed as both Restaurant and café', $node['@type'], ['Restaurant', 'CafeOrCoffeeShop']);
check('locality is set',                   $node['address']['addressLocality'] !== '', true);
check('eircode carried through',           $node['address']['postalCode'], $site['eircode']);
check('opening hours published', count($node['openingHoursSpecification']), 7);

$byDay = [];
foreach ($node['openingHoursSpecification'] as $spec) {
    $byDay[basename($spec['dayOfWeek'])] = $spec['opens'] . '-' . $spec['closes'];
}
check('Monday published as closed',  $byDay['Monday'],   '00:00-00:00');
check('weekday hours',               $byDay['Wednesday'], '09:00-16:00');
check('weekend hours run later',     $byDay['Saturday'],  '09:00-17:00');

echo "
Opening hours as the single source
----------------------------------
";

check('Monday is closed',          hours_is_open('2026-09-14'), false);
check('Tuesday is open',           hours_is_open('2026-09-15'), true);
check('no slots on a closed day',  hours_slots_for_date('2026-09-14'), []);

$tue = hours_slots_for_date('2026-09-15');
$sat = hours_slots_for_date('2026-09-19');
check('weekday opens at 09:00',    $tue[0], '09:00');
check('weekday last slot is 15:00 (an hour before a 16:00 close)', end($tue), '15:00');
check('weekend last slot is 16:00 (an hour before a 17:00 close)', end($sat), '16:00');
check('weekend has two more slots than a weekday', count($sat) - count($tue), 2);

check('next open day after Monday is Tuesday', hours_next_open_date('2026-09-14'), '2026-09-15');

// Dates taken off the booking form
$savedBlocked = $GLOBALS['booking']['no_bookings_dates'];
$GLOBALS['booking']['no_bookings_dates'] = ['2026-10-03' => '', '2026-10-04' => 'Private function.'];

check('a blocked date gives the standard wording',
    hours_blocked_message('2026-10-03'), 'We are not taking any more bookings that day.');
check('a custom reason is used when given',
    hours_blocked_message('2026-10-04'), 'Private function.');
check('any other date is unaffected',   hours_blocked_message('2026-10-05'), null);
check('a blocked date takes no bookings', hours_takes_bookings('2026-10-03'), false);
check('but the cafe is still open that day', hours_is_open('2026-10-03'), true);
check('an ordinary open day still takes them', hours_takes_bookings('2026-10-06'), true);
check('a closed Monday takes none either',  hours_takes_bookings('2026-10-05'), false);

// Landing on the 3rd should be pointed past the 4th, not into it.
check('the next bookable day skips both blocked days',
    hours_next_open_date('2026-10-03'), '2026-10-06');

$GLOBALS['booking']['no_bookings_dates'] = ['2026-10-03'];
check('a plain list of dates works too',
    hours_blocked_message('2026-10-03'), 'We are not taking any more bookings that day.');

$GLOBALS['booking']['no_bookings_dates'] = $savedBlocked;

// Whatever is configured, check it is usable: real dates, each one actually
// refused. Asserting the dates themselves would mean editing this every time
// a weekend is taken off.
$badDates = $notRefused = [];
foreach ($savedBlocked as $key => $value) {
    $date = is_int($key) ? $value : $key;
    if (!DateTimeImmutable::createFromFormat('!Y-m-d', $date)) {
        $badDates[] = $date;
    }
    if (hours_blocked_message($date) === null) {
        $notRefused[] = $date;
    }
}
check('every blocked date is a real Y-m-d date', $badDates, []);
check('every blocked date is actually refused', $notRefused, []);

$display = hours_display();
check('closed day shown as Closed',  $display['Monday'], 'Closed');
check('consecutive days grouped',    isset($display['Tuesday to Friday']), true);
check('a pair joined with &',        isset($display['Saturday & Sunday']), true);

// The staff form offers every slot the cafe ever runs, so they can take a
// booking outside normal service if they want to.
check('staff slot list spans both schedules', in_array('16:00', $booking['times'], true), true);

$GLOBALS['booking']['service_hours'] = $realHours;
$GLOBALS['booking']['times']         = $realTimes;

require __DIR__ . '/../includes/menu-data.php';
$menuNode = seo_menu_node($menu);
$items = array_sum(array_map(fn($s) => count($s['hasMenuItem']), $menuNode['hasMenuSection']));
check('every menu section in schema', count($menuNode['hasMenuSection']), count($menu));
check('every dish in schema',         $items, array_sum(array_map(fn($s) => count($s['items']), $menu)));
$GLOBALS['site']['show_prices'] = false;
$hidden = seo_menu_node($menu);
check('no prices sent to Google while prices are off', isset($hidden['hasMenuSection'][0]['hasMenuItem'][0]['offers']), false);

$GLOBALS['site']['show_prices'] = true;
$shown = seo_menu_node($menu);
check('prices carried in euro when switched on', $shown['hasMenuSection'][0]['hasMenuItem'][0]['offers']['priceCurrency'], 'EUR');
$GLOBALS['site']['show_prices'] = false;

$crumbs = seo_breadcrumbs(['Menu' => 'menu.php']);
check('breadcrumb starts at home', $crumbs['itemListElement'][0]['name'], 'Home');
check('breadcrumb positions',      $crumbs['itemListElement'][1]['position'], 2);

echo "\n" . str_repeat('=', 64) . "\n";
echo $failed === 0 ? "ALL $passed TESTS PASSED\n" : "$passed passed, $failed FAILED\n";
exit($failed === 0 ? 0 : 1);
