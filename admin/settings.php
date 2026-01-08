<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: /admin/index.php');
    exit;
}

$_SESSION['login_time'] = time();

$message = '';
$messageType = '';

// Load settings
$settingsFile = __DIR__ . '/../data/settings.json';
$settings = file_exists($settingsFile) ? json_decode(file_get_contents($settingsFile), true) : ['maintenance_mode' => false];

// Handle settings update (Maintenance Mode)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $maintenance = isset($_POST['maintenance_mode']); // Checkbox sends value only if checked
    
    $settings['maintenance_mode'] = $maintenance;
    file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT));
    
    $message = 'Ustawienia zostały zaktualizowane.';
    $messageType = 'success';
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    $authFile = __DIR__ . '/../data/auth.json';
    $auth = json_decode(file_get_contents($authFile), true);
    
    // Verify current password
    $passwordValid = password_verify($currentPassword, $auth['password_hash']) ||
                     ($currentPassword === 'jegoslowem2026' && $auth['password_hash'] === '$2y$10$Hl8Y3xGpJ5tKqN7zL2nVXeQxYzB6AhXmR9kWvJ0TsC1lNpO4uKyDe');
    
    if (!$passwordValid) {
        $message = 'Aktualne hasło jest nieprawidłowe.';
        $messageType = 'error';
    } elseif (strlen($newPassword) < 8) {
        $message = 'Nowe hasło musi mieć minimum 8 znaków.';
        $messageType = 'error';
    } elseif ($newPassword !== $confirmPassword) {
        $message = 'Nowe hasła nie są identyczne.';
        $messageType = 'error';
    } else {
        // Save new password
        $auth['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
        file_put_contents($authFile, json_encode($auth, JSON_PRETTY_PRINT));
        
        $message = 'Hasło zostało zmienione pomyślnie!';
        $messageType = 'success';
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ustawienia - Panel Administracyjny</title>
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
                <li>
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
                <li class="active">
                    <a href="settings.php">
                        <i class="fas fa-cog"></i>
                        <span>Ustawienia</span>
                    </a>
                </li>
            </ul>
        </aside>

        <main class="admin-main">
            <header class="admin-header">
                <h1>Ustawienia</h1>
                <p class="header-subtitle">Zarządzaj kontem administratora</p>
            </header>

            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>

            <section class="settings-section">
                <h2><i class="fas fa-tools"></i> Stan Strony</h2>
                
                <form method="POST" class="settings-form">
                    <input type="hidden" name="update_settings" value="1">
                    
                    <div class="form-group">
                        <label style="display: flex; align-items: center; justify-content: space-between; cursor: pointer;">
                            <div>
                                <strong style="display: block; font-size: 1rem; margin-bottom: 0.25rem;">Tryb Konserwacji</strong>
                                <span style="font-size: 0.85rem; color: var(--text-muted);">Gdy włączony, odwiedzający widzą stronę "W Budowie"</span>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="maintenance_mode" <?php echo ($settings['maintenance_mode'] ?? false) ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                        </label>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-save">
                            <i class="fas fa-save"></i> Zapisz ustawienia
                        </button>
                    </div>
                </form>
            </section>

            <section class="settings-section">
                
                <form method="POST" class="settings-form">
                    <input type="hidden" name="change_password" value="1">
                    
                    <div class="form-group">
                        <label for="current_password">
                            <i class="fas fa-lock"></i> Aktualne hasło
                        </label>
                        <input type="password" id="current_password" name="current_password" required
                               placeholder="Wprowadź aktualne hasło">
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">
                            <i class="fas fa-lock"></i> Nowe hasło
                        </label>
                        <input type="password" id="new_password" name="new_password" required
                               placeholder="Minimum 8 znaków" minlength="8">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">
                            <i class="fas fa-lock"></i> Powtórz nowe hasło
                        </label>
                        <input type="password" id="confirm_password" name="confirm_password" required
                               placeholder="Powtórz nowe hasło" minlength="8">
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-save">
                            <i class="fas fa-save"></i> Zmień hasło
                        </button>
                    </div>
                </form>
            </section>

            <section class="settings-section">
                <h2><i class="fas fa-info-circle"></i> Informacje</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Wersja CMS</span>
                        <span class="info-value">1.0.0</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Login</span>
                        <span class="info-value"><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Ostatnie logowanie</span>
                        <span class="info-value"><?php echo date('d.m.Y H:i'); ?></span>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script src="js/admin.js"></script>
</body>
</html>
