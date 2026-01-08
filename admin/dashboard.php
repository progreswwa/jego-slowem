<?php
session_start();

// Check if logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: /admin/index.php');
    exit;
}

// Session timeout (2 hours)
if (time() - ($_SESSION['login_time'] ?? 0) > 7200) {
    session_destroy();
    header('Location: /admin/index.php');
    exit;
}

// Refresh login time
$_SESSION['login_time'] = time();

// Get list of pages
$pages = [
    'home.php' => 'Strona Główna',
    'o-mnie.html' => 'O Mnie',
    'dla-kogo.html' => 'Dla Kogo',

    'cennik.html' => 'Cennik',
    'blog.html' => 'Blog',
    'faq.html' => 'Opinie',
    'kontakt.html' => 'Kontakt'
];

// Get blog post count
$blogFile = __DIR__ . '/../data/blog.json';
$blogData = file_exists($blogFile) ? json_decode(file_get_contents($blogFile), true) : ['posts' => []];
$blogCount = count($blogData['posts'] ?? []);

// Count images
$imagesDir = __DIR__ . '/../images';
$imageCount = count(glob($imagesDir . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE));
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Panel Administracyjny</title>
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
            <button class="mobile-admin-toggle" aria-label="Menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <a href="dashboard.php" class="btn btn-secondary btn-sm" style="margin-right:10px">Pulpit</a>
            <a href="logout.php" class="btn-logout" title="Wyloguj">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </nav>

    <div class="admin-container">
        <aside class="admin-sidebar">
            <ul class="sidebar-menu">
                <li class="active">
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
                <li>
                    <a href="history.php">
                        <i class="fas fa-history"></i>
                        <span>Historia</span>
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
                <h1>Pulpit</h1>
                <p class="header-subtitle">Witaj w panelu administracyjnym strony Jego Słowem</p>
            </header>

            <section class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-icon stat-pages">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-number"><?php echo count($pages); ?></span>
                        <span class="stat-label">Stron</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon stat-blog">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-number"><?php echo $blogCount; ?></span>
                        <span class="stat-label">Postów na blogu</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon stat-images">
                        <i class="fas fa-images"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-number"><?php echo $imageCount; ?></span>
                        <span class="stat-label">Obrazów</span>
                    </div>
                </div>
            </section>

            <section class="dashboard-quick-actions">
                <h2><i class="fas fa-bolt"></i> Szybkie akcje</h2>
                <div class="quick-actions-grid">
                    <a href="edit-page.php" class="action-card">
                        <i class="fas fa-edit"></i>
                        <span>Edytuj treści</span>
                        <p>Zmień teksty na stronach</p>
                    </a>
                    <a href="images.php" class="action-card">
                        <i class="fas fa-upload"></i>
                        <span>Dodaj obraz</span>
                        <p>Wgraj nowe zdjęcia</p>
                    </a>
                    <a href="blog.php?action=new" class="action-card">
                        <i class="fas fa-plus-circle"></i>
                        <span>Nowy post</span>
                        <p>Napisz artykuł na blog</p>
                    </a>
                    <a href="settings.php" class="action-card">
                        <i class="fas fa-key"></i>
                        <span>Zmień hasło</span>
                        <p>Zaktualizuj dane logowania</p>
                    </a>
                </div>
            </section>

            <section class="dashboard-pages">
                <h2><i class="fas fa-sitemap"></i> Strony do edycji</h2>
                <div class="pages-grid">
                    <?php foreach ($pages as $file => $name): ?>
                    <a href="edit-page.php?page=<?php echo urlencode($file); ?>" class="page-card">
                        <i class="fas fa-file-alt"></i>
                        <span><?php echo htmlspecialchars($name); ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </section>
        </main>
    </div>

    <script src="js/admin.js"></script>
</body>
</html>
