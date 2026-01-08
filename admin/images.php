<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: /admin/index.php');
    exit;
}

$_SESSION['login_time'] = time();

$message = '';
$messageType = '';
$imagesDir = __DIR__ . '/../images';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $file = $_FILES['image'];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (in_array($mimeType, $allowedTypes)) {
            // Generate safe filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($file['name'], PATHINFO_FILENAME));
            $filename = $safeName . '_' . time() . '.' . strtolower($extension);
            
            $destination = $imagesDir . '/' . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $message = 'Obraz "' . htmlspecialchars($filename) . '" został wgrany pomyślnie!';
                $messageType = 'success';
            } else {
                $message = 'Błąd podczas zapisywania pliku.';
                $messageType = 'error';
            }
        } else {
            $message = 'Niedozwolony format pliku. Używaj: JPG, PNG, GIF, WebP.';
            $messageType = 'error';
        }
    } else {
        $message = 'Błąd podczas wgrywania pliku.';
        $messageType = 'error';
    }
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_image'])) {
    $imageToDelete = basename($_POST['delete_image']);
    $filePath = $imagesDir . '/' . $imageToDelete;
    
    if (file_exists($filePath) && is_file($filePath)) {
        if (unlink($filePath)) {
            $message = 'Obraz został usunięty.';
            $messageType = 'success';
        } else {
            $message = 'Nie udało się usunąć obrazu.';
            $messageType = 'error';
        }
    }
}

// Get all images
$images = [];
if (is_dir($imagesDir)) {
    $files = glob($imagesDir . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
    foreach ($files as $file) {
        $images[] = [
            'name' => basename($file),
            'path' => 'images/' . basename($file),
            'size' => filesize($file),
            'modified' => filemtime($file)
        ];
    }
    // Sort by modified date, newest first
    usort($images, function($a, $b) {
        return $b['modified'] - $a['modified'];
    });
}

function formatSize($bytes) {
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 1) . ' KB';
    }
    return $bytes . ' B';
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zarządzanie Obrazami - Panel Administracyjny</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body class="admin-body">
    <nav class="admin-nav">
        <div class="nav-brand">
            <img src="../images/logo.png" alt="Jego Słowem" class="nav-logo">
            <span>Panel CMS</span>
        </div>
        <div class="nav-user">
            <span><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
            <a href="logout.php" class="btn-logout" title="Wyloguj">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </nav>

    <div class="admin-container">
        <aside class="admin-sidebar">
            <ul class="sidebar-menu">
                <li>
                    <a href="dashboard.php">
                        <i class="fas fa-home"></i>
                        <span>Pulpit</span>
                    </a>
                </li>
                <li>
                    <a href="edit-page.php">
                        <i class="fas fa-edit"></i>
                        <span>Edycja Treści</span>
                    </a>
                </li>
                <li class="active">
                    <a href="images.php">
                        <i class="fas fa-images"></i>
                        <span>Obrazy</span>
                    </a>
                </li>
                <li>
                    <a href="blog.php">
                        <i class="fas fa-newspaper"></i>
                        <span>Blog</span>
                    </a>
                </li>
                <li>
                    <a href="settings.php">
                        <i class="fas fa-cog"></i>
                        <span>Ustawienia</span>
                    </a>
                </li>
            </ul>
        </aside>

        <main class="admin-main">
            <header class="admin-header">
                <h1>Zarządzanie Obrazami</h1>
                <p class="header-subtitle">Wgraj nowe obrazy lub zarządzaj istniejącymi</p>
            </header>

            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo $message; ?>
            </div>
            <?php endif; ?>

            <section class="upload-section">
                <h2><i class="fas fa-cloud-upload-alt"></i> Wgraj nowy obraz</h2>
                <form method="POST" enctype="multipart/form-data" class="upload-form">
                    <div class="upload-dropzone" id="dropzone">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p>Przeciągnij obraz tutaj lub kliknij aby wybrać</p>
                        <span class="upload-hint">JPG, PNG, GIF, WebP (max 10MB)</span>
                        <input type="file" name="image" id="imageInput" accept="image/*" required>
                    </div>
                    <button type="submit" class="btn btn-upload">
                        <i class="fas fa-upload"></i> Wgraj obraz
                    </button>
                </form>
            </section>

            <section class="images-section">
                <h2><i class="fas fa-images"></i> Biblioteka obrazów (<?php echo count($images); ?>)</h2>
                
                <?php if (empty($images)): ?>
                <div class="empty-state">
                    <i class="fas fa-image"></i>
                    <p>Brak obrazów w bibliotece</p>
                </div>
                <?php else: ?>
                <div class="images-grid">
                    <?php foreach ($images as $image): ?>
                    <div class="image-card">
                        <div class="image-preview">
                            <img src="../<?php echo htmlspecialchars($image['path']); ?>" alt="<?php echo htmlspecialchars($image['name']); ?>">
                        </div>
                        <div class="image-info">
                            <span class="image-name" title="<?php echo htmlspecialchars($image['name']); ?>">
                                <?php echo htmlspecialchars($image['name']); ?>
                            </span>
                            <span class="image-size"><?php echo formatSize($image['size']); ?></span>
                        </div>
                        <div class="image-actions">
                            <button type="button" class="btn-copy" onclick="copyPath('<?php echo htmlspecialchars($image['path']); ?>')" title="Kopiuj ścieżkę">
                                <i class="fas fa-copy"></i>
                            </button>
                            <a href="../<?php echo htmlspecialchars($image['path']); ?>" target="_blank" class="btn-view" title="Otwórz">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Czy na pewno usunąć ten obraz?');">
                                <input type="hidden" name="delete_image" value="<?php echo htmlspecialchars($image['name']); ?>">
                                <button type="submit" class="btn-delete" title="Usuń">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </section>
        </main>
    </div>

    <script src="js/admin.js"></script>
    <script>
        function copyPath(path) {
            navigator.clipboard.writeText(path).then(() => {
                alert('Ścieżka skopiowana: ' + path);
            });
        }
    </script>
</body>
</html>
