# Handoff Setup Guide

This package contains a complete vintage car website with PHP, MySQL, and admin panel.

## 1) What the recipient needs
- PHP 8+
- MySQL 8+ (or MariaDB)
- A terminal

## 2) Configure database credentials
Open `config.php` and set:
- DB_HOST
- DB_PORT
- DB_NAME
- DB_USER
- DB_PASS

Default project database name is:
- vintage_cars

## 3) Import database schema + seed data
From project root:

```bash
mysql -u root < database/schema.sql
```

If MySQL uses a custom socket:

```bash
mysql -u root -S /tmp/mysql.sock < database/schema.sql
```

## 4) Start the website
From project root:

```bash
php -S localhost:8000
```

Open:
- Public: http://localhost:8000/index.php
- Admin: http://localhost:8000/admin/login.php

## 5) Default admin users
- admin / admin123
- editor / editor123

## 6) First things to change (important)
- Change admin/editor passwords immediately
- Update DB password in config.php if using a secured MySQL user
- Optional: run mysql_secure_installation

## 7) Troubleshooting
- Error: "could not find driver"
  - Ensure PHP has PDO MySQL enabled.
- Error: "Access denied"
  - Check DB_USER and DB_PASS in config.php.
- Error connecting to MySQL socket
  - Use host/port in config.php, or import via explicit socket path.

## 8) Production note
The built-in PHP server is for local/testing. For production, run behind Apache/Nginx with HTTPS and proper file permissions.
