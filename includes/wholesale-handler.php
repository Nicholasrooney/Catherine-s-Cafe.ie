<?php
/*
 * Wholesale page form.
 *
 * Turns an untrusted POST into either errors or one stored, emailed enquiry.
 * Like catering, the enquiry is saved first and the café emailed second; only
 * if BOTH fail is the visitor told to try another way.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/wholesale.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure'   => !empty($_SERVER['HTTPS']),
        'samesite' => 'Lax',
    ]);
    session_start();
}

if (empty($_SESSION['wholesale_token'])) {
    $_SESSION['wholesale_token'] = bin2hex(random_bytes(16));
}

$ws_errors = [];
$ws_old = [
    'business' => '', 'business_type' => '', 'location' => '', 'frequency' => '',
    'name' => '', 'phone' => '', 'email' => '', 'notes' => '',
];
$ws_products = [];
$ws_samples  = false;

$ws_sent = isset($_GET['sent']) ? ($_SESSION['wholesale_sent'] ?? null) : null;
unset($_SESSION['wholesale_sent']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($ws_old as $field => $_) {
        $ws_old[$field] = trim((string) ($_POST[$field] ?? ''));
    }
    $ws_products = array_values(array_intersect(array_keys($wholesale['range']), (array) ($_POST['products'] ?? [])));
    $ws_samples  = !empty($_POST['samples']);

    $len = fn(string $v): int => mb_strlen($v, 'UTF-8');

    if (!hash_equals($_SESSION['wholesale_token'], (string) ($_POST['token'] ?? ''))) {
        $ws_errors['form'] = 'This form expired. Please check your details and send it again.';
    }

    if ($len($ws_old['business']) < 2 || $len($ws_old['business']) > 120) {
        $ws_errors['business'] = 'Enter the name of your business.';
    }
    if (!isset($wholesale['business_types'][$ws_old['business_type']])) {
        $ws_errors['business_type'] = 'Choose the kind of business.';
    }
    if ($len($ws_old['location']) < 2 || $len($ws_old['location']) > 200) {
        $ws_errors['location'] = 'Tell us where you are, like "Dublin 8" or a street.';
    }
    if ($ws_old['frequency'] !== '' && !isset($wholesale['frequency'][$ws_old['frequency']])) {
        $ws_old['frequency'] = '';
    }
    if ($len($ws_old['name']) < 2 || $len($ws_old['name']) > 80) {
        $ws_errors['name'] = 'Enter your name.';
    }
    $digits = preg_replace('/\D/', '', $ws_old['phone']);
    if (strlen($digits) < 7 || strlen($digits) > 15) {
        $ws_errors['phone'] = 'Enter a phone number we can call you on.';
    }
    if (!filter_var($ws_old['email'], FILTER_VALIDATE_EMAIL)) {
        $ws_errors['email'] = 'Enter an email address, like name@example.com.';
    }
    if ($len($ws_old['notes']) > 1000) {
        $ws_errors['notes'] = 'Keep notes under 1000 characters.';
    }

    if (!$ws_errors && empty($_POST['website'])) {
        $record = [
            'source'        => 'wholesale',
            'occasion'      => 'wholesale',
            'guests'        => 0,
            'name'          => $ws_old['name'],
            'phone'         => $ws_old['phone'],
            'email'         => $ws_old['email'],
            'business'      => $ws_old['business'],
            'business_type' => $ws_old['business_type'],
            'location'      => $ws_old['location'],
            'products'      => implode(',', $ws_products),
            'frequency'     => $ws_old['frequency'],
            'samples'       => $ws_samples,
            'notes'         => trim(($ws_samples ? "Would like to try samples first.\n" : '') . $ws_old['notes']),
        ];

        $saved = false;
        try {
            catering_save($record);
            $saved = true;
        } catch (Throwable $e) {
            error_log('Wholesale enquiry not saved: ' . $e->getMessage());
        }

        $alerted = wholesale_mail_alert($record);

        if (!$saved && !$alerted) {
            $ws_errors['form'] = 'Sorry, your enquiry could not be sent just now. '
                . 'Please ring us on ' . $site['phone'] . ' and we will take it from there.';
        } else {
            wholesale_mail_confirmation($record);
        }
    }

    if (!$ws_errors) {
        $_SESSION['wholesale_sent']  = ['business_type' => $ws_old['business_type']];
        $_SESSION['wholesale_token'] = bin2hex(random_bytes(16));
        header('Location: wholesale.php?sent=1#enquire', true, 303);
        exit;
    }
}
