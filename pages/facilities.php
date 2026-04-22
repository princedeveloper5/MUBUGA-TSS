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
renderInnerHero('FACILITIES', $page['content'], $page['excerpt'], $page['image']);
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
