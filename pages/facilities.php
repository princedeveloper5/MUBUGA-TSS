<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/site_data.php';
require_once __DIR__ . '/../includes/site_layout.php';
require_once __DIR__ . '/../portal/header.php';
require_once __DIR__ . '/../portal/footer.php';
$page = sitePageContent('facilities', [
    'title' => 'Facilities',
    'excerpt' => 'Our campus supports real training through classrooms, labs, workshops, and a disciplined school environment.',
    'content' => 'Learning spaces built for practical growth.',
    'image' => 'assets/images/mb2.jfif',
]);
renderSiteHeader($page['title'], $schoolName, $contacts, 'facilities', [
    'description' => $page['excerpt'],
    'image' => $page['image'],
]);
?>

<!-- Hero Section with Background Image -->
<section class="hero-section" id="facilities-hero">
    <div class="hero-background" id="hero-background" style="background-image: url('/MUBUGA-TSS/<?php echo htmlspecialchars($page['image']); ?>');">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1 id="hero-title">Our Facilities</h1>
                    <p id="hero-description">Learning spaces built for practical growth. Our campus supports real training through classrooms, labs, workshops, and a disciplined school environment.</p>
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
            <h2>Welcome to Our Facilities</h2>
            <p>Modern spaces for technical excellence</p>
        </div>
        
        <div class="welcome-content">
            <div class="welcome-text">
                <p>At Mubuga Technical Secondary School, we provide state-of-the-art facilities designed to support hands-on learning and technical skill development. Our workshops, laboratories, and classrooms are equipped with modern tools and equipment to ensure students receive practical, industry-relevant training.</p>
                <p>Our facilities are maintained to high standards to create a safe, productive learning environment where students can develop their technical skills with confidence.</p>
            </div>
            <div class="welcome-cta">
                <a href="/MUBUGA-TSS/pages/gallery.php" class="button button-primary">View Gallery</a>
                <a href="/MUBUGA-TSS/pages/contact.php" class="button button-secondary">Schedule Visit</a>
            </div>
        </div>
    </div>
</section>

<!-- Facilities Grid Section -->
<main>
    <section class="section facilities">
        <div class="container">
            <div class="section-heading">
                <h2>Our Learning Spaces</h2>
                <p>Explore the facilities that support our technical education programs</p>
            </div>
            
            <div class="facilities-grid">
                <?php foreach ($facilities as $facility): ?>
                    <article class="facility-card">
                        <img src="/MUBUGA-TSS/<?php echo htmlspecialchars($facility['image']); ?>" alt="<?php echo htmlspecialchars($facility['title']); ?>" class="section-photo photo-viewer">
                        <h3><?php echo htmlspecialchars($facility['title']); ?></h3>
                        <p><?php echo htmlspecialchars($facility['text']); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
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
}
</style>
