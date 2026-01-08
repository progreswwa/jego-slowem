<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: /admin/index.php');
    exit;
}

$_SESSION['login_time'] = time();

// Get list of pages
$pages = [
    'home.php' => 'Strona Główna',
    'o-mnie.html' => 'O Mnie',
    'dla-kogo.html' => 'Dla Kogo',

    'cennik.html' => 'Oferta',
    'blog.html' => 'Blog',
    'faq.html' => 'Opinie',
    'kontakt.html' => 'Kontakt'
];

$currentPage = $_GET['page'] ?? '';
$pageName = '';
$message = '';
$messageType = '';
$editableElements = [];

// Helper function to load HTML cleanly
function loadHtml($filePath) {
    $content = file_get_contents($filePath);
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    // Force UTF-8
    $dom->loadHTML('<?xml encoding="UTF-8">' . $content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    return $dom;
}

// Helper to save HTML without adding doctype mess
function saveHtml($dom, $filePath) {
    $html = $dom->saveHTML();
    // Remove the XML encoding hack
    $html = str_replace('<?xml encoding="UTF-8">', '', $html);
    // Fix potential doctype issues if needed, but saveHTML usually handles it if loaded correctly
    return file_put_contents($filePath, $html);
}

// Handle save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['page'])) {
    $pageFile = $_POST['page'];
    
    if (array_key_exists($pageFile, $pages)) {
        $filePath = __DIR__ . '/../' . $pageFile;
        
        // Backup
        $backupDir = __DIR__ . '/../backups';
        if (!is_dir($backupDir)) mkdir($backupDir, 0755, true);
        copy($filePath, $backupDir . '/' . date('Y-m-d_H-i-s') . '_' . $pageFile);
        
        $dom = loadHtml($filePath);
        $xpath = new DOMXPath($dom);
        $nodes = $xpath->query('//*[@data-cms-id]');
        
        $changesCount = 0;
        foreach ($nodes as $node) {
            $id = $node->getAttribute('data-cms-id');
            if (isset($_POST[$id])) {
                $newContent = $_POST[$id];
                
                // Handle different element types
                if ($node->nodeName === 'img') {
                    if ($node->getAttribute('src') !== $newContent) {
                        $node->setAttribute('src', $newContent);
                        $changesCount++;
                    }
                } else {
                    // For text/html content settings
                    // We need to be careful not to strip internal HTML if the user intends it, 
                    // but for "Visual Editor" we usually assume simple text or limited HTML.
                    // For now, let's treat it as text with potential HTML entities allowed.
                    // But to be safe and simple, let's assign nodeValue for pure text or recreate children for HTML.
                    // User want "text editing", so let's stick to simple text for now to avoid breaking layout.
                    // Actually, if they want to bold something, they might need HTML.
                    // Let's use a simple approach: if it looks like HTML, use fragment.
                    
                    if (trim($node->nodeValue) !== trim($newContent)) {
                        // Create fragment to support HTML tags in content
                        $fragment = $dom->createDocumentFragment();
                        // AppendXML needs valid XML, which user input might not be.
                        // Safer to just set nodeValue if we want "idiot-proof".
                        // BUT user might want <br>.
                        // Let's try to set nodeValue for now (safe). 
                        // If they truly need HTML, we'll need a richer editor.
                        // Assuming simple text for now based on "concrete content".
                        
                        // FIX: nodeValue escapes HTML. To support basic HTML, we need more logic.
                        // Use simpler replacement:
                        $node->nodeValue = ''; // Clear
                        $node->textContent = $newContent;
                        $changesCount++;
                    }
                }
            }
        }
        
        if (saveHtml($dom, $filePath)) {
            $message = "Zapisano zmiany ($changesCount elementów)!";
            $messageType = 'success';
        } else {
            $message = 'Błąd zapisu pliku.';
            $messageType = 'error';
        }
        
        $currentPage = $pageFile;
    }
}

