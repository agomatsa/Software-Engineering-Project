<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

require_admin_auth();

$pdo = get_pdo();
$user = current_user();
$id = max(0, (int) ($_GET['id'] ?? $_POST['id'] ?? 0));
$isEdit = $id > 0;

$car = [
    'id' => 0,
    'make' => '',
    'model' => '',
    'year' => '',
    'description' => '',
    'history' => '',
    'image_url' => '',
];

if ($isEdit) {
    $stmt = $pdo->prepare('SELECT id, make, model, year, description, history, image_url FROM cars WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $found = $stmt->fetch();

    if (!$found) {
        set_flash('error', 'Car entry not found.');
        redirect('/admin/cars.php');
    }

    $car = $found;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_fail();

    $make = trim((string) ($_POST['make'] ?? ''));
    $model = trim((string) ($_POST['model'] ?? ''));
    $year = (int) ($_POST['year'] ?? 0);
    $description = trim((string) ($_POST['description'] ?? ''));
    $history = trim((string) ($_POST['history'] ?? ''));
    $imagePath = trim((string) ($_POST['image_url'] ?? ''));

    if ($make === '' || $model === '' || $year < 1886 || $year > 2099 || $description === '') {
        set_flash('error', 'Please fill all required fields with valid values.');
        redirect($isEdit ? '/admin/car_form.php?id=' . $id : '/admin/car_form.php');
    }

    if (isset($_FILES['image']) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $uploadedPath = save_uploaded_car_image($_FILES['image']);
        if ($uploadedPath === null) {
            set_flash('error', 'Image upload failed. Use a valid image up to 5MB.');
            redirect($isEdit ? '/admin/car_form.php?id=' . $id : '/admin/car_form.php');
        }
        $imagePath = $uploadedPath;
    }

    if ($isEdit && $imagePath === '') {
        $imagePath = (string) $car['image_url'];
    }

    if ($imagePath === '') {
        set_flash('error', 'Provide an image path or upload a file.');
        redirect($isEdit ? '/admin/car_form.php?id=' . $id : '/admin/car_form.php');
    }

    if ($isEdit) {
        $updateStmt = $pdo->prepare('UPDATE cars SET make = :make, model = :model, year = :year, description = :description, history = :history, image_url = :image_url, updated_by = :updated_by, updated_at = NOW() WHERE id = :id');
        $updateStmt->execute([
            'id' => $id,
            'make' => $make,
            'model' => $model,
            'year' => $year,
            'description' => $description,
            'history' => $history,
            'image_url' => $imagePath,
            'updated_by' => $user['id'],
        ]);
        set_flash('success', 'Car entry updated.');
    } else {
        $insertStmt = $pdo->prepare('INSERT INTO cars (make, model, year, description, history, image_url, created_by, updated_by, created_at, updated_at) VALUES (:make, :model, :year, :description, :history, :image_url, :created_by, :updated_by, NOW(), NOW())');
        $insertStmt->execute([
            'make' => $make,
            'model' => $model,
            'year' => $year,
            'description' => $description,
            'history' => $history,
            'image_url' => $imagePath,
            'created_by' => $user['id'],
            'updated_by' => $user['id'],
        ]);
        set_flash('success', 'Car entry created.');
    }

    redirect('/admin/cars.php');
}

$pageTitle = $isEdit ? 'Edit Car' : 'Add Car';
require __DIR__ . '/partials/header.php';
?>
<section class="admin-panel" aria-labelledby="car-form-heading">
    <div class="admin-panel__header">
        <h1 id="car-form-heading"><?= esc($isEdit ? 'Edit Car' : 'Add Car') ?></h1>
        <a href="/admin/cars.php">Back to cars</a>
    </div>

    <form method="post" action="/admin/car_form.php<?= $isEdit ? '?id=' . esc((string) $id) : '' ?>" class="stack-form admin-form" enctype="multipart/form-data" aria-label="Car form">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= esc((string) $car['id']) ?>">

        <label>
            <span>Make *</span>
            <input type="text" name="make" required maxlength="120" value="<?= esc((string) $car['make']) ?>">
        </label>

        <label>
            <span>Model *</span>
            <input type="text" name="model" required maxlength="120" value="<?= esc((string) $car['model']) ?>">
        </label>

        <label>
            <span>Year *</span>
            <input type="number" name="year" required min="1886" max="2099" value="<?= esc((string) $car['year']) ?>">
        </label>

        <label>
            <span>Description *</span>
            <textarea name="description" rows="4" required maxlength="1200"><?= esc((string) $car['description']) ?></textarea>
        </label>

        <label>
            <span>History</span>
            <textarea name="history" rows="4" maxlength="2000"><?= esc((string) $car['history']) ?></textarea>
        </label>

        <label>
            <span>Image Upload</span>
            <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp,.avif,image/*">
        </label>

        <label>
            <span>Image Path (optional if uploaded)</span>
            <input type="text" name="image_url" value="<?= esc((string) $car['image_url']) ?>" placeholder="example: Ford GT40.jpeg or uploads/cars/file.jpg">
        </label>

        <?php if ($isEdit && $car['image_url'] !== ''): ?>
            <img class="preview-image" src="/<?= esc((string) $car['image_url']) ?>" alt="Current car image preview">
        <?php endif; ?>

        <button type="submit" class="btn btn-primary"><?= esc($isEdit ? 'Save Changes' : 'Create Car') ?></button>
    </form>
</section>
<?php require __DIR__ . '/partials/footer.php'; ?>
