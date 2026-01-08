<?php
session_start();

// Load settings to check maintenance status
$settingsFile = __DIR__ . '/data/settings.json';
$maintenance_mode = false; // Default OFF

if (file_exists($settingsFile)) {
    $settings = json_decode(file_get_contents($settingsFile), true);
    if (isset($settings['site']['maintenance_mode'])) {
        $maintenance_mode = $settings['site']['maintenance_mode'];
    }
}

// Check admin session
$admin_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// Check if in edit mode
$edit_mode = isset($_GET['edit']) && $_GET['edit'] == '1' && $admin_logged_in;

// If maintenance is ON and user is NOT admin -> Show maintenance page
if ($maintenance_mode && !$admin_logged_in) {
    include 'maintenance.php';
    exit;
}

// Otherwise serve the homepage
include 'home.php';

// If in edit mode, inject CMS editor
if ($edit_mode): ?>
<link rel="stylesheet" href="/admin/cms-editor.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script>window.CMS_EDIT_MODE = true;</script>
<script src="/admin/cms-editor.js"></script>
<script>document.body.classList.add('cms-edit-mode');</script>
<?php endif; ?>
