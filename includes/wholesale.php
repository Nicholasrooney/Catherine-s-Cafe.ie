<?php
/*
 * Wholesale: the bakery supplying cafés, shops and offices.
 *
 * A wholesale enquiry is stored in catering_enquiries with source 'wholesale',
 * so it shares the staff screen, the statuses (new → quoted → booked, or lost),
 * the nightly retention purge and the alert list. This file holds only what is
 * different: the labels and the two emails.
 */

require_once __DIR__ . '/catering.php';

function wholesale_type_label(string $key): string
{
    global $wholesale;
    return $wholesale['business_types'][$key] ?? ucfirst(str_replace('_', ' ', $key));
}

function wholesale_frequency_label(string $key): string
{
    global $wholesale;
    return $wholesale['frequency'][$key] ?? ucfirst(str_replace('_', ' ', $key));
}

/**
 * The stored "products" value (comma-separated keys) as names people read.
 */
function wholesale_products_label(string $keys): string
{
    global $wholesale;
    $names = [];
    foreach (array_filter(explode(',', $keys)) as $key) {
        $names[] = $wholesale['range'][$key]['name'] ?? $key;
    }
    return implode(', ', $names);
}

/**
 * The details of an enquiry as plain lines, shared by both emails.
 */
function wholesale_summary_lines(array $d): array
{
    $lines = [
        'Business: ' . ($d['business'] ?? ''),
        'Type:     ' . wholesale_type_label($d['business_type'] ?? ''),
        'Area:     ' . ($d['location'] ?? ''),
    ];
    if (($d['products'] ?? '') !== '') {
        $lines[] = 'Interested in: ' . wholesale_products_label($d['products']);
    }
    if (($d['frequency'] ?? '') !== '') {
        $lines[] = 'How often: ' . wholesale_frequency_label($d['frequency']);
    }
    if (!empty($d['notes'])) {
        $lines[] = '';
        $lines[] = 'Notes: ' . $d['notes'];
    }
    return $lines;
}

/**
 * Tells the café a wholesale lead has arrived.
 */
function wholesale_mail_alert(array $d): bool
{
    global $site, $catering;

    $recipients = mail_notify_recipients($catering['notify_email'] ?? []);
    if (!$recipients) {
        return false;
    }

    $lines = array_merge(
        [
            'New WHOLESALE enquiry from the website.',
            '',
            'Name:     ' . ($d['name'] ?? ''),
            'Phone:    ' . ($d['phone'] ?? ''),
            'Email:    ' . ($d['email'] ?? ''),
            '',
        ],
        wholesale_summary_lines($d),
        ['', 'See all enquiries: https://' . $site['domain'] . '/staff/catering.php?kind=wholesale']
    );

    return mail_send_alert(
        sprintf('Wholesale enquiry: %s (%s)', $d['business'] ?? 'new business', wholesale_type_label($d['business_type'] ?? '')),
        implode("\n", $lines),
        ['reply_to' => ($d['email'] ?? '') !== '' ? $d['email'] : null],
        $recipients
    );
}

/**
 * Confirmation to the business that enquired.
 */
function wholesale_mail_confirmation(array $d): bool
{
    global $site;

    if (empty($d['email'])) {
        return false;
    }

    $lines = array_merge(
        [
            'Hi ' . mail_first_name($d['name'] ?? '') . ',',
            '',
            'Thanks for asking about stocking our cakes and bakes. Here is what you sent:',
            '',
        ],
        array_map(fn($l) => '  ' . $l, wholesale_summary_lines($d)),
        [
            '',
            "We'll be in touch with our trade prices and how ordering works.",
            '',
            $site['name'] . ', ' . $site['address'] . (!empty($site['eircode']) ? ', ' . $site['eircode'] : ''),
            'https://' . $site['domain'],
        ]
    );

    return mail_send($d['email'], 'Your wholesale enquiry — ' . $site['name'], implode("\n", $lines));
}
