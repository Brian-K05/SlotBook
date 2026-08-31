# SlotBook

SlotBook is a web app for booking a time on a calendar. Guests pick a day, choose an open hour, and leave a name and email. An admin can sign in, see the week, then confirm, cancel, or mark an hour as paid.

The public home is the calendar. Booking happens there — not on a separate form page first.

Dummy data only. Confirm notes are written to the local mail log, not sent to real inboxes.

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

Confirm emails land in `storage/logs/laravel.log`.

```bash
php artisan test
```

## Deploy (Railway)

One Railway project: this app + a MySQL plugin. Same idea as one Render service, just with a database next to it.

1. Push this repo to GitHub (include `composer.json` with PHP 8.4, plus `Dockerfile`, `start.sh`, `start-container.sh`, `railpack.json`, and `railway.toml`).
2. Open [railway.app](https://railway.app), **New project** → **Deploy from GitHub** → `Brian-K05/SlotBook`.
3. In that same project, **Add plugin** / **New** → **MySQL**. Wait until it is running.
4. On the **web** service, Variables:
   - Share or reference the MySQL plugin vars (`MYSQLHOST`, `MYSQLPORT`, `MYSQLDATABASE`, `MYSQLUSER`, `MYSQLPASSWORD`). The app reads those if `DB_HOST` is empty.
   - Add `APP_KEY` — copy the `APP_KEY=...` line from your local `.env` (run `php artisan key:generate --show` if you need a new one).
   - Add `APP_URL=https://your-service.up.railway.app` after Railway gives you a domain (**Settings → Networking → Generate domain**).
5. Redeploy the web service. First boot runs migrate + seed.

Open the Railway URL. Admin is still `ana@slotbook.test` / `password`.
