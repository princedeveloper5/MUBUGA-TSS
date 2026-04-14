<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/site_data.php';
require_once __DIR__ . '/../includes/site_layout.php';
renderSiteHeader('Facilities', $schoolName, $contacts, 'facilities');
renderInnerHero('FACILITIES', 'Learning spaces built for practical growth', 'Our campus supports real training through classrooms, labs, workshops, and a disciplined school environment.', 'assets/images/mb2.jfif');
?>
<main>
    <section class="section facilities">
        <div class="container">
            <div class="facilities-grid">
                <?php foreach ($facilities as $facility): ?>
                    <article class="facility-card">
                        <img src="/MUBUGA-TSS/<?php echo htmlspecialchars($facility['image']); ?>" alt="<?php echo htmlspecialchars($facility['title']); ?>" class="section-photo">
                        <h3><?php echo htmlspecialchars($facility['title']); ?></h3>
                        <p><?php echo htmlspecialchars($facility['text']); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>
<?php renderSiteFooter($schoolName); ?>
