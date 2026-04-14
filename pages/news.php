<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/site_data.php';
require_once __DIR__ . '/../includes/site_layout.php';
renderSiteHeader('News', $schoolName, $contacts, 'news');
renderInnerHero('LATEST NEWS', 'School updates and community stories', 'Stay informed about practical learning, school activities, and important Mubuga TSS updates.', 'assets/images/mb1.jfif');
?>
<main>
    <section class="section news">
        <div class="container">
            <div class="news-grid">
                <?php foreach ($news as $item): ?>
                    <article class="news-card">
                        <img src="/MUBUGA-TSS/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="news-image">
                        <p class="news-tag">Mubuga TSS</p>
                        <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                        <p><?php echo htmlspecialchars($item['text']); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>
<?php renderSiteFooter($schoolName); ?>
