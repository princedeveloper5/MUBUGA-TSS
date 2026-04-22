<?php

declare(strict_types=1);

function siteBaseUrl(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
    return $scheme . '://' . $host;
}

function siteAbsoluteUrl(string $path): string
{
    $normalizedPath = '/' . ltrim($path, '/');
    return siteBaseUrl() . $normalizedPath;
}

function renderSeoMetaTags(string $schoolName, string $pageTitle, array $seo = []): void
{
    $requestUri = (string) ($_SERVER['REQUEST_URI'] ?? '/MUBUGA-TSS/');
    $canonicalPath = strtok($requestUri, '?');
    if (!is_string($canonicalPath) || $canonicalPath === '') {
        $canonicalPath = '/MUBUGA-TSS/';
    }

    $description = trim((string) ($seo['description'] ?? ($schoolName . ' official website.')));
    if ($description === '') {
        $description = $schoolName . ' official website.';
    }

    $type = trim((string) ($seo['type'] ?? 'website'));
    if ($type === '') {
        $type = 'website';
    }

    $canonicalUrl = trim((string) ($seo['canonical_url'] ?? siteAbsoluteUrl($canonicalPath)));
    if ($canonicalUrl === '') {
        $canonicalUrl = siteAbsoluteUrl($canonicalPath);
    }

    $imagePath = trim((string) ($seo['image'] ?? ''));
    $imageUrl = '';
    if ($imagePath !== '') {
        $imageUrl = siteAbsoluteUrl('/MUBUGA-TSS/' . ltrim($imagePath, '/'));
    }

    echo '<meta name="description" content="' . htmlspecialchars($description, ENT_QUOTES, 'UTF-8') . '">' . PHP_EOL;
    echo '<link rel="canonical" href="' . htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8') . '">' . PHP_EOL;
    echo '<meta property="og:title" content="' . htmlspecialchars($pageTitle . ' | ' . $schoolName, ENT_QUOTES, 'UTF-8') . '">' . PHP_EOL;
    echo '<meta property="og:description" content="' . htmlspecialchars($description, ENT_QUOTES, 'UTF-8') . '">' . PHP_EOL;
    echo '<meta property="og:type" content="' . htmlspecialchars($type, ENT_QUOTES, 'UTF-8') . '">' . PHP_EOL;
    echo '<meta property="og:url" content="' . htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8') . '">' . PHP_EOL;
    if ($imageUrl !== '') {
        echo '<meta property="og:image" content="' . htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8') . '">' . PHP_EOL;
        echo '<meta name="twitter:image" content="' . htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8') . '">' . PHP_EOL;
    }
    echo '<meta name="twitter:card" content="summary_large_image">' . PHP_EOL;
    echo '<meta name="twitter:title" content="' . htmlspecialchars($pageTitle . ' | ' . $schoolName, ENT_QUOTES, 'UTF-8') . '">' . PHP_EOL;
    echo '<meta name="twitter:description" content="' . htmlspecialchars($description, ENT_QUOTES, 'UTF-8') . '">' . PHP_EOL;
}

function renderSiteHeader(string $pageTitle, string $schoolName, array $contacts, string $active = '', array $seo = []): void
{
    global $siteMeta;
    $formStatus = (string) ($_GET['form_status'] ?? '');
    $formMessage = (string) ($_GET['form_message'] ?? '');
    $logoPath = (string) ($siteMeta['logo_path'] ?? '');
    // Use fallback to the default logo if no custom logo is set
    if ($logoPath === '') {
        $logoPath = 'assets/images/MUBUGA%20LOGO%20SN.PNG';
    }
    $logoSize = max(32, min(140, (int) ($siteMeta['logo_size'] ?? 52)));
    $facebookUrl = (string) ($siteMeta['facebook_url'] ?? '#');
    $instagramUrl = (string) ($siteMeta['instagram_url'] ?? '#');
    $twitterUrl = (string) ($siteMeta['twitter_url'] ?? '#');
    $themeMode = (string) ($siteMeta['theme_mode'] ?? 'light');
    $homepageNotice = trim((string) ($siteMeta['homepage_notice'] ?? ''));
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle . ' | ' . $schoolName); ?></title>
    <?php renderSeoMetaTags($schoolName, $pageTitle, $seo); ?>
    <link rel="icon" type="image/png" href="/MUBUGA-TSS/assets/images/MUBUGA%20LOGO%20SN.PNG">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&family=Source+Sans+3:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/MUBUGA-TSS/assets/css/site.css">
    <link rel="stylesheet" href="/MUBUGA-TSS/assets/css/photo-viewer.css">
