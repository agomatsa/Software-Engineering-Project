<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

function esc(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function set_flash(string $key, string $message): void
{
    $_SESSION['flash'][$key] = $message;
}

function get_flash(string $key): ?string
{
    if (!isset($_SESSION['flash'][$key])) {
        return null;
    }

    $message = $_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);

    return $message;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . esc(csrf_token()) . '">';
}

function verify_csrf_or_fail(): void
{
    $token = $_POST['csrf_token'] ?? '';

    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(422);
        exit('Invalid CSRF token.');
    }
}

function paginate(int $totalItems, int $page, int $perPage): array
{
    $totalPages = max(1, (int) ceil($totalItems / $perPage));
    $safePage = max(1, min($page, $totalPages));
    $offset = ($safePage - 1) * $perPage;

    return [
        'page' => $safePage,
        'per_page' => $perPage,
        'total_items' => $totalItems,
        'total_pages' => $totalPages,
        'offset' => $offset,
    ];
}

function valid_image_upload(array $file): bool
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return false;
    }

    if (($file['size'] ?? 0) > MAX_UPLOAD_SIZE) {
        return false;
    }

    $extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_IMAGE_EXTENSIONS, true)) {
        return false;
    }

    $mime = mime_content_type((string) ($file['tmp_name'] ?? ''));
    return is_string($mime) && str_starts_with($mime, 'image/');
}

function save_uploaded_car_image(array $file): ?string
{
    if (!valid_image_upload($file)) {
        return null;
    }

    $extension = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
    $targetName = bin2hex(random_bytes(16)) . '.' . $extension;
    $targetPath = UPLOAD_DIR . '/' . $targetName;

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    if (!move_uploaded_file((string) $file['tmp_name'], $targetPath)) {
        return null;
    }

    return UPLOAD_URL_PREFIX . $targetName;
}
