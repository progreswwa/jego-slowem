<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: /admin/index.php');
    exit;
}

$_SESSION['login_time'] = time();

$message = '';
$messageType = '';
$backupDir = __DIR__ . '/../backups';

// Handle restore
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restore'])) {
    $backupFile = $_POST['restore'];
    $backupPath = $backupDir . '/' . $backupFile;
    
    if (file_exists($backupPath) && strpos($backupFile, '..') === false) {
        // Extract original filename (after timestamp)
        $parts = explode('_', $backupFile, 3);
        if (count($parts) >= 3) {
            $originalFile = $parts[2];
            $targetPath = __DIR__ . '/../' . $originalFile;
            
            if (file_exists($targetPath)) {
                // Create backup of current before restore
                copy($targetPath, $backupDir . '/' . date('Y-m-d_H-i-s') . '_PRE-RESTORE_' . $originalFile);
                
                // Restore
                if (copy($backupPath, $targetPath)) {
                    $message = 'Przywrócono backup: ' . htmlspecialchars($backupFile);
                    $messageType = 'success';
                } else {
                    $message = 'Błąd podczas przywracania.';
                    $messageType = 'error';
                }
            }
        }
    }
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $deleteFile = $_POST['delete'];
    $deletePath = $backupDir . '/' . $deleteFile;
    
    if (file_exists($deletePath) && strpos($deleteFile, '..') === false) {
        if (unlink($deletePath)) {
            $message = 'Usunięto backup: ' . htmlspecialchars($deleteFile);
            $messageType = 'success';
        }
    }
}

// Get backups
$backups = [];
if (is_dir($backupDir)) {
    $files = glob($backupDir . '/*');
    usort($files, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });
    
    foreach (array_slice($files, 0, 30) as $file) {
        $backups[] = [
            'name' => basename($file),
            'size' => filesize($file),
            'date' => filemtime($file)
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historia Zmian - CMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .backup-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .backup-table th, .backup-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        .backup-table th {
            background: var(--bg-surface);
            font-weight: 600;
            color: var(--gold-primary);
        }
        .backup-table tr:hover {
            background: rgba(201, 167, 83, 0.05);
        }
        .backup-name {
            font-family: monospace;
            font-size: 0.85rem;
        }
        .backup-size {
            color: var(--text-muted);
            font-size: 0.85rem;
        }
        .backup-date {
            color: var(--text-muted);
        }
        .backup-actions {
            display: flex;
            gap: 8px;
        }
        .btn-restore {
            background: rgba(34, 197, 94, 0.15);
            color: #22c55e;
            border: 1px solid rgba(34, 197, 94, 0.3);
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: all 0.2s;
        }
        .btn-restore:hover {
            background: #22c55e;
            color: #fff;
        }
        .btn-delete-backup {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: all 0.2s;
        }
        .btn-delete-backup:hover {
            background: #ef4444;
            color: #fff;
        }
        .no-backups {
            text-align: center;
            padding: 40px;
            color: var(--text-muted);
        }
        .no-backups i {
            font-size: 3rem;
            margin-bottom: 15px;
            display: block;
        }
    </style>
</head>
<body class="admin-body">
    <nav class="admin-nav">
        <div class="nav-brand">
            <img src="../images/logo.png" alt="CMS" class="nav-logo">
            <span>Historia Zmian</span>
        </div>
        <div class="nav-user">
            <a href="dashboard.php" class="btn btn-secondary btn-sm" style="margin-right:10px">Pulpit</a>
            <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </nav>

    <div class="admin-container">
        <aside class="admin-sidebar">
            <ul class="sidebar-menu">
                <li><a href="dashboard.php"><i class="fas fa-home"></i> Pulpit</a></li>
                <li><a href="edit-page.php"><i class="fas fa-magic"></i> Edytor Wizualny</a></li>
                <li><a href="images.php"><i class="fas fa-images"></i> Obrazy</a></li>
                <li><a href="blog.php"><i class="fas fa-newspaper"></i> Blog</a></li>
                <li class="active"><a href="history.php"><i class="fas fa-history"></i> Historia</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> Ustawienia</a></li>
            </ul>
        </aside>

        <main class="admin-main">
            <header class="admin-header">
                <h1><i class="fas fa-history"></i> Historia Zmian</h1>
                <p class="header-subtitle">Przeglądaj i przywracaj poprzednie wersje stron</p>
            </header>

            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <i class="fas fa-info-circle"></i> <?php echo $message; ?>
            </div>
            <?php endif; ?>

            <section class="editor-section">
                <?php if (empty($backups)): ?>
                <div class="no-backups">
                    <i class="fas fa-archive"></i>
                    <p>Brak zapisanych kopii zapasowych</p>
                    <p class="text-muted">Backupy są tworzone automatycznie przy każdym zapisie w edytorze.</p>
                </div>
                <?php else: ?>
                <table class="backup-table">
                    <thead>
                        <tr>
                            <th>Nazwa pliku</th>
                            <th>Rozmiar</th>
                            <th>Data</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($backups as $backup): ?>
                        <tr>
                            <td class="backup-name"><?php echo htmlspecialchars($backup['name']); ?></td>
                            <td class="backup-size"><?php echo number_format($backup['size'] / 1024, 1); ?> KB</td>
                            <td class="backup-date"><?php echo date('d.m.Y H:i', $backup['date']); ?></td>
                            <td class="backup-actions">
                                <form method="POST" style="display:inline" onsubmit="return confirm('Czy na pewno przywrócić tę wersję?');">
                                    <input type="hidden" name="restore" value="<?php echo htmlspecialchars($backup['name']); ?>">
                                    <button type="submit" class="btn-restore"><i class="fas fa-undo"></i> Przywróć</button>
                                </form>
                                <form method="POST" style="display:inline" onsubmit="return confirm('Usunąć ten backup?');">
                                    <input type="hidden" name="delete" value="<?php echo htmlspecialchars($backup['name']); ?>">
                                    <button type="submit" class="btn-delete-backup"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </section>
        </main>
    </div>

    <script src="js/admin.js"></script>
</body>
</html>
