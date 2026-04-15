<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/site_data.php';
require_once __DIR__ . '/../includes/site_layout.php';

$requestedSlug = trim((string) ($_GET['slug'] ?? ''));
$selectedNews = null;

if ($requestedSlug !== '') {
    foreach ($news as $item) {
        if (($item['slug'] ?? '') === $requestedSlug) {
            $selectedNews = $item;
            break;
        }
    }
}

if ($selectedNews !== null) {
    renderSiteHeader($selectedNews['title'], $schoolName, $contacts, 'news');
    renderInnerHero('LATEST NEWS', $selectedNews['title'], $selectedNews['text'], $selectedNews['image']);
} else {
    $page = sitePageContent('news', [
        'title' => 'News',
        'excerpt' => 'Stay informed about practical learning, school activities, and important Mubuga TSS updates.',
        'content' => 'School updates and community stories.',
        'image' => 'assets/images/mb1.jfif',
    ]);
    renderSiteHeader($page['title'], $schoolName, $contacts, 'news');
    renderInnerHero('LATEST NEWS', $page['content'], $page['excerpt'], $page['image']);
}
?>
<main>
    <?php if ($selectedNews !== null): ?>
        <section class="section">
            <div class="container">
                <article class="feature-card article-card">
                    <p class="news-tag">Mubuga TSS News</p>
                    <h2><?php echo htmlspecialchars($selectedNews['title']); ?></h2>
                    <p><?php echo nl2br(htmlspecialchars($selectedNews['content'] ?? $selectedNews['text'])); ?></p>
                    <p><a href="/MUBUGA-TSS/pages/news.php" class="inline-link">Back to all news</a></p>
                </article>
            </div>
        </section>
    <?php else: ?>
        <section class="section news">
            <div class="container">
                <div class="news-grid">
                    <?php foreach ($news as $item): ?>
                        <article class="news-card">
                            <img src="/MUBUGA-TSS/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="news-image">
                            <p class="news-tag">Mubuga TSS</p>
                            <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                            <p><?php echo htmlspecialchars($item['text']); ?></p>
                            <a href="<?php echo htmlspecialchars($item['link']); ?>" class="inline-link">Read More</a>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>
</main>
<?php renderSiteFooter($schoolName); ?>
