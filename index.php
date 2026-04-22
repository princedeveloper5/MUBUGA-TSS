<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/site_data.php';
require_once __DIR__ . '/includes/site_layout.php';

$homepageGallery = array_values(array_filter($gallery, static function (array $item): bool {
    return (string) ($item['media_type'] ?? 'image') !== 'video';
}));

renderSiteHeader('Home', $schoolName, $contacts, 'home', [
    'description' => 'Official website of Mubuga TSS, featuring Software Development and Electrical Technology programs.',
    'image' => 'assets/images/student in practical.jpeg',
]);
?>
<main id="main-content">
    <section class="hero" id="home">
        <div class="container hero-grid">
            <div class="hero-copy" data-hero-slider>
                <div class="brand-ribbon">
                    <div class="brand-seal">
                        <img src="/MUBUGA-TSS/<?php echo htmlspecialchars((string) ($siteMeta['logo_path'] ?? 'assets/images/MUBUGA%20LOGO%20SN.PNG')); ?>" alt="<?php echo htmlspecialchars($schoolName); ?> emblem" class="brand-seal-logo">
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
                        <a href="/MUBUGA-TSS/pages/admissions.php" class="button button-primary">Register</a>
                        <a href="/MUBUGA-TSS/pages/programs.php" class="button button-secondary">Explore Programs</a>
                    </div>
                </div>

                <div class="hero-statement">
                    <p>Practical learning, workshop discipline, and modern technical training are at the center of the Mubuga TSS experience.</p>
                </div>

                <div class="hero-metrics hero-metrics-compact" aria-label="School overview">
                    <?php foreach ($stats as $stat): ?>
                        <article class="hero-metric">
                            <strong><?php echo htmlspecialchars($stat['value']); ?></strong>
                            <span><?php echo htmlspecialchars($stat['label']); ?></span>
                        </article>
                    <?php endforeach; ?>
                </div>

                <div class="hero-slider-mock hero-storyline" role="tablist" aria-label="Homepage highlights">
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
                <div class="hero-editorial-strip">
                    <article class="hero-editorial-card hero-editorial-card-primary">
                        <p class="card-label">School Identity</p>
                        <h2>Competence. Discipline. Innovation.</h2>
                        <p>A confident school environment shaped by practical projects, clear routines, and career-focused learning.</p>
                    </article>
                    <article class="hero-editorial-card">
                        <p class="card-label">Student Journey</p>
                        <div class="hero-journey-list">
                            <span>Discover your trade</span>
                            <span>Build practical skills</span>
                            <span>Grow into work-ready confidence</span>
                        </div>
                    </article>
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
                <div class="hero-panel-grid hero-panel-grid-stacked">
                    <article class="hero-card">
                        <p class="card-label">Quick Links</p>
                        <div class="hero-shortcuts hero-shortcuts-vertical">
                            <a href="/MUBUGA-TSS/pages/programs.php" class="shortcut-link">View Programs</a>
                            <a href="/MUBUGA-TSS/pages/gallery.php" class="shortcut-link">See Gallery</a>
                            <a href="/MUBUGA-TSS/pages/contact.php" class="shortcut-link">Contact Us</a>
                        </div>
                    </article>
                    <article class="hero-card hero-card-accent-soft">
                        <p class="card-label">Why Choose Us</p>
                        <ul class="hero-points">
                            <?php foreach ($highlights as $highlight): ?>
                                <li><?php echo htmlspecialchars($highlight); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </article>
                </div>
            </div>
        </div>
    </section>

    <section class="section about" id="about">
        <div class="container section-grid">
            <div class="about-copy">
                <p class="eyebrow">Welcome To</p>
                <h2>Mubuga TSS</h2>
                <p><?php echo htmlspecialchars($schoolName); ?> is a TVET-focused school committed to practical education, student discipline, and career-ready technical skills. The school is centered on two specialized trades: Software Development and Electrical Technology.</p>
                <div class="about-facts about-facts-editorial">
                    <article class="about-fact about-fact-feature">
                        <span>School Direction</span>
                        <strong>Competency-based learning</strong>
                        <p>Students grow through guided practice, real tools, and clear technical standards.</p>
                    </article>
                    <article class="about-fact">
                        <span>Training Focus</span>
                        <strong>Practical trades for modern careers</strong>
                    </article>
                    <article class="about-fact">
                        <span>Learning Culture</span>
                        <strong>Discipline, teamwork, and confidence</strong>
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
            <div class="school-gallery-home-shell">
                <div class="school-gallery-home-intro">
                    <div class="school-gallery-home-copy">
                        <p class="eyebrow">School Gallery</p>
                        <h2>A professional view of campus life, workshops, and student activity.</h2>
                        <p class="school-gallery-home-text">Explore selected highlights from Mubuga TSS. The gallery showcases practical learning, school spaces, and the everyday environment that shapes student growth.</p>
                    </div>
                    <div class="school-gallery-home-stats">
                        <div class="school-gallery-home-stat">
                            <strong><?php echo count($homepageGallery); ?></strong>
                            <span>Photo highlights</span>
                        </div>
                        <div class="school-gallery-home-stat">
                            <strong><?php echo count(array_unique(array_map(static fn(array $item): string => (string) ($item['category_label'] ?? 'Campus'), $homepageGallery))); ?></strong>
                            <span>Gallery topics</span>
                        </div>
                    </div>
                </div>

                <?php if ($homepageGallery !== []): ?>
                    <div class="school-gallery-home-grid">
                        <?php foreach (array_slice($homepageGallery, 0, 6) as $item): ?>
                            <a href="/MUBUGA-TSS/pages/gallery.php" class="school-gallery-home-card" aria-label="<?php echo htmlspecialchars((string) $item['title']); ?>">
                                <div class="school-gallery-home-media">
                                    <img src="<?php echo htmlspecialchars((string) $item['image']); ?>" alt="<?php echo htmlspecialchars((string) $item['title']); ?>" class="kha-gallery-image">
                                    <span class="school-gallery-home-overlay" aria-hidden="true">
                                        <span class="school-gallery-home-icon">&#128269;</span>
                                    </span>
                                    <span class="kha-gallery-badge"><?php echo htmlspecialchars((string) ($item['category_label'] ?? 'Campus')); ?></span>
                                </div>
                                <div class="school-gallery-home-body">
                                    <h3><?php echo htmlspecialchars((string) $item['title']); ?></h3>
                                    <p><?php echo htmlspecialchars((string) $item['text']); ?></p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
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
<?php renderSiteFooter($schoolName); ?>
