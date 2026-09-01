# SlotBook

SlotBook is a web app for booking a time on a calendar. Guests pick a day, choose an open hour, and leave a name and email. An admin can sign in, see the week, then confirm, cancel, or mark an hour as paid.

The public home is the calendar. Booking happens there — not on a separate form page first.

Dummy data only. Locally, booking notes go to the mail log. On Railway they go out through a free [Brevo](https://www.brevo.com) SMTP account, so any guest email can get the note.

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

Local confirm and “we received your request” notes land in `storage/logs/laravel.log`.

```bash
php artisan test
```

## Deploy (Railway)

One Railway project: this app + a MySQL plugin. Same idea as one Render service, just with a database next to it.

1. Push this repo to GitHub (include `composer.json` with PHP 8.4, plus `Dockerfile`, `start.sh`, `start-container.sh`, `railpack.json`, and `railway.toml`).
2. Open [railway.app](https://railway.app), **New project** → **Deploy from GitHub** → `Brian-K05/SlotBook`.
3. In that same project, **New** → **Database** → **MySQL**. Wait until it is running.
4. Click the **SlotBook GitHub service** (the crashed web app), then **Variables**. You must see `MYSQLHOST` on **that** page. Shared Variables alone is not enough.
   Add **Variable Reference** (`{}`) from MySQL for:
   - `MYSQLHOST`
   - `MYSQLPORT`
   - `MYSQLDATABASE`
   - `MYSQLUSER`
   - `MYSQLPASSWORD`
5. Still on the web service, Variables:
   - `APP_KEY` — copy from your local `.env` (or run `php artisan key:generate --show`).
   - `APP_URL=https://your-service.up.railway.app` after **Settings → Networking → Generate domain**.
   - Mail (free, any guest inbox): Railway Hobby **blocks SMTP**, so do not use `MAIL_HOST` / port 587. Sign up at [brevo.com](https://www.brevo.com), verify your Gmail as a sender, then on the **SlotBook GitHub service** Variables add:
     - `BREVO_KEY` — an **API key** from Brevo → **SMTP & API** → **API keys** (starts with `xkeysib-`, not the SMTP login)
     - `MAIL_FROM_ADDRESS` — the Gmail you verified in Brevo
6. Redeploy the web service. First boot runs migrate + seed.

Open the Railway URL. Admin is still `ana@slotbook.test` / `password`.
