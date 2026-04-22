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

// Enhanced hero section with animated background image
?>
<div class="hero-section" style="background-image: url('/MUBUGA-TSS/<?php echo htmlspecialchars($page['image']); ?>'); background-size: cover; background-position: center; position: relative; min-height: 350px; display: flex; align-items: center; justify-content: center; animation: heroBackground 20s ease-in-out infinite;">
    <!-- Enhanced dark overlay for better text readability -->
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(135deg, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.4) 100%); z-index: 1; animation: heroOverlay 15s ease-in-out infinite;"></div>
    
    <div style="position: relative; z-index: 2; text-align: center; color: #ffffff; max-width: 800px; padding: 0 20px;">
        <h1 style="font-size: clamp(2rem, 5vw, 3rem); font-weight: 800; margin-bottom: 1rem; text-transform: uppercase; letter-spacing: 2px; line-height: 1.1; text-shadow: 0 3px 15px rgba(0,0,0,0.5);">WELCOME TO</h1>
        <h1 style="font-size: clamp(2.5rem, 6vw, 3.5rem); font-weight: 900; margin-bottom: 1.5rem; text-transform: uppercase; letter-spacing: 3px; line-height: 1.1; text-shadow: 0 4px 20px rgba(0,0,0,0.6); color: #FF6B35;"><?php echo htmlspecialchars($schoolName); ?></h1>
        
        <div style="margin-bottom: 1.5rem;">
            <p style="font-size: clamp(1.1rem, 2.5vw, 1.5rem); font-weight: 600; margin-bottom: 0.8rem; line-height: 1.4; text-shadow: 0 2px 8px rgba(0,0,0,0.4);"><?php echo htmlspecialchars($page['content']); ?></p>
        </div>
        
        <p style="font-size: clamp(0.95rem, 2vw, 1.2rem); font-weight: 400; margin: 0; line-height: 1.5; text-shadow: 0 1px 6px rgba(0,0,0,0.3); opacity: 0.95;"><?php echo htmlspecialchars($page['excerpt']); ?></p>
    </div>
</div>

<main>
    <section class="section">
        <div class="container section-grid">
            <div class="about-copy">
                <p class="eyebrow">Our Story</p>
                <h2>A focused TVET school with practical ambition.</h2>
                <p>Mubuga TSS is committed to helping learners grow into capable professionals and responsible citizens through strong technical education and a disciplined school culture.</p>
                <p>Our school centers learning around Software Development and Electrical Technology, with a strong emphasis on practical application, teamwork, and career readiness.</p>
                <div class="about-facts">
                    <article class="about-fact">
                        <span>School Character</span>
                        <strong>Disciplined, practical, and future-focused learning</strong>
                    </article>
                    <article class="about-fact">
                        <span>Main Priority</span>
                        <strong>Technical growth with strong student values</strong>
                    </article>
                </div>
            </div>
            <div class="feature-stack">
                <article class="feature-card">
                    <h3>Our Motto</h3>
                    <p>Discipline, competence, and innovation in technical education.</p>
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

    <section class="section values">
        <div class="container">
            <div class="section-heading">
                <p class="eyebrow">Core Values</p>
                <h2>The principles that guide daily school life.</h2>
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
</main>

<?php renderSiteFooter($schoolName); ?>