// Load elements for form
if ($currentPage && array_key_exists($currentPage, $pages)) {
    $filePath = __DIR__ . '/../' . $currentPage;
    if (file_exists($filePath)) {
        $dom = loadHtml($filePath);
        $xpath = new DOMXPath($dom);
        $nodes = $xpath->query('//*[@data-cms-id]');
        
        // Friendly Polish labels for CMS fields
        $friendlyLabels = [
            // Home page
            'home_hero_title' => '🏠 Tytuł główny (Hero)',
            'home_hero_desc' => '📝 Opis pod tytułem',
            'home_hero_btn1' => '🔘 Przycisk 1 (główny)',
            'home_hero_btn2' => '🔘 Przycisk 2',
            'home_quote_text' => '💬 Cytat biblijny',
            'home_quote_cite' => '📖 Źródło cytatu',
            // About page
            'about_photo' => '📷 Zdjęcie profilowe',
            'about_name' => '👤 Imię i nazwisko',
            'about_role' => '💼 Rola/Stanowisko',
            'about_bio_text' => '📝 Tekst biografii',
            // Target audience
            'target_card1_title' => '👤 Tytuł: Osoby indywidualne',
            'target_card1_desc' => '📝 Opis: Osoby indywidualne',
            'target_card2_title' => '❤️ Tytuł: Pary',
            'target_card2_desc' => '📝 Opis: Pary',
            'target_card3_title' => '💼 Tytuł: Liderzy',
            'target_card3_desc' => '📝 Opis: Liderzy',
            // Pricing - Individual
            'price_free_title' => '🆓 Nazwa: Konsultacja bezpłatna',
            'price_free_value' => '💰 Cena: Konsultacja bezpłatna',
            'price_single_title' => '1️⃣ Nazwa: Konsultacja jednorazowa',
            'price_single_value' => '💰 Cena: Konsultacja jednorazowa',
            'price_start_title' => '🚀 Nazwa: Pakiet Start',
            'price_start_desc' => '📝 Opis: Pakiet Start',
            'price_start_value' => '💰 Cena: Pakiet Start',
            'price_titan_title' => '⭐ Nazwa: Pakiet Tytanowy',
            'price_titan_desc' => '📝 Opis: Pakiet Tytanowy',
            'price_titan_value' => '💰 Cena: Pakiet Tytanowy',
            'price_forward_title' => '🛤️ Nazwa: Pakiet Droga Naprzód',
            'price_forward_desc' => '📝 Opis: Pakiet Droga Naprzód',
            'price_forward_value' => '💰 Cena: Pakiet Droga Naprzód',
            // Pricing - Couples
            'price_couple_new_title' => '💑 Nazwa: Pakiet Droga Na Nowo',
            'price_couple_new_desc' => '📝 Opis: Pakiet Droga Na Nowo',
            'price_couple_new_value' => '💰 Cena: Pakiet Droga Na Nowo',
            'price_couple_unity_title' => '💞 Nazwa: Pakiet Pełna Jedność',
            'price_couple_unity_desc' => '📝 Opis: Pakiet Pełna Jedność',
            'price_couple_unity_value' => '💰 Cena: Pakiet Pełna Jedność',
            'price_couple_single_title' => '👫 Nazwa: Konsultacja dla par',
            'price_couple_single_desc' => '📝 Opis: Konsultacja dla par',
            'price_couple_single_value' => '💰 Cena: Konsultacja dla par',
            // Pricing - Other
            'price_leader_title' => '👔 Nazwa: Pakiet Lider',
            'price_leader_desc' => '📝 Opis: Pakiet Lider',
            'price_leader_value' => '💰 Cena: Pakiet Lider',
            'price_long_title' => '🤝 Nazwa: Stała Współpraca',
            'price_long_desc' => '📝 Opis: Stała Współpraca',
            'price_long_value' => '💰 Cena: Stała Współpraca',
            'price_vip_title' => '👑 Nazwa: VIP Premium',
            'price_vip_desc' => '📝 Opis: VIP Premium',
            'price_vip_value' => '💰 Cena: VIP Premium',
        ];
        
        foreach ($nodes as $node) {
            $id = $node->getAttribute('data-cms-id');
            $type = ($node->nodeName === 'img') ? 'image' : 'text';
            $content = ($type === 'image') ? $node->getAttribute('src') : $node->nodeValue; // Get raw text
            
            // Use friendly label if available, otherwise generate from ID
            $label = isset($friendlyLabels[$id]) 
                ? $friendlyLabels[$id] 
                : ucwords(str_replace(['_', '-'], ' ', $id));
            
            // Clean up content (trim)
            $content = trim($content);
            
            $editableElements[] = [
                'id' => $id,
                'type' => $type,
                'content' => $content,
                'label' => $label
            ];
        }
        $pageName = $pages[$currentPage];
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edytor Wizualny - CMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body class="admin-body">
    <nav class="admin-nav">
        <div class="nav-brand">
            <img src="../images/logo.png" alt="CMS" class="nav-logo">
            <span>Visual CMS</span>
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
                <li class="active"><a href="edit-page.php"><i class="fas fa-magic"></i> Edytor Wizualny</a></li>
                <li><a href="images.php"><i class="fas fa-images"></i> Obrazy</a></li>
                <li><a href="blog.php"><i class="fas fa-newspaper"></i> Blog</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> Ustawienia</a></li>
            </ul>
        </aside>

        <main class="admin-main">
            <header class="admin-header">
                <h1>Edytor Wizualny</h1>
                <p class="header-subtitle">Edytuj treść wypełniając proste pola</p>
            </header>

            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <i class="fas fa-info-circle"></i> <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>

            <section class="page-selector">
                <h2>Wybierz stronę:</h2>
                <div class="pages-grid">
                    <?php foreach ($pages as $file => $name): ?>
                    <a href="?page=<?php echo urlencode($file); ?>" 
                       class="page-card <?php echo $currentPage === $file ? 'active' : ''; ?>">
                        <i class="fas fa-file-alt"></i>
                        <span><?php echo htmlspecialchars($name); ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </section>

            <?php if ($currentPage): ?>
                <?php if (empty($editableElements)): ?>
                <div class="empty-state">
                    <i class="fas fa-code-branch"></i>
                    <p>Ta strona nie ma jeszcze oznaczonych elementów do edycji wizualnej.</p>
                    <p class="text-xs text-muted">Dodaj atrybuty <code>data-cms-id</code> w kodzie HTML.</p>
                </div>
                <?php else: ?>
                <section class="editor-section">
                    <h2><i class="fas fa-pen-fancy"></i> Edycja: <?php echo htmlspecialchars($pageName); ?></h2>
                    
                    <form method="POST" class="visual-form">
                        <input type="hidden" name="page" value="<?php echo htmlspecialchars($currentPage); ?>">
                        
                        <?php foreach ($editableElements as $el): ?>
                        <div class="form-group">
                            <label for="<?php echo $el['id']; ?>">
                                <?php if($el['type']==='image'): ?><i class="fas fa-image"></i><?php else: ?><i class="fas fa-font"></i><?php endif; ?>
                                <?php echo htmlspecialchars($el['label']); ?>
                                <span class="text-xs text-muted">(ID: <?php echo $el['id']; ?>)</span>
                            </label>
                            
                            <?php if ($el['type'] === 'image'): ?>
                                <div class="image-input-group">
                                    <input type="text" id="<?php echo $el['id']; ?>" name="<?php echo $el['id']; ?>" 
                                           value="<?php echo htmlspecialchars($el['content']); ?>" class="form-input">
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="window.open('images.php', '_blank')">Galeria</button>
                                </div>
                                <div class="image-preview-mini">
                                    <img src="../<?php echo htmlspecialchars($el['content']); ?>" alt="Podgląd" style="max-height: 100px; margin-top: 5px; border-radius: 4px;">
                                </div>
                            <?php elseif (strlen($el['content']) > 60): ?>
                                <textarea id="<?php echo $el['id']; ?>" name="<?php echo $el['id']; ?>" rows="4" class="form-input"><?php echo htmlspecialchars($el['content']); ?></textarea>
                            <?php else: ?>
                                <input type="text" id="<?php echo $el['id']; ?>" name="<?php echo $el['id']; ?>" 
                                       value="<?php echo htmlspecialchars($el['content']); ?>" class="form-input">
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                        
                        <div class="editor-actions sticky-actions">
                            <button type="submit" class="btn btn-save">Zapisz zmiany</button>
                            <a href="../<?php echo htmlspecialchars($currentPage); ?>" target="_blank" class="btn btn-preview">Podgląd</a>
                        </div>
                    </form>
                </section>
                <?php endif; ?>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
