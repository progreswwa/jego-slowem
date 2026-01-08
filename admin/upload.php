<?php
session_start();
header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Nie zalogowano']);
    exit;
}

// Check if file was uploaded
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Błąd przesyłania pliku']);
    exit;
}

$file = $_FILES['image'];
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$maxSize = 5 * 1024 * 1024; // 5MB

// Validate file type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedTypes)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Niedozwolony typ pliku. Użyj JPG, PNG, GIF lub WebP.']);
    exit;
}

// Validate file size
if ($file['size'] > $maxSize) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Plik jest zbyt duży. Maksymalny rozmiar to 5MB.']);
    exit;
}

// Generate unique filename
$extension = match($mimeType) {
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/gif' => 'gif',
    'image/webp' => 'webp',
    default => 'jpg'
};

$filename = 'cms_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
$uploadDir = __DIR__ . '/../images/';
$uploadPath = $uploadDir . $filename;

// Create directory if not exists
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Move uploaded file
if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
    echo json_encode([
        'success' => true,
        'path' => 'images/' . $filename,
        'filename' => $filename
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Błąd zapisu pliku']);
}
