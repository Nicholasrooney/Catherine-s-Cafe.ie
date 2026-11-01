# Going live: catherinescafe.ie

The site is in `github.com/Nicholasrooney/Catherine-s-Cafe.ie` on `main`. Do these in
order. Each step depends on the one before it.

## 1. Connect Hostinger to the repo

hPanel → **Websites → catherinescafe.ie → Advanced → Git**. Repository
`https://github.com/Nicholasrooney/Catherine-s-Cafe.ie.git`, branch `main`, folder
`public_html` (leave the directory field empty). Click **Deploy**.

Turn on SSL under **Security → SSL** once the domain points at Hostinger.

## 2. The database (bookings, the staff diary and the stats need it)

1. hPanel → **Databases → MySQL Databases**. Create a database and a user and give the
   user full access. The names look like `u123456789_catherines`.
2. Go to `https://catherinescafe.ie/setup.php`. It checks the details, creates every table
   and writes `includes/secrets/db.php` for you. That file is gitignored and blocked from
   the web, so passwords never reach GitHub.
3. Pick a staff password on the same page. Use something like `meath-street-fry-26`, not
   `12345`: the diary holds customers' names and phone numbers.

The public site works without a database, but bookings, enquiries and stats are only
saved once this is done.

## 3. Email for booking and cake alerts

1. hPanel → **Emails** → create `bookings@catherinescafe.ie`.
2. Put its password into `includes/secrets/db.php` (`$mail['smtp']['pass']`) using the
   File Manager, and tell Claude the address so it can be set in `config.php`.
3. Check that SPF and DKIM are on (**Emails → DNS settings**), or alerts go to spam.

Alerts go to Nicholas for now. Add Catherine's email to `notify_email` in `config.php`.

## 4. The ROI dashboard

It's at **`/staff/stats.php`**, behind the staff password.

It works the moment the database exists. Every directions, order, phone, menu and booking
tap is counted on our own server, with no cookie and nothing personal stored, so it
includes the visitors who decline cookies.

What it shows:
- **Visitors**, **directions taps**, **order taps** split by Flipdish, Just Eat and
  Deliveroo, **phone taps**, **real table bookings**, **cake and catering enquiries** and
  **wholesale enquiries** (with how many were won).
  Each comes with the change against the previous period.
- A day-by-day chart, where visitors came from (Google, Instagram, Facebook and so on),
  which map app people used, and which pages they read.
- **What it's worth.** Bookings and enquiries are real. The taps are multiplied by your
  own conversion guesses, which you can edit on the page. Put the monthly cost of the
  site in, and it shows the return for every €1.
- **Figures from elsewhere.** Once a month, type in the numbers that live on other sites:
  - Google Business Profile → Performance: views, **directions requests**, calls and
    website clicks.
  - Flipdish, Just Eat and Deliveroo: orders and sales.

  Google keeps directions requests made from the Maps listing itself and never shares
  them with websites, so this is the only way to get them onto the same page.

### Tag your links so the dashboard can tell sources apart

Add `?utm_source=` to every link you control. The dashboard then shows each one as its
own source:

| Where | Link to use |
|---|---|
| Google Business Profile website button | `https://catherinescafe.ie/?utm_source=google_profile` |
| Instagram bio | `https://catherinescafe.ie/?utm_source=instagram_bio` |
| Facebook page | `https://catherinescafe.ie/?utm_source=facebook_page` |
| QR code on flyers or in the shop | `https://catherinescafe.ie/?utm_source=qr` |

## 5. Google Analytics 4 (optional, for the deeper reports)

1. Go to analytics.google.com and create a GA4 property for catherinescafe.ie with a web
   stream. Copy the Measurement ID (`G-XXXXXXX`).
2. Tell Claude the ID; it goes in `$analytics['ga4_id']` in `config.php`.

GA4 only loads after a visitor taps "Accept all", as Irish law requires. The ROI
dashboard doesn't depend on it.

## 6. Things to confirm with Catherine before launch

- [ ] **Opening hours.** The ones in `config.php` come from libertiesdublin.ie and are
      unconfirmed. They also set the booking slots and the hours Google sees.
- [ ] **Menu prices.** They're taken from the online ordering menu (Oct 2026), so check
      them against the boards.
- [ ] **Bakery prices**, if she wants them shown. The bakery is listed without prices
      for now.
- [ ] **Allergens** for each dish. Until we have them, the menu says to ask staff.
- [ ] **The Deliveroo link.** It's hidden until a URL is added in `$ordering`.
- [ ] Table booking limits: up to 4 guests and 2 bookings an hour. Are those right for
      the room?
- [ ] Cake and catering minimum of 6 people. Notice needed for a celebration cake?
- [ ] **Wholesale.** The page publishes no prices, minimums or delivery days on purpose.
      Before the first enquiry, Catherine needs: a trade price list, a minimum order,
      delivery area and days (or collection only), an order cut-off time, and written
      allergen information for every product (she must give it to businesses she
      supplies). Does she want to offer samples? The form has an "I'd like to try
      samples first" box.
- [ ] When a wholesale enquiry becomes a customer, mark it **Booked** in Staff →
      Catering & wholesale. Set "What a wholesale account is worth a month" on the
      stats page so the dashboard can value it.
- [ ] Catherine's email for alerts.
