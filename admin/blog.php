<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: /admin/index.php');
    exit;
}

$_SESSION['login_time'] = time();

$message = '';
$messageType = '';
$blogFile = __DIR__ . '/../data/blog.json';

// Load blog data
$blogData = file_exists($blogFile) ? json_decode(file_get_contents($blogFile), true) : ['posts' => []];

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_post'])) {
    $postId = $_POST['delete_post'];
    foreach ($blogData['posts'] as $key => $post) {
        if ($post['id'] === $postId) {
            unset($blogData['posts'][$key]);
            $blogData['posts'] = array_values($blogData['posts']);
            file_put_contents($blogFile, json_encode($blogData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $message = 'Post został usunięty.';
            $messageType = 'success';
            break;
        }
    }
}

// Handle save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_post'])) {
    $postId = $_POST['post_id'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $image = trim($_POST['image'] ?? '');
    $published = isset($_POST['published']) ? true : false;
    
    if ($title && $content) {
        if ($postId) {
            // Update existing post
            foreach ($blogData['posts'] as &$post) {
                if ($post['id'] === $postId) {
                    $post['title'] = $title;
                    $post['content'] = $content;
                    $post['excerpt'] = $excerpt;
                    $post['image'] = $image;
                    $post['published'] = $published;
                    $post['updated_at'] = date('Y-m-d H:i:s');
                    break;
                }
            }
            $message = 'Post został zaktualizowany!';
        } else {
            // Create new post
            $newPost = [
                'id' => uniqid('post_'),
                'title' => $title,
                'content' => $content,
                'excerpt' => $excerpt,
                'image' => $image,
                'published' => $published,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            array_unshift($blogData['posts'], $newPost);
            $message = 'Nowy post został utworzony!';
        }
        
        file_put_contents($blogFile, json_encode($blogData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $messageType = 'success';
        
        // Reload data
        $blogData = json_decode(file_get_contents($blogFile), true);
    } else {
        $message = 'Tytuł i treść są wymagane.';
        $messageType = 'error';
    }
}

// Get action
$action = $_GET['action'] ?? 'list';
$editPost = null;

if ($action === 'edit' && isset($_GET['id'])) {
    foreach ($blogData['posts'] as $post) {
        if ($post['id'] === $_GET['id']) {
            $editPost = $post;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zarządzanie Blogiem - Panel Administracyjny</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <!-- TinyMCE -->
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: '#content',
            height: 500,
            menubar: false,
            plugins: 'autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount',
            toolbar: 'undo redo | formatselect | ' +
            'bold italic backcolor | alignleft aligncenter ' +
            'alignright alignjustify | bullist numlist outdent indent | ' +
            'removeformat | help',
            content_style: 'body { font-family:Poppins,Helvetica,Arial,sans-serif; font-size:14px }',
            skin: 'oxide-dark',
            content_css: 'dark'
        });
    </script>
    <style>
        /* Image Picker Modal */
        .modal {
            display: none; 
            position: fixed; 
            z-index: 1000; 
            left: 0;
            top: 0;
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgba(0,0,0,0.8); 
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
        }
        .modal-header {
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;
        }
        .close-modal {
            color: var(--text-muted); font-size: 28px; font-weight: bold; cursor: pointer;
        }
        .close-modal:hover { color: var(--gold-primary); }
        .image-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px;
        }
        .image-item {
            border: 2px solid transparent; cursor: pointer; overflow: hidden; border-radius: 8px; position: relative;
        }
        .image-item:hover { border-color: var(--gold-primary); }
        .image-item img { width: 100%; height: 100px; object-fit: cover; display: block; }
        .image-name {
            font-size: 12px; padding: 5px; text-align: center; color: var(--text-muted); background: rgba(0,0,0,0.5);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
    </style>
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
                <li class="active">
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
                <h1>Zarządzanie Blogiem</h1>
                <p class="header-subtitle">Twórz i edytuj wpisy na blogu</p>
            </header>

            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>

            <?php if ($action === 'new' || $action === 'edit'): ?>
            <!-- POST EDITOR -->
            <section class="blog-editor">
                <div class="section-header">
                    <h2><i class="fas fa-pen"></i> <?php echo $editPost ? 'Edytuj post' : 'Nowy post'; ?></h2>
                    <a href="blog.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Powrót do listy
                    </a>
                </div>
                
                <form method="POST" class="post-form">
                    <input type="hidden" name="save_post" value="1">
                    <input type="hidden" name="post_id" value="<?php echo htmlspecialchars($editPost['id'] ?? ''); ?>">
                    
                    <div class="form-group">
                        <label for="title">
                            <i class="fas fa-heading"></i> Tytuł *
                        </label>
                        <input type="text" id="title" name="title" required
                               value="<?php echo htmlspecialchars($editPost['title'] ?? ''); ?>"
                               placeholder="Wprowadź tytuł posta">
                    </div>
                    
                    <div class="form-group">
                        <label for="excerpt">
                            <i class="fas fa-align-left"></i> Skrócony opis
                        </label>
                        <textarea id="excerpt" name="excerpt" rows="3"
                                  placeholder="Krótki opis wyświetlany na liście postów"><?php echo htmlspecialchars($editPost['excerpt'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="content">
                            <i class="fas fa-file-alt"></i> Treść *
                        </label>
                        <textarea id="content" name="content" rows="15" required
                                  placeholder="Pełna treść posta (obsługuje HTML)"><?php echo htmlspecialchars($editPost['content'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="image">
                            <i class="fas fa-image"></i> Obraz (ścieżka)
                        </label>
                        <div style="display: flex; gap: 10px;">
                            <input type="text" id="image" name="image"
                                   value="<?php echo htmlspecialchars($editPost['image'] ?? ''); ?>"
                                   placeholder="np. images/post-image.jpg" style="flex: 1;">
                            <button type="button" class="btn btn-secondary" onclick="openImageModal()">
                                <i class="fas fa-images"></i> Wybierz
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group form-checkbox">
                        <label>
                            <input type="checkbox" name="published" 
                                   <?php echo ($editPost['published'] ?? false) ? 'checked' : ''; ?>>
                            <span><i class="fas fa-globe"></i> Opublikowany</span>
                        </label>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-save">
                            <i class="fas fa-save"></i> Zapisz post
                        </button>
                    </div>
                </form>
            </section>
            
            <?php else: ?>
            <!-- POST LIST -->
            <section class="blog-list">
                <div class="section-header">
                    <h2><i class="fas fa-list"></i> Wszystkie posty</h2>
                    <a href="blog.php?action=new" class="btn btn-add">
                        <i class="fas fa-plus"></i> Nowy post
                    </a>
                </div>
                
                <?php if (empty($blogData['posts'])): ?>
                <div class="empty-state">
                    <i class="fas fa-newspaper"></i>
                    <p>Brak postów na blogu</p>
                    <a href="blog.php?action=new" class="btn btn-primary">Utwórz pierwszy post</a>
                </div>
                <?php else: ?>
                <div class="posts-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Tytuł</th>
                                <th>Status</th>
                                <th>Data</th>
                                <th>Akcje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($blogData['posts'] as $post): ?>
                            <tr>
                                <td class="post-title">
                                    <?php echo htmlspecialchars($post['title']); ?>
                                </td>
                                <td>
                                    <?php if ($post['published']): ?>
                                    <span class="status-published"><i class="fas fa-check"></i> Opublikowany</span>
                                    <?php else: ?>
                                    <span class="status-draft"><i class="fas fa-edit"></i> Szkic</span>
                                    <?php endif; ?>
                                </td>
                                <td class="post-date">
                                    <?php echo date('d.m.Y', strtotime($post['created_at'])); ?>
                                </td>
                                <td class="post-actions">
                                    <a href="blog.php?action=edit&id=<?php echo urlencode($post['id']); ?>" class="btn-edit" title="Edytuj">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" style="display:inline" onsubmit="return confirm('Czy na pewno usunąć ten post?');">
                                        <input type="hidden" name="delete_post" value="<?php echo htmlspecialchars($post['id']); ?>">
                                        <button type="submit" class="btn-delete" title="Usuń">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </section>
            <?php endif; ?>
        </main>
    </div>

    <!-- IMAGE MODAL -->
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
        function openImageModal() { document.getElementById('imageModal').style.display = 'block'; }
        function closeImageModal() { document.getElementById('imageModal').style.display = 'none'; }
        
        function selectImage(path) {
            document.getElementById('image').value = path;
            closeImageModal();
        }
        
        // Close on click outside
        window.onclick = function(event) {
            if (event.target == document.getElementById('imageModal')) {
                closeImageModal();
            }
        }
    </script>
    <script src="js/admin.js"></script>
</body>
</html>
