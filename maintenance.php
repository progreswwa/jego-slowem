<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jego Słowem - Strona w Budowie</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #050505;
            --gold-primary: #C9A753;
            --text-main: #FFFFFF;
        }
        body {
            margin: 0;
            padding: 0;
            background-color: var(--bg-dark);
            color: var(--text-main);
            font-family: 'Poppins', sans-serif;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            background-image: radial-gradient(circle at center, #111 0%, #000 100%);
        }
        .container {
            padding: 2rem;
            max-width: 600px;
        }
        h1 {
            font-family: 'Cinzel', serif;
            color: var(--gold-primary);
            font-size: 2.5rem;
            margin-bottom: 1rem;
            letter-spacing: 0.1em;
        }
        p {
            font-size: 1.1rem;
            color: #aaa;
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        .logo {
            width: 150px;
            margin-bottom: 2rem;
            filter: brightness(1.2);
        }
        .admin-link {
            position: fixed;
            bottom: 20px;
            right: 20px;
            color: #222;
            text-decoration: none;
            font-size: 0.8rem;
            transition: color 0.3s;
        }
        .admin-link:hover {
            color: var(--gold-primary);
        }
        /* Loader/Orb */
        .orb {
            width: 40px;
            height: 40px;
            background: radial-gradient(circle, var(--gold-primary) 0%, transparent 70%);
            border-radius: 50%;
            margin: 0 auto 2rem;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(0.95); opacity: 0.5; }
            50% { transform: scale(1.2); opacity: 1; }
            100% { transform: scale(0.95); opacity: 0.5; }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Optional Logo if available, else text -->
        <!-- <img src="images/logo.png" alt="Logo" class="logo"> -->
        <div class="orb"></div>
        <h1>STRONA W BUDOWIE</h1>
        <p>Pracujemy nad nową odsłoną serwisu.<br>Zapraszamy wkrótce.</p>
    </div>
    <a href="admin/" class="admin-link">Admin</a>
</body>
</html>
