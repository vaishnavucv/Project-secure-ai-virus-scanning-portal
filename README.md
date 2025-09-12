# PHP Web Portal (Auth + Roles)

This is a simple PHP/MySQL portal with two roles: admin and user.

## Demo Accounts
- Admin: `admin@secure.com` / password: `password`
- User: `user1@secure.com` / password: `password`

## Setup
1. Ensure MySQL is running and you can connect as `root` (or edit `config.php`).
2. Create DB, tables, and seed demo users:
   ```bash
   mysql -u root -p < schema.sql
   ```
3. If needed, edit `config.php` to match your DB credentials or set env vars `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`.
4. Serve the `public/` directory with PHP's built-in server:
   ```bash
   php -S 127.0.0.1:8000 -t public
   ```
5. Open `http://127.0.0.1:8000` in your browser.

## Notes
- Passwords are hashed with `password_hash()` (bcrypt). The seed hashes are for the word `password`.
- Admin can add new users from the Admin panel (`/admin.php`).
