<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('UTC');

define('APP_NAME', 'Agomatsa Motor Vault');
define('BASE_PATH', __DIR__);
define('UPLOAD_DIR', BASE_PATH . '/uploads/cars');
define('UPLOAD_URL_PREFIX', 'uploads/cars/');

define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'vintage_cars');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

define('ITEMS_PER_PAGE', 9);
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024);

define('ALLOWED_IMAGE_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp', 'avif']);