</head>
<body class="site-theme-<?php echo htmlspecialchars($themeMode); ?>" data-site-theme="<?php echo htmlspecialchars($themeMode); ?>">
    <div class="project-loader" data-project-loader>
        <div class="project-loader-card">
            <img src="/MUBUGA-TSS/<?php echo htmlspecialchars($logoPath); ?>" alt="<?php echo htmlspecialchars($schoolName); ?> logo" class="project-loader-logo">
            <div class="project-spinner" aria-hidden="true">
                <span></span><span></span><span></span><span></span><span></span><span></span>
                <span></span><span></span><span></span><span></span><span></span><span></span>
            </div>
            <strong>Loading <?php echo htmlspecialchars($schoolName); ?></strong>
        </div>
    </div>
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
                    <a href="<?php echo htmlspecialchars($facebookUrl); ?>" class="topbar-social-link" aria-label="Follow us on Facebook" title="Facebook">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                    <a href="<?php echo htmlspecialchars($instagramUrl); ?>" class="topbar-social-link" aria-label="Follow us on Instagram" title="Instagram">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm0 21.6c-5.3 0-9.6-4.3-9.6-9.6s4.3-9.6 9.6-9.6 9.6 4.3 9.6 9.6-4.3 9.6-9.6 9.6zm0-15.6c-3.3 0-6 2.7-6 6s2.7 6 6 6 6-2.7 6-6-2.7-6-6-6zm5.6-1.8c0 .79.64 1.43 1.43 1.43s1.43-.64 1.43-1.43-.64-1.43-1.43-1.43-1.43.64-1.43 1.43z"/></svg>
                    </a>
                    <a href="<?php echo htmlspecialchars($twitterUrl); ?>" class="topbar-social-link" aria-label="Follow us on Twitter" title="Twitter">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24h-6.6l-5.165-6.75-5.97 6.75h-3.315l7.73-8.835L.42 2.25h6.75l4.678 6.017L17.474 2.25zM16.6 20.47h1.832L7.06 3.88H5.063L16.6 20.47z"/></svg>
                    </a>
                    <a href="/MUBUGA-TSS/admin/">Login</a>
                </div>
            </div>
        </header>

        <header class="main-header">
            <div class="container nav-wrap">
                <a class="brand" href="/MUBUGA-TSS/" aria-label="<?php echo htmlspecialchars($schoolName); ?> home">
                    <img src="/MUBUGA-TSS/<?php echo htmlspecialchars($logoPath); ?>" alt="<?php echo htmlspecialchars($schoolName); ?> logo" class="brand-logo" style="width: <?php echo $logoSize; ?>px; height: auto;">
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
                    
                    <div class="dropdown">
                        <a href="/MUBUGA-TSS/pages/admissions.php"<?php echo $active === 'admissions' ? ' class="is-current"' : ''; ?>>Admission &#9662;</a>
                        <div class="dropdown-menu">
                            <a href="/MUBUGA-TSS/pages/admissions.php">Admission Overview</a>
                            <a href="/MUBUGA-TSS/pages/fees.php">Fees &amp; Requirements</a>
                            <a href="/MUBUGA-TSS/pages/registration.php">Student Registration</a>
                    </div>
                    </div>
                    
                    <a href="/MUBUGA-TSS/pages/team.php"<?php echo $active === 'team' ? ' class="is-current"' : ''; ?>>Our Team</a>
                    
                    <div class="dropdown">
                        <a href="/MUBUGA-TSS/pages/news.php"<?php echo $active === 'news' ? ' class="is-current"' : ''; ?>>News &#9662;</a>
                        <div class="dropdown-menu">
                            <a href="/MUBUGA-TSS/pages/news.php">All News</a>
                            <a href="/MUBUGA-TSS/pages/events.php">Events</a>
                            <a href="/MUBUGA-TSS/pages/announcements.php">Announcements</a>
                    </div>
                    </div>
                    
                    <div class="dropdown">
                        <a href="/MUBUGA-TSS/pages/gallery.php"<?php echo $active === 'gallery' ? ' class="is-current"' : ''; ?>>Gallery &#9662;</a>
                        <div class="dropdown-menu">
                            <a href="/MUBUGA-TSS/pages/gallery.php#pictures">Pictures</a>
                            <a href="/MUBUGA-TSS/pages/gallery.php#videos">Videos</a>
                        </div>
                    </div>
                    
                    <a href="/MUBUGA-TSS/pages/contact.php" class="nav-cta">Contacts</a>
                </nav>
            </div>
        </header>
        <?php if ($homepageNotice !== ''): ?>
            <div class="site-notice-bar">
                <div class="container">
                    <strong>School Notice:</strong>
                    <span><?php echo htmlspecialchars($homepageNotice); ?></span>
                </div>
            </div>
        <?php endif; ?>
        <?php if ($formStatus !== '' && $formMessage !== ''): ?>
            <div class="container form-feedback-wrap">
                <div class="form-feedback form-feedback-<?php echo htmlspecialchars($formStatus); ?>">
                    <?php echo htmlspecialchars($formMessage); ?>
                </div>
            </div>
        <?php endif; ?>
