<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/site_data.php';

$homepageGallery = array_values(array_filter($gallery, static function (array $item): bool {
    return (string) ($item['media_type'] ?? 'image') !== 'video';
}));
$galleryLead = $homepageGallery[0] ?? null;
$galleryHighlights = array_slice($homepageGallery, 1, 4);
$galleryMore = array_slice($homepageGallery, 5, 4);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($schoolName); ?> | Technical Secondary School</title>
    <meta name="description" content="Official website of Mubuga TSS, featuring Software Development and Electrical Technology programs.">
    <link rel="icon" type="image/png" href="/MUBUGA-TSS/assets/images/MUBUGA%20LOGO%20SN.PNG">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&family=Source+Sans+3:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/site.css">
</head>
<body>
    <?php
    $formStatus = (string) ($_GET['form_status'] ?? '');
    $formMessage = (string) ($_GET['form_message'] ?? '');
    $logoPath = (string) ($siteMeta['logo_path'] ?? '');
    // Use fallback to the default logo if no custom logo is set
    if ($logoPath === '') {
        $logoPath = 'assets/images/MUBUGA%20LOGO%20SN.PNG';
    }
    $facebookUrl = (string) ($siteMeta['facebook_url'] ?? '#');
    $instagramUrl = (string) ($siteMeta['instagram_url'] ?? '#');
    $twitterUrl = (string) ($siteMeta['twitter_url'] ?? '#');
    ?>
    <div class="project-loader" data-project-loader>
        <div class="project-loader-card">
            <?php if ($logoPath !== ''): ?>
                <img src="/MUBUGA-TSS/<?php echo htmlspecialchars($logoPath); ?>" alt="<?php echo htmlspecialchars($schoolName); ?> logo" class="project-loader-logo">
            <?php else: ?>
                <img src="/MUBUGA-TSS/assets/images/MUBUGA%20LOGO%20SN.PNG" alt="<?php echo htmlspecialchars($schoolName); ?> logo" class="project-loader-logo">
            <?php endif; ?>
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
                    <span><?php echo htmlspecialchars($contacts[0]['value']); ?></span>
                    <span><?php echo htmlspecialchars($contacts[1]['value']); ?></span>
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
        <?php if ($formStatus !== '' && $formMessage !== ''): ?>
            <div class="container form-feedback-wrap">
                <div class="form-feedback form-feedback-<?php echo htmlspecialchars($formStatus); ?>">
                    <?php echo htmlspecialchars($formMessage); ?>
                </div>
            </div>
        <?php endif; ?>

        <header class="main-header">
            <div class="container nav-wrap">
                <a class="brand" href="/MUBUGA-TSS/" aria-label="Mubuga TSS home">
                    <img src="/MUBUGA-TSS/<?php echo htmlspecialchars($logoPath); ?>" alt="<?php echo htmlspecialchars($schoolName); ?> logo" class="brand-logo">
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
                    <a href="/MUBUGA-TSS/">Home</a>
                    <a href="/MUBUGA-TSS/pages/about.php">About Us</a>
                    <a href="/MUBUGA-TSS/pages/programs.php">Our Programs</a>
                    <a href="/MUBUGA-TSS/pages/facilities.php">Facilities</a>
                    
                    <div class="dropdown">
                        <a href="/MUBUGA-TSS/pages/admissions.php">Admission &#9662;</a>
                        <div class="dropdown-menu">
                            <a href="/MUBUGA-TSS/pages/admissions.php#requirements">Fees &amp; Requirements</a>
                            <a href="/MUBUGA-TSS/pages/admissions.php#registration">Student Registration</a>
                        </div>
                    </div>
                    
                    <a href="/MUBUGA-TSS/pages/team.php">Our Team</a>
                    
                    <div class="dropdown">
                        <a href="/MUBUGA-TSS/pages/news.php">News &#9662;</a>
                        <div class="dropdown-menu">
                            <a href="/MUBUGA-TSS/pages/news.php?type=events">Events</a>
                            <a href="/MUBUGA-TSS/pages/news.php?type=announcements">Announcements</a>
                        </div>
                    </div>
                    
                    <div class="dropdown">
                        <a href="/MUBUGA-TSS/pages/gallery.php">Gallery &#9662;</a>
                        <div class="dropdown-menu">
                            <a href="/MUBUGA-TSS/pages/gallery.php#pictures">Pictures</a>
                            <a href="/MUBUGA-TSS/pages/gallery.php#videos">Videos</a>
                        </div>
                    </div>
                    
                    <a href="/MUBUGA-TSS/pages/contact.php" class="nav-cta">Contacts</a>
                </nav>
            </div>
        </header>

        <main id="main-content">
            <section class="hero" id="home">
                <div class="container hero-grid">
                    <div class="hero-copy" data-hero-slider>
                        <div class="hero-topline">
                        </div>

                        <div class="brand-ribbon">
                            <div class="brand-seal">
                                <img src="/MUBUGA-TSS/<?php echo htmlspecialchars($logoPath); ?>" alt="<?php echo htmlspecialchars($schoolName); ?> emblem" class="brand-seal-logo">
                            </div>
                            <div>
                                <strong><?php echo htmlspecialchars($schoolName); ?></strong>
                                <p>Technical Secondary School</p>
                            </div>
                        </div>

                        <div class="hero-intro">
                            <p class="eyebrow" data-hero-eyebrow><?php echo htmlspecialchars($heroSlides[0]['eyebrow']); ?></p>
                            <h1 data-hero-title><?php echo htmlspecialchars($heroSlides[0]['title']); ?></h1>
                            <p class="hero-text" data-hero-text><?php echo htmlspecialchars($heroSlides[0]['text']); ?></p>
                            <div class="hero-actions">
                                <a href="<?php echo htmlspecialchars($heroSlides[0]['link'] ?? '/MUBUGA-TSS/pages/admissions.php'); ?>" class="button button-primary" data-hero-button><?php echo htmlspecialchars($heroSlides[0]['button']); ?></a>
                                <a href="/MUBUGA-TSS/pages/programs.php" class="button button-secondary">Explore Programs</a>
                            </div>
                        </div>

                        <div class="hero-metrics" aria-label="School overview">
                            <?php foreach ($stats as $stat): ?>
                                <article class="hero-metric">
                                    <strong><?php echo htmlspecialchars($stat['value']); ?></strong>
                                    <span><?php echo htmlspecialchars($stat['label']); ?></span>
                                </article>
                            <?php endforeach; ?>
                        </div>

                        <div class="hero-slider-mock" role="tablist" aria-label="Homepage highlights">
                            <?php foreach ($heroSlides as $index => $slide): ?>
                                <button
                                    type="button"
                                    class="slide-card<?php echo $index === 0 ? ' is-active' : ''; ?>"
                                    data-hero-trigger
                                    data-index="<?php echo $index; ?>"
                                    data-eyebrow="<?php echo htmlspecialchars($slide['eyebrow']); ?>"
                                    data-title="<?php echo htmlspecialchars($slide['title']); ?>"
                                    data-text="<?php echo htmlspecialchars($slide['text']); ?>"
                                    data-button="<?php echo htmlspecialchars($slide['button']); ?>"
                                    data-link="<?php echo htmlspecialchars($slide['link'] ?? '/MUBUGA-TSS/pages/admissions.php'); ?>"
                                    data-image="<?php echo htmlspecialchars($slide['image'] ?? 'assets/images/students.jfif'); ?>"
                                    data-spotlight="<?php echo htmlspecialchars($slide['spotlight'] ?? $slide['eyebrow']); ?>"
                                    aria-selected="<?php echo $index === 0 ? 'true' : 'false'; ?>"
                                >
                                    <p class="card-label"><?php echo htmlspecialchars($slide['eyebrow']); ?></p>
                                    <h3><?php echo htmlspecialchars($slide['title']); ?></h3>
                                    <p><?php echo htmlspecialchars($slide['text']); ?></p>
                                    <span><?php echo htmlspecialchars($slide['button']); ?></span>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="hero-panel">
                        <div class="hero-photo hero-photo-slider">
                            <?php foreach ($heroSlides as $index => $slide): ?>
                                <img
                                    src="/MUBUGA-TSS/<?php echo htmlspecialchars($slide['image'] ?? 'assets/images/students.jfif'); ?>"
                                    alt="<?php echo htmlspecialchars($slide['title']); ?>"
                                    class="hero-slide-image<?php echo $index === 0 ? ' is-active' : ''; ?> photo-viewer"
                                    data-hero-image
                                >
                            <?php endforeach; ?>
                            <div class="hero-photo-controls" aria-label="Hero slide controls">
                                <button type="button" class="hero-photo-control" data-hero-prev aria-label="Previous slide">&#10094;</button>
                                <button type="button" class="hero-photo-control" data-hero-next aria-label="Next slide">&#10095;</button>
                            </div>
                            <div class="hero-photo-overlay">
                                <p class="card-label">Featured View</p>
                                <h2 data-hero-spotlight><?php echo htmlspecialchars($heroSlides[0]['spotlight'] ?? 'Campus and school community'); ?></h2>
                                <a href="/MUBUGA-TSS/pages/gallery.php" class="hero-overlay-link">View school gallery</a>
                            </div>
                        </div>
                        <div class="hero-slider-dots" aria-label="Hero slide pagination">
                            <?php foreach ($heroSlides as $index => $slide): ?>
                                <button
                                    type="button"
                                    class="hero-dot<?php echo $index === 0 ? ' is-active' : ''; ?>"
                                    data-hero-dot
                                    data-index="<?php echo $index; ?>"
                                    aria-label="Show slide <?php echo $index + 1; ?>"
                                    aria-pressed="<?php echo $index === 0 ? 'true' : 'false'; ?>"
                                ></button>
                            <?php endforeach; ?>
                        </div>
                        <div class="hero-panel-grid">
                            <article class="hero-card">
                                <p class="card-label">Quick Links</p>
                                <h2>Explore Mubuga TSS</h2>
                                <div class="hero-shortcuts">
                                    <a href="/MUBUGA-TSS/pages/programs.php" class="shortcut-link">View Programs</a>
                                    <a href="/MUBUGA-TSS/pages/gallery.php" class="shortcut-link">See Gallery</a>
                                    <a href="/MUBUGA-TSS/pages/contact.php" class="shortcut-link">Contact Us</a>
                                </div>
                            </article>
                            <article class="hero-card">
                                <p class="card-label">Why Choose Us</p>
                                <ul class="hero-points">
                                    <?php foreach ($highlights as $highlight): ?>
                                        <li><?php echo htmlspecialchars($highlight); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </article>
                        </div>
                        <div class="hero-badge">Competence. Discipline. Innovation.</div>
                        <p class="hero-support-copy">A practical school environment inspired by the clear, confidence-building hero structure of the reference site, but tailored to Mubuga TSS.</p>
                    </div>
                </div>
            </section>



            <section class="section about" id="about">
                <div class="container section-grid">
                    <div class="about-copy">
                        <p class="eyebrow">Welcome To</p>
                        <h2>Mubuga TSS</h2>
                        <p><?php echo htmlspecialchars($schoolName); ?> is a TVET-focused school committed to practical education, student discipline, and career-ready technical skills. The school is centered on two specialized trades: Software Development and Electrical Technology.</p>
                        <div class="about-facts">
                            <article class="about-fact">
                                <span>School Direction</span>
                                <strong>Competency-based learning</strong>
                            </article>
                            <article class="about-fact">
                                <span>Training Focus</span>
                                <strong>Practical trades for modern careers</strong>
                            </article>
                        </div>
                        <a href="/MUBUGA-TSS/pages/about.php" class="inline-link">Read More</a>
                    </div>
                </div>
            </section>

            <section class="section programs" id="programs">
                <div class="container">
                    <div class="section-heading kha-gallery-heading">
                        <p class="eyebrow">Our Trades</p>
                        <h2>Two training programs built for modern technical careers.</h2>
                    </div>

                    <div class="program-grid">
                        <?php foreach ($programs as $program): ?>
                            <article class="program-card">
                                <?php if (!empty($program['image'])): ?>
                                    <img src="<?php echo htmlspecialchars($program['image']); ?>" alt="<?php echo htmlspecialchars($program['title']); ?>" class="program-image photo-viewer">
                                <?php endif; ?>
                                <div class="program-card-body">
                                    <p class="card-label">Technical Trade</p>
                                    <h3><?php echo htmlspecialchars($program['title']); ?></h3>
                                    <p><?php echo htmlspecialchars($program['summary']); ?></p>
                                    <ul>
                                        <?php foreach ($program['focus'] as $item): ?>
                                            <li><?php echo htmlspecialchars($item); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <a href="<?php echo htmlspecialchars($program['link'] ?? '#admissions'); ?>" class="inline-link">View More</a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>



            <section class="section values">
                <div class="container">
                    <div class="section-heading kha-gallery-heading">
                        <p class="eyebrow"><?php echo htmlspecialchars($schoolName); ?></p>
                        <h2>Our foundation and school values.</h2>
                    </div>

                    <div class="values-grid expanded-values">
                        <article class="value-card">
                            <h3>Our Motto</h3>
                            <p>Excellence in technical education.</p>
                        </article>
                        <article class="value-card">
                            <h3>Vision</h3>
                            <p>To prepare learners for employment, entrepreneurship, and further technical growth through quality training.</p>
                        </article>
                        <article class="value-card">
                            <h3>Mission</h3>
                            <p>To transform student potential into practical competence through focused technical learning and strong character formation.</p>
                        </article>
                        <article class="value-card value-list-card">
                            <h3>Core Values</h3>
                            <ul class="value-list">
                                <?php foreach ($values as $value): ?>
                                    <li><?php echo htmlspecialchars($value); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </article>
                    </div>
                </div>
            </section>

            <section class="section facilities" id="facilities">
                <div class="container">
                    <div class="section-heading">
                        <p class="eyebrow">Facilities and Learning Spaces</p>
                        <h2>An environment built for technical practice and student growth.</h2>
                    </div>

                    <div class="facilities-grid">
                        <?php foreach ($facilities as $facility): ?>
                            <article class="facility-card">
                                <img src="<?php echo htmlspecialchars($facility['image']); ?>" alt="<?php echo htmlspecialchars($facility['title']); ?>" class="section-photo photo-viewer">
                                <h3><?php echo htmlspecialchars($facility['title']); ?></h3>
                                <p><?php echo htmlspecialchars($facility['text']); ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <section class="section gallery-section" id="gallery">
                <div class="container">
                    <div class="section-heading">
                        <p class="eyebrow">School Gallery</p>
                        <h2>A closer look at campus life, workshop practice, and student activity.</h2>
                    </div>

                    <?php if ($homepageGallery !== []): ?>
                        <div class="kha-gallery-grid kha-gallery-grid-home">
                            <?php foreach (array_slice($homepageGallery, 0, 8) as $item): ?>
                                <a href="/MUBUGA-TSS/pages/gallery.php" class="kha-gallery-card kha-gallery-card-home" aria-label="<?php echo htmlspecialchars((string) $item['title']); ?>">
                                    <img src="<?php echo htmlspecialchars((string) $item['image']); ?>" alt="<?php echo htmlspecialchars((string) $item['title']); ?>" class="kha-gallery-image">
                                    <span class="kha-gallery-badge"><?php echo htmlspecialchars((string) ($item['category_label'] ?? 'Campus')); ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <div class="section-more">
                        <a href="/MUBUGA-TSS/pages/gallery.php" class="inline-link">View More Gallery &rarr;</a>
                    </div>
                </div>
            </section>

            <section class="section leadership" id="leadership">
                <div class="container">
                    <div class="section-heading">
                        <p class="eyebrow">Leadership</p>
                        <h2>Meet our school leaders.</h2>
                    </div>

                    <div class="leadership-grid">
                        <?php foreach ($leadership as $member): ?>
                            <article class="leader-card">
                                <img src="<?php echo htmlspecialchars($member['photo'] ?? 'assets/images/master.jpeg'); ?>" alt="<?php echo htmlspecialchars($member['name']); ?>" class="leader-image photo-viewer">
                                <span><?php echo htmlspecialchars($member['role']); ?></span>
                                <h3><?php echo htmlspecialchars($member['name']); ?></h3>
                                <p><?php echo htmlspecialchars($member['text']); ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <section class="section admissions" id="admissions">
                <div class="container admissions-grid">
                    <div class="admissions-copy">
                        <p class="eyebrow">Admissions</p>
                        <h2>Start your journey at Mubuga TSS with a clear path.</h2>
                        <p>Follow the steps below to apply.</p>
                    </div>
                    <div class="admission-steps">
                        <?php foreach ($admissions as $index => $step): ?>
                            <article class="admission-step">
                                <span>0<?php echo $index + 1; ?></span>
                                <div class="admission-step-copy">
                                    <strong>Step <?php echo $index + 1; ?></strong>
                                    <p><?php echo htmlspecialchars($step); ?></p>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <section class="section news" id="news">
                <div class="container">
                    <div class="section-heading">
                        <p class="eyebrow">School Updates</p>
                        <h2>Latest News</h2>
                    </div>

                    <div class="news-grid">
                        <?php foreach ($news as $item): ?>
                            <article class="news-card">
                                <?php if (!empty($item['image'])): ?>
                                    <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="news-image photo-viewer">
                                <?php endif; ?>
                                <div class="news-card-body">
                                    <p class="news-tag">Mubuga TSS</p>
                                    <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                                    <p><?php echo htmlspecialchars($item['text']); ?></p>
                                    <a href="<?php echo htmlspecialchars($item['link'] ?? '#contact'); ?>" class="inline-link">Read More</a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                    <div class="section-more">
                        <a href="/MUBUGA-TSS/pages/news.php" class="inline-link">View More</a>
                    </div>
                </div>
            </section>

            <section class="section cta" id="contact">
                <div class="container contact-grid">
                    <div class="cta-panel">
                        <div class="cta-copy">
                            <p class="eyebrow">Join Mubuga TSS</p>
                            <h2>Ready to study Software Development or Electrical Technology?</h2>
                            <p>Contact the school office for admissions, reporting dates, and program guidance.</p>
                        </div>
                        <div class="cta-actions">
                            <a href="mailto:info@mubugatss.rw" class="button button-primary">Email The School</a>
                            <a href="#home" class="button button-secondary">Back To Top</a>
                        </div>
                    </div>

                    <aside class="contact-card">
                        <p class="eyebrow">For More Info</p>
                        <h3>Reach the school office.</h3>
                        <div class="contact-list">
                            <?php foreach ($contacts as $contact): ?>
                                <div class="contact-item">
                                    <strong><?php echo htmlspecialchars($contact['label']); ?></strong>
                                    <span><?php echo htmlspecialchars($contact['value']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="contact-extra">
                            <div class="contact-extra-item">
                                <strong>Office Hours</strong>
                                <span>Monday to Friday, 7AM - 5PM</span>
                            </div>
                            <div class="contact-extra-item">
                                <strong>Admissions Help</strong>
                                <span>Contact the school office for guidance on reporting and requirements.</span>
                            </div>
                        </div>
                    </aside>
                </div>
            </section>
        </main>

        <div class="floating-actions">
            <a href="/MUBUGA-TSS/pages/admissions.php" class="floating-link">Apply</a>
            <a href="/MUBUGA-TSS/pages/contact.php" class="floating-link floating-link-secondary">Contact</a>
        </div>
        <button type="button" class="back-to-top" aria-label="Back to top">Top</button>
        <footer class="site-footer">
            <div class="container footer-main">
                <div class="footer-topline">
                    <div>
                        <p class="eyebrow">Mubuga TSS</p>
                        <h2>Technical training for a confident future.</h2>
                    </div>
                    <a href="/MUBUGA-TSS/pages/admissions.php" class="button button-primary">Apply Now</a>
                </div>
                <div class="footer-grid">
                    <!-- Brand Section -->
                    <div class="footer-section footer-brand-section">
                        <div class="footer-brand">
                            <img src="/MUBUGA-TSS/<?php echo htmlspecialchars($logoPath); ?>" alt="<?php echo htmlspecialchars($schoolName); ?> logo" class="footer-brand-logo">
                            <div>
                                <strong><?php echo htmlspecialchars($schoolName); ?></strong>
                                <span>Technical Secondary School</span>
                            </div>
                        </div>
                        <p class="footer-description">Focused technical education in Software Development and Electrical Technology.</p>
                        <div class="footer-social">
                            <a href="<?php echo htmlspecialchars($facebookUrl); ?>" class="social-link" aria-label="Follow us on Facebook" title="Facebook">
                                <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            </a>
                            <a href="<?php echo htmlspecialchars($instagramUrl); ?>" class="social-link" aria-label="Follow us on Instagram" title="Instagram">
                                <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm0 21.6c-5.3 0-9.6-4.3-9.6-9.6s4.3-9.6 9.6-9.6 9.6 4.3 9.6 9.6-4.3 9.6-9.6 9.6zm0-15.6c-3.3 0-6 2.7-6 6s2.7 6 6 6 6-2.7 6-6-2.7-6-6-6zm5.6-1.8c0 .79.64 1.43 1.43 1.43s1.43-.64 1.43-1.43-.64-1.43-1.43-1.43-1.43.64-1.43 1.43z"/></svg>
                            </a>
                        </div>
                    </div>

                    <div class="footer-section">
                        <h3 class="footer-heading">Programs</h3>
                        <ul class="footer-links">
                            <li><a href="/MUBUGA-TSS/pages/programs.php">Software Development</a></li>
                            <li><a href="/MUBUGA-TSS/pages/programs.php">Electrical Technology</a></li>
                            <li><a href="/MUBUGA-TSS/pages/programs.php">View All Programs</a></li>
                        </ul>
                    </div>

                    <div class="footer-section">
                        <h3 class="footer-heading">Quick Links</h3>
                        <ul class="footer-links">
                            <li><a href="/MUBUGA-TSS/pages/about.php">About Us</a></li>
                            <li><a href="/MUBUGA-TSS/pages/facilities.php">Facilities</a></li>
                            <li><a href="/MUBUGA-TSS/pages/gallery.php">Gallery</a></li>
                            <li><a href="/MUBUGA-TSS/pages/news.php">News</a></li>
                            <li><a href="/MUBUGA-TSS/pages/contact.php">Contact Us</a></li>
                        </ul>
                    </div>

                    <!-- Contact Info & Newsletter -->
                    <div class="footer-section">
                        <h3 class="footer-heading">Contact</h3>
                        <div class="footer-contact-info">
                            <a href="mailto:<?php echo htmlspecialchars($contacts[0]['value']); ?>" class="contact-link">
                                <span class="contact-icon">📞</span>
                                <span><?php echo htmlspecialchars($contacts[0]['value']); ?></span>
                            </a>
                            <a href="tel:<?php echo htmlspecialchars($contacts[1]['value']); ?>" class="contact-link">
                                <span class="contact-icon">✉️</span>
                                <span><?php echo htmlspecialchars($contacts[1]['value']); ?></span>
                            </a>
                            <div class="contact-link">
                                <span class="contact-icon">📍</span>
                                <span><?php echo htmlspecialchars($contacts[2]['value'] ?? 'Mubuga, Rwanda'); ?></span>
                            </div>
                        </div>
                        
                        <h3 class="footer-heading footer-heading-newsletter">Newsletter</h3>
                        <p class="footer-newsletter-desc">Get updates on admissions and school news.</p>
                        <form class="footer-newsletter-form" method="post" action="/MUBUGA-TSS/handlers/site_forms.php">
                            <input type="hidden" name="form_action" value="newsletter_subscribe">
                            <input type="hidden" name="source" value="footer">
                            <input type="hidden" name="redirect_to" value="/MUBUGA-TSS/">
                            <div class="newsletter-input-group">
                                <input type="email" name="email" placeholder="Your email address" required aria-label="Email address">
                                <button type="submit" aria-label="Subscribe to newsletter">Send</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <div class="container footer-bottom-content">
                    <p class="footer-copyright">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($schoolName); ?>. MADE BY 2P company LTD
                </p>
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
</body>
</html>

