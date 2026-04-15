<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/site_data.php';
require_once __DIR__ . '/../includes/site_layout.php';
$page = sitePageContent('gallery', [
    'title' => 'Gallery',
    'excerpt' => 'See Mubuga TSS through classroom, workshop, campus, and student activity images.',
    'content' => 'Pictures from school life and training.',
    'image' => 'assets/images/mb1.jfif',
]);
renderSiteHeader($page['title'], $schoolName, $contacts, 'gallery');
renderInnerHero('GALLERY', $page['content'], $page['excerpt'], $page['image']);
?>
<main>
    <section class="section gallery-section">
        <div class="container">
            <div class="gallery-grid">
                <?php foreach ($gallery as $item): ?>
                    <article class="gallery-card">
                        <img src="/MUBUGA-TSS/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="gallery-image">
                        <div class="gallery-copy">
                            <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                            <p><?php echo htmlspecialchars($item['text']); ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>
<?php renderSiteFooter($schoolName); ?>
