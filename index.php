<?php
session_start();

// Load settings to check maintenance status
$settingsFile = __DIR__ . '/data/settings.json';
$maintenance_mode = true; // Default

if (file_exists($settingsFile)) {
    $settings = json_decode(file_get_contents($settingsFile), true);
    if (isset($settings['maintenance_mode'])) {
        $maintenance_mode = $settings['maintenance_mode'];
    }
}

// Check admin session
$admin_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// If maintenance is ON and user is NOT admin -> Show maintenance page
if ($maintenance_mode && !$admin_logged_in) {
    include 'maintenance.php';
    exit;
}

// Otherwise serve the homepage
include 'home.php';
?>
