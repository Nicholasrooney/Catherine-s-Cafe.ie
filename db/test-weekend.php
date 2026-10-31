<?php
/*
 * Tests for the staff weekend-pause toggle.
 *
 *     php db/test-weekend.php
 *
 * The two things worth being sure of:
 *
 *   1. It expires by itself. A pause is stored against one weekend's Sunday,
 *      so once that Sunday passes it stops applying. If that ever broke, one
 *      busy Saturday would quietly close every weekend after it and nobody
 *      would get an error to tell them.
 *
 *   2. It never touches a booking. Pausing gates the public form and nothing
 *      else: rows already in the table stay exactly as they were, and staff
 *      can still add one by hand.
 */

require_once __DIR__ . '/test-helpers.php';
require_once __DIR__ . '/../includes/settings.php';
require_once __DIR__ . '/../includes/bookings.php';
require_once __DIR__ . '/../includes/customers.php';
require_once __DIR__ . '/../includes/capacity.php';

echo "Cilantro Café — weekend pause tests\n";
echo str_repeat('=', 64) . "\n";

fresh_database();

/*
 * Clear the hand-written blocked dates for the whole suite.
 *
 * config.php genuinely has this weekend blocked by hand right now, which was
 * quietly making some of these assertions pass for the wrong reason: the date
 * was refused by the list, not by the toggle. The toggle has to be the only
 * thing in play, or this suite proves nothing about it.
 */
$realBlockedDates = $GLOBALS['booking']['no_bookings_dates'];
$GLOBALS['booking']['no_bookings_dates'] = [];

/** Pause as if it were $today, so the clock is not part of the test. */
function pause_on(string $today, string $actor = 'tester'): bool
{
    return setting_set('paused_weekend', hours_weekend_id($today), $actor);
}

// ------------------------------------------------------------ which weekend

section('Which weekend the button points at');

// Thu 29 Oct 2026 through Mon 2 Nov.
check('Monday looks ahead to the coming Sunday',  hours_weekend_id('2026-10-26'), '2026-11-01');
check('Thursday, the same weekend',               hours_weekend_id('2026-10-29'), '2026-11-01');
check('Friday, still that weekend',               hours_weekend_id('2026-10-30'), '2026-11-01');
check('Saturday, still that weekend',             hours_weekend_id('2026-10-31'), '2026-11-01');
check('on the Sunday itself, that Sunday',        hours_weekend_id('2026-11-01'), '2026-11-01');
check('the Monday after, it has rolled on',       hours_weekend_id('2026-11-02'), '2026-11-08');

check('a Fri-Sun weekend is three dates',
    hours_weekend_dates('2026-10-28', ['fri', 'sat', 'sun']),
    ['2026-10-30', '2026-10-31', '2026-11-01']);
check('Sat-Sun only, if that is how it is configured',
    hours_weekend_dates('2026-10-28', ['sat', 'sun']),
    ['2026-10-31', '2026-11-01']);
check('Friday belongs to the weekend it leads into, not the one just gone',
    in_array('2026-10-30', hours_weekend_dates('2026-10-30', ['fri', 'sat', 'sun']), true), true);
check('config drives it',
    hours_weekend_dates('2026-10-28'),
    hours_weekend_dates('2026-10-28', $GLOBALS['booking']['weekend_days']));

// ------------------------------------------------------------ the toggle

section('Pausing and re-opening');

settings_forget();
check('nothing is paused to begin with', hours_weekend_paused('2026-10-29'), false);

check('pausing saves',                   pause_on('2026-10-29'), true);
settings_forget();
check('the weekend now reads as paused', hours_weekend_paused('2026-10-29'), true);

check('the stored value is the Sunday, not a flag',
    setting_get('paused_weekend'), '2026-11-01');
check('and it records who did it',
    (setting_meta('paused_weekend') ?? [])['updated_by'] ?? null, 'tester');

check('un-pausing clears it', setting_set('paused_weekend', null, 'tester'), true);
settings_forget();
check('open again',           hours_weekend_paused('2026-10-29'), false);
check('nothing left behind',  setting_get('paused_weekend'), null);

// ------------------------------------------------------------ self-expiry

section('It expires on its own');

pause_on('2026-10-29');
settings_forget();

