<?php

declare(strict_types=1);

function renderSiteHeader(string $pageTitle, string $schoolName, array $contacts, string $active = ''): void
{
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle . ' | ' . $schoolName); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($schoolName); ?> official website.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&family=Source+Sans+3:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/MUBUGA-TSS/assets/css/site.css">
</head>
<body>
    <div class="site-shell">
        <header class="topbar">
            <div class="container topbar-inner">
                <div class="topbar-meta">
                    <span><?php echo htmlspecialchars($contacts[0]['value'] ?? ''); ?></span>
                    <span><?php echo htmlspecialchars($contacts[1]['value'] ?? ''); ?></span>
                    <span>Open: 7AM - 5PM</span>
                </div>
                <div class="topbar-links">
                    <a href="#">Facebook</a>
                    <a href="#">Instagram</a>
                    <a href="/MUBUGA-TSS/admin/">Login</a>
                </div>
            </div>
        </header>

        <header class="main-header">
            <div class="container nav-wrap">
                <a class="brand" href="/MUBUGA-TSS/" aria-label="<?php echo htmlspecialchars($schoolName); ?> home">
                    <span class="brand-mark">MT</span>
                    <span class="brand-text">
                        <strong><?php echo htmlspecialchars($schoolName); ?></strong>
                        <small>Technical Secondary School</small>
                    </span>
                </a>

                <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>

                <nav class="site-nav" id="site-nav">
                    <a href="/MUBUGA-TSS/"<?php echo $active === 'home' ? ' class="is-current"' : ''; ?>>Home</a>
                    <a href="/MUBUGA-TSS/pages/about.php"<?php echo $active === 'about' ? ' class="is-current"' : ''; ?>>About Us</a>
                    <a href="/MUBUGA-TSS/pages/programs.php"<?php echo $active === 'programs' ? ' class="is-current"' : ''; ?>>Our Programs</a>
                    <a href="/MUBUGA-TSS/pages/facilities.php"<?php echo $active === 'facilities' ? ' class="is-current"' : ''; ?>>Facilities</a>
                    <a href="/MUBUGA-TSS/pages/admissions.php"<?php echo $active === 'admissions' ? ' class="is-current"' : ''; ?>>Admission</a>
                    <a href="/MUBUGA-TSS/pages/team.php"<?php echo $active === 'team' ? ' class="is-current"' : ''; ?>>Our Team</a>
                    <a href="/MUBUGA-TSS/pages/news.php"<?php echo $active === 'news' ? ' class="is-current"' : ''; ?>>News</a>
                    <a href="/MUBUGA-TSS/pages/gallery.php"<?php echo $active === 'gallery' ? ' class="is-current"' : ''; ?>>Gallery</a>
                    <a href="/MUBUGA-TSS/pages/contact.php" class="nav-cta">Contacts</a>
                </nav>
            </div>
        </header>
<?php
}

function renderInnerHero(string $eyebrow, string $title, string $text, string $image): void
{
    ?>
        <section class="inner-hero">
            <div class="container inner-hero-grid">
                <div>
                    <p class="eyebrow"><?php echo htmlspecialchars($eyebrow); ?></p>
                    <h1 class="inner-title"><?php echo htmlspecialchars($title); ?></h1>
                    <p class="hero-text"><?php echo htmlspecialchars($text); ?></p>
                </div>
                <div class="inner-hero-photo">
                    <img src="/MUBUGA-TSS/<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($title); ?>">
                </div>
            </div>
        </section>
<?php
}

function renderSiteFooter(string $schoolName): void
{
    ?>
        <footer class="site-footer">
            <div class="container footer-grid">
                <div>
                    <div class="footer-brand">
                        <span class="brand-mark">MT</span>
                        <strong><?php echo htmlspecialchars($schoolName); ?></strong>
                    </div>
                    <p>Technical Secondary School focused on Software Development and Electrical Technology.</p>
                    <p>Mubuga, Rwanda</p>
                </div>
                <div>
                    <p><strong>Useful Links</strong></p>
                    <p><a href="/MUBUGA-TSS/pages/about.php">About us</a></p>
                    <p><a href="/MUBUGA-TSS/pages/team.php">Staff</a></p>
                    <p><a href="/MUBUGA-TSS/pages/facilities.php">Facilities</a></p>
                    <p><a href="/MUBUGA-TSS/pages/admissions.php">Fees &amp; Requirements</a></p>
                    <p><a href="/MUBUGA-TSS/pages/contact.php">Contacts</a></p>
                </div>
                <div>
                    <p><strong>Mailing List</strong></p>
                    <p>Sign up for our mailing list to get latest updates and offers.</p>
                    <form class="mailing-form">
                        <input type="email" placeholder="Your email address">
                        <button type="button">Subscribe</button>
                    </form>
                </div>
            </div>
        </footer>
    </div>
    <script src="/MUBUGA-TSS/assets/js/site.js"></script>
</body>
</html>
<?php
}
