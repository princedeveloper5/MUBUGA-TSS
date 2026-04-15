<?php

declare(strict_types=1);

function renderSiteHeader(string $pageTitle, string $schoolName, array $contacts, string $active = ''): void
{
    global $siteMeta;
    $formStatus = (string) ($_GET['form_status'] ?? '');
    $formMessage = (string) ($_GET['form_message'] ?? '');
    $logoPath = (string) ($siteMeta['logo_path'] ?? '');
    $facebookUrl = (string) ($siteMeta['facebook_url'] ?? '#');
    $instagramUrl = (string) ($siteMeta['instagram_url'] ?? '#');
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle . ' | ' . $schoolName); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($schoolName); ?> official website.">
    <link rel="icon" type="image/png" href="/MUBUGA-TSS/assets/images/MUBUGA%20LOGO%20SN.PNG">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&family=Source+Sans+3:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/MUBUGA-TSS/assets/css/site.css">
</head>
<body>
    <a href="#main-content" class="skip-link">Skip to content</a>
    <div class="site-shell">
        <div class="scroll-progress" aria-hidden="true">
            <span class="scroll-progress-bar"></span>
        </div>
        <header class="topbar">
            <div class="container topbar-inner">
                <div class="topbar-meta">
                    <span><?php echo htmlspecialchars($contacts[0]['value'] ?? ''); ?></span>
                    <span><?php echo htmlspecialchars($contacts[1]['value'] ?? ''); ?></span>
                    <span>Open: 7AM - 5PM</span>
                </div>
                <div class="topbar-links">
                    <a href="<?php echo htmlspecialchars($facebookUrl); ?>">Facebook</a>
                    <a href="<?php echo htmlspecialchars($instagramUrl); ?>">Instagram</a>
                    <a href="/MUBUGA-TSS/admin/">Login</a>
                </div>
            </div>
        </header>

        <header class="main-header">
            <div class="container nav-wrap">
                <a class="brand" href="/MUBUGA-TSS/" aria-label="<?php echo htmlspecialchars($schoolName); ?> home">
                    <?php if ($logoPath !== ''): ?>
                        <img src="/MUBUGA-TSS/<?php echo htmlspecialchars($logoPath); ?>" alt="<?php echo htmlspecialchars($schoolName); ?> logo" class="brand-logo">
                    <?php else: ?>
                        <span class="brand-mark">MT</span>
                    <?php endif; ?>
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
        <?php if ($formStatus !== '' && $formMessage !== ''): ?>
            <div class="container form-feedback-wrap">
                <div class="form-feedback form-feedback-<?php echo htmlspecialchars($formStatus); ?>">
                    <?php echo htmlspecialchars($formMessage); ?>
                </div>
            </div>
        <?php endif; ?>
<?php
}

function renderInnerHero(string $eyebrow, string $title, string $text, string $image): void
{
    ?>
        <section class="inner-hero">
            <div class="container inner-hero-grid">
                <div class="inner-hero-copy">
                    <p class="eyebrow"><?php echo htmlspecialchars($eyebrow); ?></p>
                    <h1 class="inner-title"><?php echo htmlspecialchars($title); ?></h1>
                    <p class="hero-text"><?php echo htmlspecialchars($text); ?></p>
                    <div class="inner-hero-badges">
                        <span>Technical Excellence</span>
                        <span>Student Growth</span>
                        <span>Practical Learning</span>
                    </div>
                </div>
                <div class="inner-hero-photo">
                    <img src="/MUBUGA-TSS/<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($title); ?>">
                    <div class="inner-hero-photo-badge">
                        <strong><?php echo htmlspecialchars($title); ?></strong>
                        <span><?php echo htmlspecialchars($eyebrow); ?></span>
                    </div>
                </div>
            </div>
        </section>
<?php
}

function renderSiteFooter(string $schoolName): void
{
    global $siteMeta;
    $logoPath = (string) ($siteMeta['logo_path'] ?? '');
    ?>
        <div class="floating-actions">
            <a href="/MUBUGA-TSS/pages/admissions.php" class="floating-link">Apply</a>
            <a href="/MUBUGA-TSS/pages/contact.php" class="floating-link floating-link-secondary">Contact</a>
        </div>
        <button type="button" class="back-to-top" aria-label="Back to top">Top</button>
        <footer class="site-footer">
            <div class="container footer-grid">
                <div>
                    <div class="footer-brand">
                        <?php if ($logoPath !== ''): ?>
                            <img src="/MUBUGA-TSS/<?php echo htmlspecialchars($logoPath); ?>" alt="<?php echo htmlspecialchars($schoolName); ?> logo" class="brand-logo">
                        <?php else: ?>
                            <span class="brand-mark">MT</span>
                        <?php endif; ?>
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
                    <form class="mailing-form" method="post" action="/MUBUGA-TSS/handlers/site_forms.php">
                        <input type="hidden" name="form_action" value="newsletter_subscribe">
                        <input type="hidden" name="source" value="footer">
                        <input type="hidden" name="redirect_to" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/MUBUGA-TSS/'); ?>">
                        <input type="email" name="email" placeholder="Your email address" required>
                        <button type="submit">Subscribe</button>
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
