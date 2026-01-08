<?php
session_start();
header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Nie zalogowano']);
    exit;
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Brak danych']);
    exit;
}

$contentFile = __DIR__ . '/../data/content.json';
$backupDir = __DIR__ . '/../data/backups/';

// Create backup
if (file_exists($contentFile)) {
    $timestamp = date('Y-m-d_H-i-s');
    copy($contentFile, $backupDir . "content_backup_{$timestamp}.json");
    
    // Keep only last 20 backups
    $backups = glob($backupDir . 'content_backup_*.json');
    if (count($backups) > 20) {
        usort($backups, function($a, $b) { return filemtime($a) - filemtime($b); });
        for ($i = 0; $i < count($backups) - 20; $i++) {
            unlink($backups[$i]);
        }
    }
}

// Load current content
$content = file_exists($contentFile) ? json_decode(file_get_contents($contentFile), true) : [];

// Update content based on type
$type = $input['type'] ?? 'text';
$page = $input['page'] ?? 'home';
$field = $input['field'] ?? '';
$value = $input['value'] ?? '';

if (!$page || !$field) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Brakuje page lub field']);
    exit;
}

// Initialize page if not exists
if (!isset($content[$page])) {
    $content[$page] = [];
}

// Save the value
$content[$page][$field] = $value;

// Write to file
if (file_put_contents($contentFile, json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
    echo json_encode(['success' => true, 'message' => 'Zapisano']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Błąd zapisu']);
}
