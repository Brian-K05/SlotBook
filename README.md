# SlotBook

SlotBook is a web app for booking a time on a calendar. Guests pick a day, choose an open hour, and leave a name and email. An admin can sign in, see the week, then confirm, cancel, or mark an hour as paid.

The public home is the calendar. Booking happens there — not on a separate form page first.

Dummy data only. Confirm notes are written to the local mail log, not sent to real inboxes.

## Stack

Laravel, MySQL, Blade, Laravel auth.

## Run

You need PHP 8.3+, Composer, and MySQL 8. Docker is the easiest way to get MySQL.

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