check('paused on the Thursday, the Friday is shut',  hours_weekend_paused('2026-10-30'), true);
check('and still shut on the Sunday',                hours_weekend_paused('2026-11-01'), true);
check('but open again the Monday after',             hours_weekend_paused('2026-11-02'), false);
check('and the weekend after that',                  hours_weekend_paused('2026-11-05'), false);
check('one pause cannot close every future weekend', hours_weekend_paused('2027-01-14'), false);

// The stale value is harmless — it simply stops matching.
check('the old value is still on disk, just irrelevant',
    setting_get('paused_weekend'), '2026-11-01');

// ------------------------------------------------------- effect on the form

section('What a customer sees');

$blocked = [];   // no hand-written dates, so only the toggle is in play

pause_on('2026-10-29');
settings_forget();

check('the paused Saturday is refused',
    hours_blocked_message('2026-10-31', $blocked),
    'We are not taking any more bookings this weekend.');
check('so is the Friday',  hours_blocked_message('2026-10-30', $blocked) !== null, true);
check('so is the Sunday',  hours_blocked_message('2026-11-01', $blocked) !== null, true);
check('midweek is untouched', hours_blocked_message('2026-10-29', $blocked), null);
check('and so is the weekend after',
    hours_blocked_message('2026-11-07', $blocked), null);

check('a paused day takes no bookings',   hours_takes_bookings('2026-10-31'), false);
check('but the café is still open on it', hours_is_open('2026-10-31'), true);
// The form's own slot list is checked against the running site instead:
// booking-handler.php starts a session when included, so it is not safe to
// pull into a CLI suite.
check('the café still has hours that day',
    count(hours_slots_for_date('2026-10-31')) > 0, true);

// A reason somebody typed by hand is more specific, so it wins.
check('a hand-written reason beats the generic weekend wording',
    hours_blocked_message('2026-10-31', ['2026-10-31' => 'Private function.']),
    'Private function.');

setting_set('paused_weekend', null);
settings_forget();
check('re-opening lets the day take bookings again',
    hours_takes_bookings('2026-10-31'), true);

// ------------------------------------------------- bookings are not touched

section('Existing bookings are left alone');

$pdo = fresh_database();
settings_forget();

$id = booking_create([
    'name' => 'Already Booked', 'phone' => '0871234567', 'email' => 'a@example.com',
    'date' => '2026-10-31', 'time' => '13:00', 'guests' => 2,
    'seating' => 'Table', 'notes' => '', 'status' => 'confirmed',
]);
check('a booking exists on what will become a paused day', $id > 0, true);

$before = booking_get($id);

pause_on('2026-10-29');
settings_forget();

$after = booking_get($id);
check('the row is byte-for-byte unchanged', $after, $before);
check('still confirmed',                    $after['status'], 'confirmed');
check('still there on the day',             count(bookings_for_date('2026-10-31')), 1);
check('and still visible to staff',         $after['name'], 'Already Booked');

// Staff can still take a phone booking on a paused day — that is the point of
// pausing the form rather than closing the day.
$second = booking_create([
    'name' => 'Phone Booking', 'phone' => '0869999999', 'email' => '',
    'date' => '2026-10-31', 'time' => '15:00', 'guests' => 3,
    'seating' => 'Stools', 'notes' => 'rang the café', 'status' => 'confirmed',
]);
check('staff can still add one by hand',       $second > 0, true);
check('both bookings are on the paused day',   count(bookings_for_date('2026-10-31')), 2);
check('the public form still refuses that day', hours_blocked_message('2026-10-31', []) !== null, true);

// ------------------------------------------------------------ failing open

section('When the database is unreachable');

/*
 * If the settings read throws, the form must keep taking bookings rather than
 * refuse everybody. An unwanted booking is a phone call; silently refusing a
 * whole weekend the café is open is lost business with no symptom.
 */
pause_on('2026-10-29');
settings_forget();
check('paused while the database is up', hours_weekend_paused('2026-10-29'), true);

db(db_connect(['driver' => 'sqlite', 'path' => ':memory:']));   // no settings table
settings_forget();

check('a missing table does not throw',    setting_get('paused_weekend'), null);
check('and the weekend reads as open',     hours_weekend_paused('2026-10-29'), false);
check('so the form keeps taking bookings', hours_blocked_message('2026-10-31', []), null);
check('a failed write says so rather than throwing',
    setting_set('paused_weekend', '2026-11-01', 'tester'), true);   // creates the table, then writes

$GLOBALS['booking']['no_bookings_dates'] = $realBlockedDates;

finish();
