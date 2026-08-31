# SlotBook

Sprint **02 / 10**. Fullstack product for Brian Kyle Salor’s public proof (dummy data only).

## Stack

Laravel 13, MySQL, Blade, Laravel auth. No Vue SPA — one Alpine island on the public calendar.

## MVP (v1 — ship this)

- Public page: pick a day, pick a slot, submit name + email
- Admin login: see week, confirm or cancel
- Manual paid flag (no live GCash API required for v1)
- Email on confirm (log in local)
- Prevent double-book of the same slot
- Seed a week of slots

## Out of scope for v1

Payments providers, mobile apps, AI, copying Machica production data, extra modules not listed above.

## Design

Follow `DESIGN.md`. Unique layout. Inspired ≠ cloned.

## Run

Needs PHP 8.3+, Composer, and MySQL 8. Docker is the easiest way to get MySQL on this machine.

```bash
docker compose up -d
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Open [http://127.0.0.1:8000](http://127.0.0.1:8000).

If MySQL is already running locally, skip Docker, create a `slotbook` database, and match `DB_*` in `.env`.

### Dummy admin

- Email: `ana@slotbook.test`
- Password: `password`

Confirm emails are not sent to real inboxes. They are written to `storage/logs/laravel.log`.

```bash
php artisan test
```

## GitHub (push from your account)

Do not let an editor agent commit or push this repo. Commit on your machine so GitHub shows you as the author.

```bash
git init
git add -A
git status
git commit -m "Ship SlotBook MVP: calendar booking, admin week, dummy seed."
git branch -M main
git remote add origin https://github.com/YOUR_USER/02-slotbook.git
git push -u origin main
```

`git add -A` already respects `.gitignore`. It will not upload `.env`, `vendor/`, logs, or editor folders.

## Deploy

This is a Laravel + MySQL app. It cannot live on GitHub Pages.

After you push:

1. Create a MySQL database on your host.
2. Set `APP_ENV=production`, `APP_DEBUG=false`, a real `APP_KEY`, and `APP_URL` to the HTTPS URL.
3. Keep `MAIL_MAILER=log` until you have a real mailer.
4. Run `composer install --no-dev --optimize-autoloader`, `php artisan migrate --seed --force`, then point the web root at `public/`.

## Live

Add HTTPS URL here when deployed.

## Portfolio

Do not copy this folder into the Portfolio git repo. After live, update the Portfolio **in a separate window** and push only if asked.
