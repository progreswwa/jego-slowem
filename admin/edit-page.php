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
    <style>
        /* Live Preview Split View */
        .split-view {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            min-height: 600px;
        }
        .split-view .editor-panel {
            max-height: 80vh;
            overflow-y: auto;
            padding-right: 10px;
        }
        .split-view .preview-panel {
            position: sticky;
            top: 80px;
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            overflow: hidden;
            background: #fff;
        }
        .split-view .preview-panel iframe {
            width: 100%;
            height: 80vh;
            border: none;
        }
        .preview-header {
            background: var(--bg-card);
            padding: 10px 15px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .preview-header span {
            font-size: 0.85rem;
            color: var(--text-muted);
        }
        /* Image Picker Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0; top: 0;
            width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.85);
            backdrop-filter: blur(5px);
        }
        .modal-content {
            background-color: var(--bg-card);
            margin: 5% auto;
            padding: 20px;
            border: 1px solid var(--gold-primary);
            border-radius: var(--radius);
            width: 80%;
            max-width: 900px;
            max-height: 80vh;
            overflow-y: auto;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .modal-header h3 { margin: 0; }
        .close-modal {
            color: var(--text-muted);
            font-size: 28px;
            cursor: pointer;
        }
        .close-modal:hover { color: var(--gold-primary); }
        .image-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 12px;
        }
        .image-item {
            border: 2px solid transparent;
            cursor: pointer;
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.2s;
        }
        .image-item:hover {
            border-color: var(--gold-primary);
            transform: scale(1.03);
        }
        .image-item img {
            width: 100%;
            height: 90px;
            object-fit: cover;
        }
        .image-name {
            font-size: 11px;
            padding: 5px;
            text-align: center;
            color: var(--text-muted);
            background: rgba(0,0,0,0.5);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        /* Current editing indicator */
        .current-page-badge {
            background: var(--gold-gradient);
            color: #111;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        @media (max-width: 1024px) {
            .split-view {
                grid-template-columns: 1fr;
            }
            .preview-panel {
                display: none;
            }
        }
        
        /* Accordion Sections */
        .accordion-section {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            margin-bottom: 12px;
            overflow: hidden;
        }
        .accordion-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            cursor: pointer;
            background: var(--bg-card);
            transition: all 0.2s;
            font-weight: 500;
        }
        .accordion-header:hover {
            background: rgba(201, 167, 83, 0.1);
        }
        .accordion-header i {
            transition: transform 0.3s;
            color: var(--gold-primary);
        }
        .accordion-section.open .accordion-header i {
            transform: rotate(180deg);
        }
        .accordion-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            padding: 0 20px;
        }
        .accordion-section.open .accordion-content {
            max-height: 2000px;
            padding: 15px 20px;
        }
    </style>
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
                    <div class="section-header" style="margin-bottom: 20px;">
                        <h2><i class="fas fa-pen-fancy"></i> Edycja: <?php echo htmlspecialchars($pageName); ?></h2>
                        <span class="current-page-badge"><i class="fas fa-file-alt"></i> <?php echo htmlspecialchars($currentPage); ?></span>
                    </div>

                    <div class="split-view">
                    <!-- EDITOR PANEL -->
                    <div class="editor-panel">
                    <form method="POST" class="visual-form">
                        <input type="hidden" name="page" value="<?php echo htmlspecialchars($currentPage); ?>">
                        
                        <?php 
                        // Group elements by section
                        $sections = [];
                        foreach ($editableElements as $el) {
                            $parts = explode('_', $el['id']);
                            $sectionKey = $parts[0] ?? 'other';
                            if (!isset($sections[$sectionKey])) {
                                $sections[$sectionKey] = ['name' => '', 'items' => []];
                            }
                            $sections[$sectionKey]['items'][] = $el;
                        }
                        
                        // Section names
                        $sectionNames = [
                            'home' => '🏠 Strona Główna',
                            'about' => '👤 O Mnie',
                            'target' => '🎯 Dla Kogo',
                            'price' => '💰 Cennik',
                            'other' => '📝 Inne'
                        ];
                        
                        $sectionIndex = 0;
                        foreach ($sections as $sectionKey => $section): 
                            $sectionName = $sectionNames[$sectionKey] ?? ucfirst($sectionKey);
                            $isOpen = $sectionIndex === 0; // First section open by default
                        ?>
                        <div class="accordion-section <?php echo $isOpen ? 'open' : ''; ?>">
                            <div class="accordion-header" onclick="toggleAccordion(this)">
                                <span><?php echo $sectionName; ?> (<?php echo count($section['items']); ?> pól)</span>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="accordion-content">
                        <?php foreach ($section['items'] as $el): ?>
                        <div class="form-group">
                            <label for="<?php echo $el['id']; ?>">
                                <?php if($el['type']==='image'): ?><i class="fas fa-image"></i><?php else: ?><i class="fas fa-font"></i><?php endif; ?>
                                <?php echo htmlspecialchars($el['label']); ?>
                                <span class="text-xs text-muted">(ID: <?php echo $el['id']; ?>)</span>
                            </label>
                            
                            <?php if ($el['type'] === 'image'): ?>
                                <div class="image-input-group" style="display:flex; gap:10px;">
                                    <input type="text" id="<?php echo $el['id']; ?>" name="<?php echo $el['id']; ?>" 
                                           value="<?php echo htmlspecialchars($el['content']); ?>" class="form-input" style="flex:1;">
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="openImagePicker('<?php echo $el['id']; ?>')">
                                        <i class="fas fa-images"></i> Wybierz
                                    </button>
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
                        </div><!-- end form-group -->
                        <?php endforeach; ?>
                            </div><!-- end accordion-content -->
                        </div><!-- end accordion-section -->
                        <?php 
                            $sectionIndex++;
                        endforeach; 
                        ?>
                        <div class="editor-actions sticky-actions">
                            <button type="submit" class="btn btn-save">Zapisz zmiany</button>
                            <a href="../<?php echo htmlspecialchars($currentPage); ?>" target="_blank" class="btn btn-preview">Podgląd</a>
                        </div>
                    </form>
                    </div><!-- end editor-panel -->

                    <!-- LIVE PREVIEW PANEL -->
                    <div class="preview-panel">
                        <div class="preview-header">
                            <span><i class="fas fa-eye"></i> Podgląd na żywo</span>
                            <a href="../<?php echo htmlspecialchars($currentPage); ?>" target="_blank" class="btn btn-secondary btn-sm">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                        <iframe id="livePreview" src="../<?php echo htmlspecialchars($currentPage); ?>"></iframe>
                    </div>
                    </div><!-- end split-view -->
                </section>
                <?php endif; ?>
            <?php endif; ?>
        </main>
    </div>

    <!-- IMAGE PICKER MODAL -->
    <div id="imageModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-images"></i> Wybierz obraz z biblioteki</h3>
                <span class="close-modal" onclick="closeImageModal()">&times;</span>
            </div>
            <div class="image-grid">
                <?php
                $existingImages = glob(__DIR__ . '/../images/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
                if ($existingImages) {
                    foreach ($existingImages as $img) {
                        $basename = basename($img);
                        echo '<div class="image-item" onclick="selectImage(\'images/' . $basename . '\')">
                                <img src="../images/' . $basename . '" loading="lazy" alt="' . $basename . '">
                                <div class="image-name">' . $basename . '</div>
                              </div>';
                    }
                } else {
                    echo '<p style="grid-column: 1/-1; text-align: center; padding: 20px;">Brak zdjęć w folderze images/</p>';
                }
                ?>
            </div>
        </div>
    </div>

    <script>
        // Image Picker
        let currentImageField = null;
        
        function openImagePicker(fieldId) {
            currentImageField = fieldId;
            document.getElementById('imageModal').style.display = 'block';
        }
        
        function closeImageModal() {
            document.getElementById('imageModal').style.display = 'none';
            currentImageField = null;
        }
        
        function selectImage(path) {
            if (currentImageField) {
                document.getElementById(currentImageField).value = path;
                // Update preview
                const previewImg = document.getElementById(currentImageField).parentElement.nextElementSibling?.querySelector('img');
                if (previewImg) {
                    previewImg.src = '../' + path;
                }
            }
            closeImageModal();
        }
        
        // Close modal on click outside
        window.onclick = function(event) {
            const modal = document.getElementById('imageModal');
            if (event.target === modal) {
                closeImageModal();
            }
        }
        
        // Live Preview Refresh
        function refreshPreview() {
            const iframe = document.getElementById('livePreview');
            if (iframe) {
                iframe.src = iframe.src;
            }
        }
        
        // Accordion Toggle
        function toggleAccordion(header) {
            const section = header.parentElement;
            section.classList.toggle('open');
        }
    </script>
    <script src="js/admin.js"></script>
</body>
</html>
