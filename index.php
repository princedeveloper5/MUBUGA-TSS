<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/site_data.php';
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
    $facebookUrl = (string) ($siteMeta['facebook_url'] ?? '#');
    $instagramUrl = (string) ($siteMeta['instagram_url'] ?? '#');
    ?>
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
                    <a href="<?php echo htmlspecialchars($facebookUrl); ?>">Facebook</a>
                    <a href="<?php echo htmlspecialchars($instagramUrl); ?>">Instagram</a>
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
                    <a href="/MUBUGA-TSS/">Home</a>
                    <a href="/MUBUGA-TSS/pages/about.php">About Us</a>
                    <a href="/MUBUGA-TSS/pages/programs.php">Our Programs</a>
                    <a href="/MUBUGA-TSS/pages/facilities.php">Facilities</a>
                    <a href="/MUBUGA-TSS/pages/admissions.php">Admission</a>
                    <a href="/MUBUGA-TSS/pages/team.php">Our Team</a>
                    <a href="/MUBUGA-TSS/pages/news.php">News</a>
                    <a href="/MUBUGA-TSS/pages/contact.php" class="nav-cta">Contacts</a>
                </nav>
            </div>
        </header>

        <main id="main-content">
            <section class="hero" id="home">
                <div class="container hero-grid">
                    <div class="hero-copy" data-hero-slider>
                        <div class="brand-ribbon">
                            <div class="brand-seal">
                                <?php if ($logoPath !== ''): ?>
                                    <img src="/MUBUGA-TSS/<?php echo htmlspecialchars($logoPath); ?>" alt="<?php echo htmlspecialchars($schoolName); ?> emblem" class="brand-seal-logo">
                                <?php else: ?>
                                    <span>MT</span>
                                <?php endif; ?>
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
                                <a href="#admissions" class="button button-primary" data-hero-button><?php echo htmlspecialchars($heroSlides[0]['button']); ?></a>
                                <a href="#about" class="button button-secondary">Learn About Us</a>
                            </div>
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
                                    data-image="<?php echo htmlspecialchars($slide['image'] ?? 'assets/images/mb1.jfif'); ?>"
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
                                    src="<?php echo htmlspecialchars($slide['image'] ?? 'assets/images/mb1.jfif'); ?>"
                                    alt="<?php echo htmlspecialchars($slide['title']); ?>"
                                    class="hero-slide-image<?php echo $index === 0 ? ' is-active' : ''; ?>"
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
                            <div class="hero-card hero-card-accent">
                                <p class="card-label">Core Trades</p>
                                <h2>Software Development</h2>
                                <p>Programming, web systems, digital projects, and modern technical thinking.</p>
                            </div>
                            <div class="hero-card">
                                <p class="card-label">Core Trades</p>
                                <h2>Electrical Technology</h2>
                                <p>Electrical installation, maintenance, workshop practice, and safe technical execution.</p>
                            </div>
                        </div>
                        <div class="hero-badge">Competence. Discipline. Innovation.</div>
                        <ul class="hero-points">
                            <?php foreach ($highlights as $highlight): ?>
                                <li><?php echo htmlspecialchars($highlight); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </section>

            <section class="section featured-strip">
                <div class="container featured-grid">
                    <?php foreach ($featuredStories as $story): ?>
                        <article class="featured-card">
                            <img src="<?php echo htmlspecialchars($story['image']); ?>" alt="<?php echo htmlspecialchars($story['title']); ?>" class="featured-image">
                            <div class="featured-copy">
                                <h3><?php echo htmlspecialchars($story['title']); ?></h3>
                                <p><?php echo htmlspecialchars($story['text']); ?></p>
                                <a href="<?php echo htmlspecialchars($story['link']); ?>" class="inline-link">Learn More</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="section quick-info">
                <div class="container quick-info-grid">
                    <article class="quick-info-card">
                        <span>School Type</span>
                        <strong>TVET / Technical Secondary School</strong>
                        <p>Structured technical education with practical training and clear learner pathways.</p>
                    </article>
                    <article class="quick-info-card">
                        <span>Main Trades</span>
                        <strong>Software Development and Electrical Technology</strong>
                        <p>Focused technical departments designed around current skills and hands-on learning.</p>
                    </article>
                    <article class="quick-info-card">
                        <span>Learning Style</span>
                        <strong>Competency-based and practical</strong>
                        <p>Students build confidence through projects, workshop practice, and guided instruction.</p>
                    </article>
                </div>
            </section>

            <section class="section institutional-overview">
                <div class="container institutional-grid">
                    <div class="welcome-panel">
                        <p class="eyebrow">Welcome Message</p>
                        <h2>A school environment built to form capable and responsible technicians.</h2>
                        <p><?php echo htmlspecialchars($leadership[0]['name'] ?? 'Mubuga TSS Administration'); ?> welcomes learners and families to a school community that values practical competence, discipline, and a strong sense of purpose.</p>
                        <p><?php echo htmlspecialchars($leadership[0]['text'] ?? 'Mubuga TSS is committed to giving learners strong technical foundations, discipline, and confidence for the future.'); ?></p>
                        <div class="welcome-signature">
                            <img src="<?php echo htmlspecialchars($leadership[0]['photo'] ?? 'assets/images/master.jpeg'); ?>" alt="<?php echo htmlspecialchars($leadership[0]['name'] ?? 'Mubuga TSS Administration'); ?>" class="welcome-avatar">
                            <div>
                                <strong><?php echo htmlspecialchars($leadership[0]['name'] ?? 'Mubuga TSS Administration'); ?></strong>
                                <span><?php echo htmlspecialchars($leadership[0]['role'] ?? 'School Leadership'); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="institutional-side">
                        <div class="institution-card-grid">
                            <?php foreach ($institutionCards as $card): ?>
                                <article class="institution-card">
                                    <span><?php echo htmlspecialchars($card['label']); ?></span>
                                    <h3><?php echo htmlspecialchars($card['title']); ?></h3>
                                    <p><?php echo htmlspecialchars($card['text']); ?></p>
                                </article>
                            <?php endforeach; ?>
                        </div>
                        <article class="school-profile-card">
                            <p class="eyebrow">School Profile</p>
                            <ul class="profile-list">
                                <?php foreach ($welcomeHighlights as $highlight): ?>
                                    <li><?php echo htmlspecialchars($highlight); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </article>
                    </div>
                </div>
            </section>

            <section class="stats">
                <div class="container stats-grid">
                    <?php foreach ($stats as $stat): ?>
                        <article class="stat-card">
                            <strong><?php echo htmlspecialchars($stat['value']); ?></strong>
                            <span><?php echo htmlspecialchars($stat['label']); ?></span>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="section about" id="about">
                <div class="container section-grid">
                    <div class="about-copy">
                        <p class="eyebrow">About The School</p>
                        <h2>Welcome to Mubuga TSS.</h2>
                        <p><?php echo htmlspecialchars($schoolName); ?> is a TVET-focused school committed to practical education, student discipline, and career-ready technical skills. The school is centered on two specialized trades: Software Development and Electrical Technology.</p>
                        <p>We aim to help learners grow into capable professionals and responsible citizens by connecting theory, workshop practice, digital projects, and strong school values in one focused environment.</p>
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
                        <a href="#programs" class="inline-link">Read more about our programs</a>
                    </div>
                    <div class="feature-stack">
                        <article class="feature-card feature-card-image">
                            <img src="<?php echo htmlspecialchars($leadership[0]['photo'] ?? 'assets/images/master.jpeg'); ?>" alt="Mubuga TSS school representative">
                            <div class="feature-card-body">
                                <h3>School Leadership Message</h3>
                                <p>Mubuga TSS is committed to giving learners strong technical foundations, discipline, and confidence for the future.</p>
                            </div>
                        </article>
                        <article class="feature-card">
                            <h3>Our Motto</h3>
                            <p>Excellence in technical education.</p>
                        </article>
                        <article class="feature-card">
                            <h3>Vision</h3>
                            <p>To become a respected center of technical excellence that prepares students for opportunity, service, and innovation.</p>
                        </article>
                        <article class="feature-card">
                            <h3>Mission</h3>
                            <p>To equip learners with practical skills, discipline, and creativity through focused vocational education.</p>
                        </article>
                    </div>
                </div>
            </section>

            <section class="section programs" id="programs">
                <div class="container">
                    <div class="section-heading">
                        <p class="eyebrow">Our Trades</p>
                        <h2>Two training programs built for modern technical careers.</h2>
                        <p>Following the same content rhythm as the reference site, this section presents Mubuga TSS programs as focused pathways with clear practical outcomes.</p>
                    </div>

                    <div class="program-grid">
                        <?php foreach ($programs as $program): ?>
                            <article class="program-card">
                                <?php if (!empty($program['image'])): ?>
                                    <img src="<?php echo htmlspecialchars($program['image']); ?>" alt="<?php echo htmlspecialchars($program['title']); ?>" class="program-image">
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

            <section class="section info-banner">
                <div class="container info-banner-panel">
                    <div>
                        <p class="eyebrow">Need More Information?</p>
                        <h2>Excellence in education.</h2>
                    </div>
                    <a href="#admissions" class="button button-primary">Register Now</a>
                </div>
            </section>

            <section class="section values">
                <div class="container">
                    <div class="section-heading">
                        <p class="eyebrow"><?php echo htmlspecialchars($schoolName); ?></p>
                        <h2>Our motto, vision, mission, and core values.</h2>
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
                        <p>Like the reference school site, this section shows how Mubuga TSS combines classroom learning with spaces that support real technical experience.</p>
                    </div>

                    <div class="facilities-grid">
                        <?php foreach ($facilities as $facility): ?>
                            <article class="facility-card">
                                <img src="<?php echo htmlspecialchars($facility['image']); ?>" alt="<?php echo htmlspecialchars($facility['title']); ?>" class="section-photo">
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
                        <h2>A more visual look at school life and technical training.</h2>
                        <p>This gallery-style block helps the page feel closer to the reference website even before real photos are added.</p>
                    </div>

                    <div class="gallery-grid">
                        <?php foreach ($gallery as $item): ?>
                            <article class="gallery-card">
                                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="gallery-image">
                                <div class="gallery-copy">
                                    <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                                    <p><?php echo htmlspecialchars($item['text']); ?></p>
                                </div>
                            </article>
                        <?php endforeach; ?>
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
                                <img src="<?php echo htmlspecialchars($member['photo'] ?? 'assets/images/master.jpeg'); ?>" alt="<?php echo htmlspecialchars($member['name']); ?>" class="leader-image">
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
                        <p>We designed this section to make the site feel more complete and school-ready. You can later replace these steps with your exact admission requirements and dates.</p>
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
                        <h2>Latest news.</h2>
                    </div>

                    <div class="news-grid">
                        <?php foreach ($news as $item): ?>
                            <article class="news-card">
                                <?php if (!empty($item['image'])): ?>
                                    <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="news-image">
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
                        <a href="#contact" class="inline-link">View More</a>
                    </div>
                </div>
            </section>

            <section class="section cta" id="contact">
                <div class="container contact-grid">
                    <div class="cta-panel">
                        <div class="cta-copy">
                            <p class="eyebrow">Join Mubuga TSS</p>
                            <h2>Ready to study Software Development or Electrical Technology?</h2>
                            <p>We can continue by adding your real school contacts, photos, leadership names, location details, and application information.</p>
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
                        <p class="contact-note">Replace the placeholder phone number and location with the exact school details when you are ready.</p>
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
            <div class="container footer-grid">
                <div class="footer-column footer-brand-column">
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
                <div class="footer-column">
                    <p><strong>Programs</strong></p>
                    <p>Software Development</p>
                    <p>Electrical Technology</p>
                </div>
                <div class="footer-column">
                    <p><strong>Quick Links</strong></p>
                    <p><a href="#about">About us</a></p>
                    <p><a href="#leadership">Staff</a></p>
                    <p><a href="#facilities">Facilities</a></p>
                    <p><a href="#gallery">Gallery</a></p>
                    <p><a href="#admissions">Fees &amp; Requirements</a></p>
                    <p><a href="#contact">Contacts</a></p>
                </div>
                <div class="footer-column">
                    <p><strong>Mailing List</strong></p>
                    <p>Sign up for our mailing list to get latest updates and offers.</p>
                    <form class="mailing-form" method="post" action="/MUBUGA-TSS/handlers/site_forms.php">
                        <input type="hidden" name="form_action" value="newsletter_subscribe">
                        <input type="hidden" name="source" value="footer">
                        <input type="hidden" name="redirect_to" value="/MUBUGA-TSS/">
                        <input type="email" name="email" placeholder="Your email address" required>
                        <button type="submit">Subscribe</button>
                    </form>
                </div>
            </div>
        </footer>
    </div>

    <script src="assets/js/site.js"></script>
</body>
</html>
