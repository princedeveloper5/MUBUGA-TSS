<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/site_data.php';
require_once __DIR__ . '/../includes/site_layout.php';

$galleryLead = $gallery[0] ?? null;
$galleryHighlights = array_slice($gallery, 1, 4);
$galleryCollection = array_slice($gallery, 5);

$page = sitePageContent('gallery', [
    'title' => 'Gallery',
    'excerpt' => 'See Mubuga TSS through classroom, workshop, campus, and student activity images.',
    'content' => 'Pictures from school life and training.',
    'image' => 'assets/images/school view 1.jpg',
]);
renderSiteHeader($page['title'], $schoolName, $contacts, 'gallery');
renderInnerHero('GALLERY', $page['content'], $page['excerpt'], $page['image']);
?>
<main>
    <section class="section gallery-section">
        <div class="container">
            <div class="section-heading">
                <p class="eyebrow">Photo Stories</p>
                <h2>School views arranged in a clearer campus gallery style.</h2>
                <p>This page now opens with a featured campus image and a supporting photo wall to feel more like a complete school gallery.</p>
            </div>

            <?php if ($galleryLead !== null): ?>
                <div class="gallery-showcase gallery-showcase-page">
                    <article class="gallery-feature-card gallery-card">
                        <img src="/MUBUGA-TSS/<?php echo htmlspecialchars($galleryLead['image']); ?>" alt="<?php echo htmlspecialchars($galleryLead['title']); ?>" class="gallery-image gallery-image-feature">
                        <div class="gallery-copy gallery-copy-feature">
                            <p class="gallery-kicker">Featured View</p>
                            <h3><?php echo htmlspecialchars($galleryLead['title']); ?></h3>
                            <p><?php echo htmlspecialchars($galleryLead['text']); ?></p>
                        </div>
                    </article>
                    <div class="gallery-side-grid">
                        <?php foreach ($galleryHighlights as $item): ?>
                            <article class="gallery-mini-card gallery-card">
                                <img src="/MUBUGA-TSS/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="gallery-image">
                                <div class="gallery-copy">
                                    <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                                    <p><?php echo htmlspecialchars($item['text']); ?></p>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="gallery-grid gallery-grid-page">
                <?php foreach ($galleryCollection as $index => $item): ?>
                    <?php
                    $cardClass = 'gallery-card gallery-wall-card';
                    if ($index === 0) {
                        $cardClass .= ' is-hero';
                    } elseif ($index % 5 === 1 || $index % 5 === 4) {
                        $cardClass .= ' is-tall';
                    }
                    ?>
                    <article class="<?php echo htmlspecialchars($cardClass); ?>">
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
