<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jego Słowem - Przebudzenie Relacji</title>
    <meta name="description"
        content="Przebudzenie relacji. Odkryj siłę nowej komunikacji i głębszych więzi. Twoja podróż zaczyna się tutaj.">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="css/style.css">

    <!-- FontAwesome (if needed for icons, based on CSS 'content' usage it might be useful, though pure CSS icons are used in some places) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

    <!-- Header & Navigation -->
    <header class="header">
        <div class="container nav">
            <div class="nav-brand">
                <a href="index.php" class="brand">
                    <img src="images/logo.png" alt="Jego Słowem" class="brand-logo">
                </a>
            </div>

            <button class="mobile-nav-toggle" aria-label="Otwórz menu">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <ul class="nav-links">
                <li><a href="index.php" class="active">Start</a></li>
                <li><a href="o-mnie.html">O Mnie</a></li>
                <li><a href="dla-kogo.html">Dla Kogo</a></li>
                <li><a href="cennik.html">Oferta</a></li>
                <li><a href="blog.html">Blog</a></li>
                <li><a href="faq.html">Opinie</a></li>
                <li><a href="kontakt.html">Kontakt</a></li>
            </ul>

            <div class="nav-actions">
                <a href="kontakt.html" class="btn btn-primary btn-sm">Umów Konsultację</a>
            </div>
        </div>
    </header>

    <main>
        <!-- Hero Section -->
        <section class="hero">
            <div class="container hero-content">
                <div class="hero-grid-layout">
                    <!-- Left Column: Text Content -->
                    <div class="hero-text-col">
                        <span class="hero-eyebrow">COACHING • MENTORING • CONSULTING</span>
                        <h1 class="hero-title" data-cms-id="home_hero_title">
                            PRZEBUDZENIE <span class="hero-highlight">RELACJI</span>
                        </h1>

                        <p class="hero-description" data-cms-id="home_hero_desc">
                            Pomagam przywracać utracone i budować trwałe relacje w oparciu o wartości Słowa Bożego.
                            Przeprowadzam przez drogę prawdy, uzdrowienia i miłości.
                        </p>

                        <div class="hero-actions">
                            <a href="kontakt.html" class="btn btn-hero-primary" data-cms-id="home_hero_btn1">UMÓW SIĘ NA
                                ROZMOWĘ</a>
                            <a href="oferta.html" class="btn btn-hero-outline" data-cms-id="home_hero_btn2">ZOBACZ
                                OFERTĘ</a>
                        </div>

                        <div class="hero-quote-section">
                            <div class="golden-orb"></div>
                            <p class="quote-text" data-cms-id="home_quote_text">OTO CZYNIĘ WSZYSTKO NOWYM.</p>
                            <p class="quote-citation" data-cms-id="home_quote_cite">— AP 21:5</p>
                        </div>
                    </div>

                    <!-- Right Column: Image -->
                    <div class="hero-image-col">
                        <div class="hero-image-wrapper">
                            <img src="images/krzysztof-portrait.jpg" alt="Krzysztof Kozieł" class="hero-profile-img">
                            <div class="hero-img-glow"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="hero-trust-bar">
                <div class="trust-item">
                    <i class="fas fa-shield-alt"></i> <span>Poufność</span>
                </div>
                <div class="trust-item">
                    <i class="fas fa-video"></i> <span>Online / Stacjonarnie</span>
                </div>
                <div class="trust-item">
                    <i class="fas fa-award"></i> <span>15+ lat doświadczenia</span>
                </div>
            </div>
        </section>
    </main>

    <!-- Extended Footer -->
    <footer class="footer-extended">
        <div class="container footer-extended-grid">
            <div class="footer-col footer-brand-col">
                <a href="index.php" class="footer-logo">
                    <img src="images/logo.png" alt="Jego Słowem" class="footer-brand-logo">
                </a>
                <p class="footer-tagline">Towarzyszę ludziom w odzyskiwaniu prawdy o sobie i budowaniu głębokich relacji
                    w świetle Bożego Słowa.</p>
            </div>
            <div class="footer-col">
                <h4>NAWIGACJA</h4>
                <ul>
                    <li><a href="o-mnie.html">O mnie</a></li>
                    <li><a href="dla-kogo.html">Dla kogo</a></li>
                    <li><a href="cennik.html">Oferta</a></li>
                    <li><a href="faq.html">Opinie</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>KONTAKT</h4>
                <p><i class="fas fa-phone"></i> 530 441 448</p>
                <p><i class="fas fa-envelope"></i> kontakt@jegoslowem.pl</p>
                <div class="footer-social">
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
        <div class="container footer-bottom">
            <p class="copyright">&copy; 2025 Jego Słowem. Wszelkie prawa zastrzeżone.</p>
            <a href="#" class="privacy-link">Polityka Prywatności</a>
        </div>
    </footer>

    <script src="js/main.js" defer></script>
</body>

</html>