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

// Enhanced hero section with animated background image
?>
<div class="hero-section" style="background-image: url('/MUBUGA-TSS/<?php echo htmlspecialchars($page['image']); ?>'); background-size: cover; background-position: center; position: relative; min-height: 350px; display: flex; align-items: center; justify-content: center; animation: heroBackground 20s ease-in-out infinite;">
    <!-- Enhanced dark overlay for better text readability -->
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(135deg, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.4) 100%); z-index: 1; animation: heroOverlay 15s ease-in-out infinite;"></div>
    
    <div style="position: relative; z-index: 2; text-align: center; color: #ffffff; max-width: 800px; padding: 0 20px;">
        <h1 style="font-size: clamp(2rem, 5vw, 3rem); font-weight: 800; margin-bottom: 1rem; text-transform: uppercase; letter-spacing: 2px; line-height: 1.1; text-shadow: 0 3px 15px rgba(0,0,0,0.5);">FACILITIES</h1>
        
        <div style="margin-bottom: 1.5rem;">
            <p style="font-size: clamp(1.1rem, 2.5vw, 1.5rem); font-weight: 600; margin-bottom: 0.8rem; line-height: 1.4; text-shadow: 0 2px 8px rgba(0,0,0,0.4);"><?php echo htmlspecialchars($page['content']); ?></p>
        </div>
        
        <p style="font-size: clamp(0.95rem, 2vw, 1.2rem); font-weight: 400; margin: 0; line-height: 1.5; text-shadow: 0 1px 6px rgba(0,0,0,0.3); opacity: 0.95;"><?php echo htmlspecialchars($page['excerpt']); ?></p>
    </div>
</div>

<main>
    <section class="section facilities">
        <div class="container">
            <div class="facilities-grid">
                <?php foreach ($facilities as $facility): ?>
                    <article class="facility-card">
                        <img src="/MUBUGA-TSS/<?php echo htmlspecialchars($facility['image']); ?>" alt="<?php echo htmlspecialchars($facility['title']); ?>" class="section-photo" loading="lazy" decoding="async">
                        <h3><?php echo htmlspecialchars($facility['title']); ?></h3>
                        <p><?php echo htmlspecialchars($facility['text']); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>

<?php renderSiteFooter($schoolName); ?>