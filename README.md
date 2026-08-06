# Agomatsa Motor Vault (PHP + MySQL)

A fully responsive vintage car showcase website with:
- Public frontend (hero, featured collections, filterable gallery, timeline, contact, newsletter)
- PHP 8 backend with PDO and session authentication
- Role-based admin panel (`admin` vs `editor`)
- Car CRUD with secure image uploads
- Subscriber and contact message management
- MySQL schema with indexes and foreign keys

## Tech Stack
- PHP 8+
- MySQL 8+ (or MariaDB compatible)
- HTML5 + CSS3 (Grid/Flexbox) + vanilla JavaScript

## Project Structure
- `index.php`: public website
- `process_contact.php`: stores contact submissions
- `process_newsletter.php`: stores newsletter subscriptions
- `admin/`: admin panel pages
- `includes/`: shared config, auth, DB, and utility helpers
- `assets/css/style.css`: main styles
- `assets/js/main.js`: interactivity and reveal effects
- `database/schema.sql`: schema + seed data
- `uploads/cars/`: uploaded images

## Setup
1. Create a MySQL database and import the SQL schema:
   - `database/schema.sql`
2. Update DB credentials in `config.php`:
   - `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`
3. Start PHP in the project root:
   - `php -S localhost:8000`
4. Open:
   - Public site: `http://localhost:8000/index.php`
   - Admin login: `http://localhost:8000/admin/login.php`

## Default Seed Users
- `admin` / `admin123`
- `editor` / `editor123`

## RBAC Rules
- `admin`: full access, can delete cars and manage subscribers/messages
- `editor`: can login and create/update car entries

## Security Included
- Password hashes + `password_verify()`
- PDO prepared statements
- CSRF token protection on all POST forms
- Session regeneration on login
- Upload validation (size, extension, MIME)
