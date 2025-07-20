<?php
require_once '../includes/functions.php';
require_once '../includes/auth_admin.php';
require_once '../includes/header.php';

$brandingFile = '../config/branding.json';
$branding = file_exists($brandingFile) ? json_decode(file_get_contents($brandingFile), true) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle Logo Upload
    if (!empty($_FILES['logo']['tmp_name'])) {
        $logoPath = '../assets/uploads/branding/logo.png';
        move_uploaded_file($_FILES['logo']['tmp_name'], $logoPath);
        $branding['logo'] = 'assets/uploads/branding/logo.png';
    }

    // Colors and Texts
    $branding['primary_color'] = $_POST['primary_color'] ?? '#007bff';
    $branding['secondary_color'] = $_POST['secondary_color'] ?? '#6c757d';
    $branding['site_title'] = $_POST['site_title'] ?? 'Recruitory';
    $branding['footer_text'] = $_POST['footer_text'] ?? '';

    // Save to JSON
    file_put_contents($brandingFile, json_encode($branding, JSON_PRETTY_PRINT));

    // Create CSS override
    $customCss = ":root {
        --primary-color: {$branding['primary_color']};
        --secondary-color: {$branding['secondary_color']};
    }";
    file_put_contents('../assets/css/custom_brand.css', $customCss);

    $_SESSION['success'] = "Branding updated successfully.";
    header('Location: customize_brand.php');
    exit;
}
?>

<div class="container mt-4">
    <h3>Customize Branding</h3>
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data" class="row g-3">
        <div class="col-md-6">
            <label for="logo" class="form-label">Upload Logo (PNG preferred)</label>
            <input type="file" class="form-control" name="logo" id="logo">
            <?php if (!empty($branding['logo'])): ?>
                <img src="/<?= $branding['logo'] ?>" alt="Logo" class="img-thumbnail mt-2" width="150">
            <?php endif; ?>
        </div>

        <div class="col-md-3">
            <label class="form-label">Primary Color</label>
            <input type="color" name="primary_color" class="form-control form-control-color" value="<?= htmlspecialchars($branding['primary_color'] ?? '#007bff') ?>">
        </div>

        <div class="col-md-3">
            <label class="form-label">Secondary Color</label>
            <input type="color" name="secondary_color" class="form-control form-control-color" value="<?= htmlspecialchars($branding['secondary_color'] ?? '#6c757d') ?>">
        </div>

        <div class="col-12">
            <label class="form-label">Site Title</label>
            <input type="text" name="site_title" class="form-control" value="<?= htmlspecialchars($branding['site_title'] ?? '') ?>">
        </div>

        <div class="col-12">
            <label class="form-label">Footer Text</label>
            <textarea name="footer_text" class="form-control"><?= htmlspecialchars($branding['footer_text'] ?? '') ?></textarea>
        </div>

        <div class="col-12">
            <button class="btn btn-primary">Save Changes</button>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>

