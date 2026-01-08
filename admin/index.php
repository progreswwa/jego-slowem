<?php
session_start();

// If already logged in, redirect to editor
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: /index.php?edit=1');
    exit;
}

$error = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Simple auth - hardcoded for stability
    $valid_username = 'admin';
    $valid_password = 'jegoslowem2026';
    
    if ($username === $valid_username && $password === $valid_password) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = $username;
        header('Location: /index.php?edit=1');
        exit;
    } else {
        $error = 'Nieprawidłowy login lub hasło';
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administratora - Jego Słowem</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: #0a0a0a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #f5f5f5;
        }
        
        .login-container {
            background: #1a1a1e;
            border-radius: 16px;
            padding: 3rem;
            width: 100%;
            max-width: 400px;
            border: 1px solid rgba(201, 167, 83, 0.2);
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
        }
        
        .login-logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-logo h1 {
            font-size: 1.5rem;
            color: #C9A753;
            font-weight: 600;
            letter-spacing: 0.1em;
        }
        
        .login-logo p {
            color: #888;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #aaa;
            font-size: 0.85rem;
        }
        
        .form-group input {
            width: 100%;
            padding: 14px 16px;
            background: #0f0f11;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            color: #f5f5f5;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #C9A753;
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #DCCA8B 0%, #C9A753 50%, #8F722E 100%);
            border: none;
            border-radius: 8px;
            color: #111;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(201, 167, 83, 0.3);
        }
        
        .error-msg {
            background: rgba(220, 53, 69, 0.15);
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: #ff6b6b;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
            font-size: 0.9rem;
        }
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            color: #666;
            text-decoration: none;
            font-size: 0.85rem;
        }
        
        .back-link:hover {
            color: #C9A753;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-logo">
            <h1>JEGO SŁOWEM</h1>
            <p>Panel Administratora</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error-msg"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="username">Login</label>
                <input type="text" id="username" name="username" required autocomplete="username">
            </div>
            
            <div class="form-group">
                <label for="password">Hasło</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>
            
            <button type="submit" class="btn-login">Zaloguj się</button>
        </form>
        
        <a href="/" class="back-link">← Wróć do strony</a>
    </div>
</body>
</html>
