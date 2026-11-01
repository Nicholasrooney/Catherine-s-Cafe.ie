# Catherine's Café & Bakery — project instructions

PHP 8.3 site for Catherine's Café & Bakery, 16/17 Meath Street, Dublin 8. It was
copied on 2026-10-31 from the Cilantro Café engine and repointed. It is not a static
site: there are MySQL table bookings, a password-gated staff diary, cake and catering
enquiries, consent-gated GA4, and our own ROI stats.

Repo: `github.com/Nicholasrooney/Catherine-s-Cafe.ie`, branch `main` → Hostinger
Git deploy. Domain: catherinescafe.ie. Read `docs/GO-LIVE.md` for the launch steps and
the facts still to confirm with Catherine.

## Hard rules

- **Never touch `C:\Claudework\Cilantrocafe.ie`.** It is a live site. Read from it if
  you need to, but never write to it or run git there.
- **Credentials never go through git.** `includes/secrets/` is gitignored apart from its
  `.htaccess` and `README.md`. The local `includes/secrets/db.php` points at a SQLite
  dev database and must never be uploaded.
- **Don't invent business facts.** Hours, prices, allergens and cake notice periods come
  from Catherine or her own published menus only. An empty value hides on the page; a
  wrong one gets published and indexed.
- **Reviews are verbatim** (`includes/reviews-data.php`). Long ones may be cut at a
  sentence end, but never reworded.

## Where things live

- `includes/config.php`: the single source of truth. It holds the hours (which also
  drive the booking slots and the schema), `$ordering` (the order platforms, shown
  equally), catering packages and the `$stats` defaults.
- `includes/menu-data.php`: the menu. A price of `''` means "not published" (the bakery).
- `includes/stats.php` + `track.php` + `staff/stats.php`: the ROI dashboard. These are
  cookie-free first-party counts with a daily-rotating salt for visitor counts.
  `js/main.js` recognises links by their destination, so a new order or directions
  button is counted automatically. `includes/analytics.php` sends every event to our
  stats always, and to GA4 only after consent.
- `wholesale.php` + `includes/wholesale.php` + `includes/wholesale-handler.php`: the trade
  page. Its enquiries are rows in `catering_enquiries` with `source = 'wholesale'` (columns
  `business`, `business_type`, `products`, `frequency` are added lazily on the live DB).
  `$wholesale` in config holds the range, business types and frequencies. No trade
  prices, minimums or delivery promises go on the page until Catherine sets them.
- Raw photos, reviews and scraped menus live in `Extras/` (gitignored).
  `Extras/photo-manifest.md` lists every image.

## Before every push

There is now a portable PHP at `C:\Users\Nicholas Rooney\tools\php83\php.exe`.

```bash
PHP="/c/Users/Nicholas Rooney/tools/php83/php.exe"
for t in db/test.php db/test-seo.php db/test-setup.php db/test-catering.php db/test-weekend.php db/test-stats.php db/test-wholesale.php; do "$PHP" $t | tail -1; done
```

Keep the tests business-agnostic: the hours tests use a fixed test week, and the alert
tests read the lists from config. The preview server is `catherines` in
`C:\Claudework\.claude\launch.json` (port 8791). The local staff password is
`localtest`, set in the local-only secrets file.

## Deploying

Commit and push to `main` from here, then Nicholas clicks **Deploy** in Hostinger.
