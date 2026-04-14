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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&family=Source+Sans+3:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/site.css">
</head>
<body>
    <div class="site-shell">
        <header class="topbar">
            <div class="container topbar-inner">
                <div class="topbar-meta">
                    <span><?php echo htmlspecialchars($contacts[0]['value']); ?></span>
                    <span><?php echo htmlspecialchars($contacts[1]['value']); ?></span>
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
                <a class="brand" href="/MUBUGA-TSS/" aria-label="Mubuga TSS home">
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

        <main>
            <section class="hero" id="home">
                <div class="container hero-grid">
                    <div class="hero-copy">
                        <div class="brand-ribbon">
                            <div class="brand-seal">
                                <span>MT</span>
                            </div>
                            <div>
                                <strong><?php echo htmlspecialchars($schoolName); ?></strong>
                                <p>Technical Secondary School</p>
                            </div>
                        </div>

                        <div class="hero-intro">
                            <p class="eyebrow"><?php echo htmlspecialchars($heroSlides[0]['eyebrow']); ?></p>
                            <h1><?php echo htmlspecialchars($heroSlides[0]['title']); ?></h1>
                            <p class="hero-text"><?php echo htmlspecialchars($tagline); ?></p>
                            <div class="hero-actions">
                                <a href="#admissions" class="button button-primary"><?php echo htmlspecialchars($heroSlides[0]['button']); ?></a>
                                <a href="#about" class="button button-secondary">Learn About Us</a>
                            </div>
                        </div>

                        <div class="hero-slider-mock">
                            <?php foreach ($heroSlides as $slide): ?>
                                <article class="slide-card">
                                    <p class="card-label"><?php echo htmlspecialchars($slide['eyebrow']); ?></p>
                                    <h3><?php echo htmlspecialchars($slide['title']); ?></h3>
                                    <p><?php echo htmlspecialchars($slide['text']); ?></p>
                                    <a href="#admissions"><?php echo htmlspecialchars($slide['button']); ?></a>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="hero-panel">
                        <div class="hero-photo">
                            <img src="assets/images/mb1.jfif" alt="Mubuga TSS students and staff in front of the school building">
                        </div>
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
                    </article>
                    <article class="quick-info-card">
                        <span>Main Trades</span>
                        <strong>Software Development and Electrical Technology</strong>
                    </article>
                    <article class="quick-info-card">
                        <span>Learning Style</span>
                        <strong>Competency-based and practical</strong>
                    </article>
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
                    <div>
                        <p class="eyebrow">About The School</p>
                        <h2>Welcome to Mubuga TSS.</h2>
                        <p><?php echo htmlspecialchars($schoolName); ?> is a TVET-focused school committed to practical education, student discipline, and career-ready technical skills. The school is centered on two specialized trades: Software Development and Electrical Technology.</p>
                        <p>We aim to help learners grow into capable professionals and responsible citizens by connecting theory, workshop practice, digital projects, and strong school values in one focused environment.</p>
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
                                <h3><?php echo htmlspecialchars($program['title']); ?></h3>
                                <p><?php echo htmlspecialchars($program['summary']); ?></p>
                                <ul>
                                    <?php foreach ($program['focus'] as $item): ?>
                                        <li><?php echo htmlspecialchars($item); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <a href="<?php echo htmlspecialchars($program['link'] ?? '#admissions'); ?>" class="inline-link">View More</a>
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
                                <p><?php echo htmlspecialchars($step); ?></p>
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
                                <p class="news-tag">Mubuga TSS</p>
                                <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                                <p><?php echo htmlspecialchars($item['text']); ?></p>
                                <a href="<?php echo htmlspecialchars($item['link'] ?? '#contact'); ?>" class="inline-link">Read More</a>
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
                        <div>
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
                        <p class="contact-note">Replace the placeholder phone number and location with the exact school details when you are ready.</p>
                    </aside>
                </div>
            </section>
        </main>

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
                    <p><strong>Programs</strong></p>
                    <p>Software Development</p>
                    <p>Electrical Technology</p>
                </div>
                <div>
                    <p><strong>Quick Links</strong></p>
                    <p><a href="#about">About us</a></p>
                    <p><a href="#leadership">Staff</a></p>
                    <p><a href="#facilities">Facilities</a></p>
                    <p><a href="#gallery">Gallery</a></p>
                    <p><a href="#admissions">Fees &amp; Requirements</a></p>
                    <p><a href="#contact">Contacts</a></p>
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

    <script src="assets/js/site.js"></script>
</body>
</html>
