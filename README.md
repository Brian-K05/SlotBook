# SlotBook

SlotBook is a web app for booking a time on a calendar. Guests pick a day, choose an open hour, and leave a name and email. An admin can sign in, see the week, then confirm, cancel, or mark an hour as paid.

The public home is the calendar. Booking happens there — not on a separate form page first.

Dummy data only. Guests get a note when they book, and another when the admin confirms. Paid is a flag only — no payment email. Cancel does not email the guest.

## Stack

Laravel, MySQL, Blade, Laravel auth.

## Run

You need PHP 8.4+, Composer, and MySQL 8. Docker is the easiest way to get MySQL.

```bash
docker compose up -d
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Open [http://127.0.0.1:8000](http://127.0.0.1:8000).

If MySQL is already running on your machine, skip Docker, create a `slotbook` database, and match `DB_*` in `.env`.

### Admin

- Email: `ana@slotbook.test`
- Password: `password`

Local booking and confirm notes are written to `storage/logs/laravel.log`. They are not sent to a real inbox.

```bash
php artisan test
```

## Deploy (Railway)

One Railway project: this GitHub app plus a MySQL plugin.

1. Push this repo to GitHub.
2. Open [railway.app](https://railway.app), **New project** → **Deploy from GitHub** → `Brian-K05/SlotBook`.
3. In that same project, **New** → **Database** → **MySQL**. Wait until it is running.
4. Click the **SlotBook** web service, then **Variables**. You must see `MYSQLHOST` on **that** page. Shared Variables alone is not enough.
   Add **Variable Reference** (`{}`) from MySQL for:
   - `MYSQLHOST`
   - `MYSQLPORT`
   - `MYSQLDATABASE`
   - `MYSQLUSER`
   - `MYSQLPASSWORD`
5. Still on the web service, Variables:
   - `APP_KEY` — copy from your local `.env` (or run `php artisan key:generate --show`).
   - `APP_URL` — the public URL after **Settings → Networking → Generate domain**.
   - Mail: Railway Hobby blocks SMTP (ports 587 and 465), so notes go out through the [Brevo](https://www.brevo.com) HTTPS API. Verify your Gmail as a sender in Brevo, then add:
     - `BREVO_KEY` — an **API key** from Brevo → **SMTP & API** → **API keys** (starts with `xkeysib-`, not the SMTP login)
     - `MAIL_FROM_ADDRESS` — the Gmail you verified in Brevo
     The sender name is SlotBook. Do not set `MAIL_HOST` or `MAIL_PORT`.
6. Redeploy the web service. First boot runs migrate + seed.

Open the Railway URL. Admin is still `ana@slotbook.test` / `password`.