<?php
}

function renderInnerHero(string $eyebrow, string $title, string $text, string $image, bool $showSeal = true): void
{
    global $siteMeta, $schoolName;
    $logoPath = (string) ($siteMeta['logo_path'] ?? '');
    // Use fallback to the default logo if no custom logo is set
    if ($logoPath === '') {
        $logoPath = 'assets/images/MUBUGA%20LOGO%20SN.PNG';
    }
    $logoSize = max(32, min(140, (int) ($siteMeta['logo_size'] ?? 52)));
    ?>
        <section class="inner-hero">
            <div class="container inner-hero-grid">
                <div class="inner-hero-copy">
                    <?php if ($showSeal): ?>
                        <div class="inner-hero-seal">
                            <img src="/MUBUGA-TSS/<?php echo htmlspecialchars($logoPath); ?>" alt="<?php echo htmlspecialchars($schoolName); ?> emblem" class="inner-hero-seal-logo" style="width: <?php echo min(96, $logoSize); ?>px; height: auto;">
                        </div>
                    <?php endif; ?>
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
    global $siteMeta, $contacts;
    $logoPath = (string) ($siteMeta['logo_path'] ?? '');
    // Use fallback to the default logo if no custom logo is set
    if ($logoPath === '') {
        $logoPath = 'assets/images/MUBUGA%20LOGO%20SN.PNG';
    }
    $logoSize = max(32, min(140, (int) ($siteMeta['logo_size'] ?? 52)));
    $facebookUrl = (string) ($siteMeta['facebook_url'] ?? '#');
    $instagramUrl = (string) ($siteMeta['instagram_url'] ?? '#');
    $emailAddress = (string) ($contacts[0]['value'] ?? '');
    $phoneNumber = (string) ($contacts[1]['value'] ?? '');
    $locationText = (string) ($contacts[2]['value'] ?? 'Mubuga, Rwanda');
    ?>
        <div class="floating-actions">
            <button type="button" class="back-to-top" aria-label="Back to top">Top</button>
            <a href="/MUBUGA-TSS/pages/admissions.php" class="floating-link">Apply</a>
            <a href="/MUBUGA-TSS/pages/contact.php" class="floating-link floating-link-secondary">Contact</a>
        </div>
        <footer class="site-footer">
            <div class="container footer-main">
                <div class="footer-topline">
                    <div>
                        <p class="eyebrow">Mubuga TSS</p>
                        <h2>Short path. Big future.</h2>
                    </div>
                    <a href="/MUBUGA-TSS/pages/admissions.php" class="button button-primary">Apply Now</a>
                </div>
                <div class="footer-grid">
                    <!-- Brand & Contact Combined -->
                    <div class="footer-section">
                        <div class="footer-brand">
                            <img src="/MUBUGA-TSS/<?php echo htmlspecialchars($logoPath); ?>" alt="<?php echo htmlspecialchars($schoolName); ?> logo" class="footer-brand-logo" style="width: <?php echo min(72, $logoSize); ?>px; height: auto;">
                            <div>
                                <strong><?php echo htmlspecialchars($schoolName); ?></strong>
                                <span>Technical Secondary School</span>
                            </div>
                        </div>
                        <p class="footer-description">Practical training in software and electrical technology.</p>
                        <div class="footer-social">
                            <a href="<?php echo htmlspecialchars($facebookUrl); ?>" class="social-link" aria-label="Follow us on Facebook" title="Facebook">
                                <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            </a>
                            <a href="<?php echo htmlspecialchars($instagramUrl); ?>" class="social-link" aria-label="Follow us on Instagram" title="Instagram">
                                <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm0 21.6c-5.3 0-9.6-4.3-9.6-9.6s4.3-9.6 9.6-9.6 9.6 4.3 9.6 9.6-4.3 9.6-9.6 9.6zm0-15.6c-3.3 0-6 2.7-6 6s2.7 6 6 6 6-2.7 6-6-2.7-6-6-6zm5.6-1.8c0 .79.64 1.43 1.43 1.43s1.43-.64 1.43-1.43-.64-1.43-1.43-1.43-1.43.64-1.43 1.43z"/></svg>
                            </a>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div class="footer-section">
                        <h3 class="footer-heading">Quick Links</h3>
                        <ul class="footer-links">
                            <li><a href="/MUBUGA-TSS/pages/about.php">About Us</a></li>
                            <li><a href="/MUBUGA-TSS/pages/programs.php">Our Programs</a></li>
                            <li><a href="/MUBUGA-TSS/pages/facilities.php">Facilities</a></li>
                            <li><a href="/MUBUGA-TSS/pages/team.php">Our Team</a></li>
                            <li><a href="/MUBUGA-TSS/pages/gallery.php">Gallery</a></li>
                            <li><a href="/MUBUGA-TSS/pages/news.php">News &amp; Updates</a></li>
                            <li><a href="/MUBUGA-TSS/pages/events.php">Events</a></li>
                            <li><a href="/MUBUGA-TSS/pages/announcements.php">Announcements</a></li>
                        </ul>
                    </div>

                    <!-- Admissions -->
                    <div class="footer-section">
                        <h3 class="footer-heading">Programs</h3>
                        <ul class="footer-links">
                            <li><a href="/MUBUGA-TSS/pages/programs.php">Software Development</a></li>
                            <li><a href="/MUBUGA-TSS/pages/programs.php">Electrical Technology</a></li>
                            <li><a href="/MUBUGA-TSS/pages/programs.php">View All Programs</a></li>
                            <li><a href="/MUBUGA-TSS/pages/fees.php">Fees &amp; Requirements</a></li>
                            <li><a href="/MUBUGA-TSS/pages/registration.php">Student Registration</a></li>
                        </ul>
                        <p class="footer-newsletter-desc">Get admissions and school updates.</p>
                        <form class="footer-newsletter-form" method="post" action="/MUBUGA-TSS/handlers/site_forms.php">
                            <input type="hidden" name="form_action" value="newsletter_subscribe">
                            <input type="hidden" name="source" value="footer">
                            <input type="hidden" name="redirect_to" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/MUBUGA-TSS/'); ?>">
                            <div class="newsletter-input-group">
                                <input type="email" name="email" placeholder="Your email address" required aria-label="Email address">
                                <button type="submit" aria-label="Subscribe to newsletter">Send</button>
                            </div>
                        </form>
                    </div>

                    <!-- Contact Info & Newsletter -->
                    <div class="footer-section">
                        <h3 class="footer-heading">Contact</h3>
                        <div class="footer-contact-info">
                            <?php if ($phoneNumber): ?>
                                <a href="tel:<?php echo htmlspecialchars($phoneNumber); ?>" class="contact-link">
                                    <span class="contact-icon">📞</span>
                                    <span><?php echo htmlspecialchars($phoneNumber); ?></span>
                                </a>
                            <?php endif; ?>
                            <?php if ($emailAddress): ?>
                                <a href="mailto:<?php echo htmlspecialchars($emailAddress); ?>" class="contact-link">
                                    <span class="contact-icon">✉️</span>
                                    <span><?php echo htmlspecialchars($emailAddress); ?></span>
                                </a>
                            <?php endif; ?>
                            <div class="contact-link">
                                <span class="contact-icon">📍</span>
                                <span><?php echo htmlspecialchars($locationText); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="footer-bottom">
                <div class="container footer-bottom-content">
                    <p class="footer-copyright" style="text-align: center;">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($schoolName); ?>. All rights reserved.</p>
                    <div class="footer-bottom-links">
                        <a href="/MUBUGA-TSS/pages/admissions.php" class="footer-bottom-link">Admissions</a>
                        <a href="/MUBUGA-TSS/pages/gallery.php" class="footer-bottom-link">Gallery</a>
                        <a href="/MUBUGA-TSS/pages/contact.php" class="footer-bottom-link">Contacts</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
    <script src="/MUBUGA-TSS/assets/js/site.js"></script>
    <script src="/MUBUGA-TSS/assets/js/photo-viewer.js"></script>
</body>
</html>
<?php
}


