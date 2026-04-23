<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/site_data.php';
require_once __DIR__ . '/../includes/site_layout.php';
require_once __DIR__ . '/../portal/header.php';
require_once __DIR__ . '/../portal/footer.php';
$page = sitePageContent('about-us', [
    'title' => 'About Us',
    'excerpt' => 'Learn story, mission, vision, and values that shape Mubuga TSS.',
    'content' => 'Mubuga TSS is committed to helping learners grow into capable professionals and responsible citizens through strong technical education and a disciplined school culture.',
    'image' => 'assets/images/students.jfif',
]);
renderSiteHeader($page['title'], $schoolName, $contacts, 'about', [
    'description' => $page['excerpt'],
    'image' => $page['image'],
]);
?>

<!-- Hero Section with Background Image -->
<section class="hero-section" id="about-hero">
    <div class="hero-background" id="hero-background" style="background-image: url('/MUBUGA-TSS/<?php echo htmlspecialchars($page['image']); ?>');">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1 id="hero-title">Welcome To <?php echo htmlspecialchars($schoolName); ?></h1>
                    <p id="hero-description">A focused TVET school with practical ambition. Learn story, mission, vision, and values that shape Mubuga TSS.</p>
                    <div class="hero-actions">
                        <a href="/MUBUGA-TSS/pages/admissions.php" class="button button-primary">Register</a>
                        <a href="/MUBUGA-TSS/pages/programs.php" class="button button-secondary">Explore Programs</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Welcome Message Section -->
<section class="section welcome" id="welcome">
    <div class="container">
        <div class="section-heading">
            <h2>Welcome to <?php echo htmlspecialchars($schoolName); ?></h2>
            <p>Your gateway to technical excellence</p>
        </div>
        
        <div class="welcome-content">
            <div class="welcome-text">
                <p><?php echo htmlspecialchars($schoolName); ?> is committed to helping learners grow into capable professionals and responsible citizens through strong technical education and a disciplined school culture.</p>
                <p>Our school centers learning around Software Development and Electrical Technology, with a strong emphasis on practical application, teamwork, and career readiness.</p>
            </div>
            <div class="welcome-cta">
                <a href="/MUBUGA-TSS/pages/gallery.php" class="button button-primary">View Gallery</a>
                <a href="/MUBUGA-TSS/pages/contact.php" class="button button-secondary">Contact Us</a>
            </div>
        </div>
    </div>
</section>

<!-- About Story Section -->
<main>
    <section class="section about" id="about">
        <div class="container">
            <div class="section-heading">
                <h2>Our Story</h2>
                <p>A focused TVET school with practical ambition</p>
            </div>
            
            <div class="about-grid">
                <div class="about-item">
                    <div class="about-icon">
                        <svg viewBox="0 0 24 24" width="32" height="32" fill="currentColor">
                            <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/>
                        </svg>
                    </div>
                    <div class="about-content">
                        <h3>School Character</h3>
                        <p>Disciplined, practical, and future-focused learning environment that prepares students for technical careers.</p>
                    </div>
                </div>
                
                <div class="about-item">
                    <div class="about-icon">
                        <svg viewBox="0 0 24 24" width="32" height="32" fill="currentColor">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                    </div>
                    <div class="about-content">
                        <h3>Main Priority</h3>
                        <p>Technical growth with strong student values, ensuring both skill development and character formation.</p>
                    </div>
                </div>
                
                <div class="about-item">
                    <div class="about-icon">
                        <svg viewBox="0 0 24 24" width="32" height="32" fill="currentColor">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    </div>
                    <div class="about-content">
                        <h3>Training Focus</h3>
                        <p>Practical trades for modern careers in Software Development and Electrical Technology with hands-on experience.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission, Vision, and Motto Section -->
    <section class="section values" id="values">
        <div class="container">
            <div class="section-heading">
                <h2>Our Foundation</h2>
                <p>Mission, Vision, and Values that guide us</p>
            </div>

            <div class="values-grid expanded-values">
                <article class="value-card">
                    <h3>Our Motto</h3>
                    <p>Discipline, competence, and innovation in technical education.</p>
                </article>
                <article class="value-card">
                    <h3>Vision</h3>
                    <p>To become a respected center of technical excellence that prepares students for opportunity, service, and innovation.</p>
                </article>
                <article class="value-card">
                    <h3>Mission</h3>
                    <p>To equip learners with practical skills, discipline, and creativity through focused vocational education.</p>
                </article>
            </div>
        </div>
    </section>

    <!-- Core Values Section -->
    <section class="section core-values" id="core-values">
        <div class="container">
            <div class="section-heading">
                <h2>Core Values</h2>
                <p>The principles that guide daily school life</p>
            </div>
            <div class="values-grid expanded-values">
                <?php foreach ($values as $value): ?>
                    <article class="value-card">
                        <h3><?php echo htmlspecialchars($value); ?></h3>
                        <p>Part of culture we expect students and staff to live out in learning, leadership, and service.</p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Programs Section -->
    <section class="section programs" id="programs">
        <div class="container">
            <div class="section-heading">
                <h2>Our Programs</h2>
                <p>Two training programs built for modern technical careers</p>
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

    <!-- Contact Section -->
    <section class="section cta" id="contact">
        <div class="container contact-grid">
            <div class="cta-panel">
                <div class="cta-copy">
                    <p class="eyebrow">Join <?php echo htmlspecialchars($schoolName); ?></p>
                    <h2>Ready to study Software Development or Electrical Technology?</h2>
                    <p>Contact the school office for admissions, reporting dates, and program guidance.</p>
                </div>
                <div class="cta-actions">
                    <a href="mailto:info@mubugatss.rw" class="button button-primary">Email The School</a>
                    <a href="/MUBUGA-TSS/pages/contact.php" class="button button-secondary">Contact Us</a>
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

<style>
/* Hero Section Styles */
.hero-section {
    position: relative;
    min-height: 100vh;
    overflow: hidden;
}

.hero-background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed;
}

.hero-background::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1;
}

.hero-content {
    position: relative;
    z-index: 2;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    text-align: center;
    color: #ffffff;
}

.hero-text h1 {
    font-size: clamp(2.5rem, 5vw, 4rem);
    font-weight: 800;
    margin-bottom: 1.5rem;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
}

.hero-text p {
    font-size: clamp(1.1rem, 2vw, 1.4rem);
    margin-bottom: 2.5rem;
    max-width: 800px;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.8);
}

.hero-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

/* Welcome Section Styles */
.welcome {
    padding: 4rem 0;
    background: #f8f9fa;
}

.welcome-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
    align-items: center;
}

.welcome-text p {
    margin-bottom: 1.5rem;
    line-height: 1.8;
}

.welcome-cta {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

/* About Section Styles */
.about-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-top: 3rem;
}

.about-item {
    display: flex;
    gap: 1.5rem;
    align-items: flex-start;
}

.about-icon {
    flex-shrink: 0;
    width: 60px;
    height: 60px;
    background: var(--accent-color, #FF6B35);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.about-content h3 {
    margin-bottom: 0.5rem;
    color: var(--heading-color, #1a1a1a);
}

.about-content p {
    line-height: 1.6;
    color: var(--text-color, #666);
}

/* Values Section Styles */
.core-values {
    background: #f8f9fa;
}

/* Responsive Design */
@media (max-width: 768px) {
    .welcome-content {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .hero-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .hero-actions .button {
        width: 100%;
        max-width: 300px;
    }
    
    .about-item {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }
    
    .about-icon {
        margin: 0 auto;
    }
}
</style>
