<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/site_data.php';
require_once __DIR__ . '/../includes/site_layout.php';

$requestedSlug = trim((string) ($_GET['slug'] ?? ''));
$selectedNews = null;
$newsItems = array_map(function (array $item): array {
    $item['category'] = normalizeNewsCategory((string) ($item['category'] ?? 'news'));
    $item['published_label'] = formatPublishedDate((string) ($item['published_at'] ?? ''));
    return $item;
}, $news);

if ($requestedSlug !== '') {
    foreach ($newsItems as $item) {
        if (($item['slug'] ?? '') === $requestedSlug) {
            $selectedNews = $item;
            break;
        }
    }
}

if ($selectedNews !== null) {
    renderSiteHeader($selectedNews['title'], $schoolName, $contacts, 'news', [
        'description' => (string) ($selectedNews['text'] ?? ''),
        'image' => (string) ($selectedNews['image'] ?? ''),
        'type' => 'article',
    ]);
    renderInnerHero(newsCategoryLabel((string) $selectedNews['category']), $selectedNews['title'], $selectedNews['text'], $selectedNews['image']);
} else {
    $page = sitePageContent('news', [
        'title' => 'News',
        'excerpt' => 'Find the latest events, announcements and school updates.',
        'content' => 'School updates',
        'image' => 'assets/images/students.jfif',
    ]);
    renderSiteHeader($page['title'], $schoolName, $contacts, 'news', [
        'description' => $page['excerpt'],
        'image' => $page['image'],
    ]);
    renderInnerHero('LATEST NEWS', $page['content'], $page['excerpt'], $page['image']);
}
?>
<main>
    <?php if ($selectedNews !== null): ?>
        <section class="section">
            <div class="container">
                <article class="feature-card article-card">
                    <p class="news-tag"><?php echo htmlspecialchars(newsCategoryLabel((string) $selectedNews['category'])); ?></p>
                    <h2><?php echo htmlspecialchars($selectedNews['title']); ?></h2>
                    <p class="story-date"><?php echo htmlspecialchars((string) $selectedNews['published_label']); ?></p>
                    <p><?php echo nl2br(htmlspecialchars($selectedNews['content'] ?? $selectedNews['text'])); ?></p>
                    <p><a href="/MUBUGA-TSS/pages/news.php" class="inline-link">Back to all news</a></p>
                </article>
            </div>
        </section>
    <?php else: ?>
        <section class="section news-page-section">
            <div class="container">
                <div class="section-heading">
                    <p class="eyebrow">School Newsroom</p>
                    <h2>Latest stories from Mubuga TSS.</h2>
                    <p>Open any story to read the full update, then explore events and announcements from the same newsroom.</p>
                </div>

                <div class="news-filter-bar">
                    <a href="/MUBUGA-TSS/pages/news.php" class="news-filter-link is-active">All News</a>
                    <a href="/MUBUGA-TSS/pages/events.php" class="news-filter-link">Events</a>
                    <a href="/MUBUGA-TSS/pages/announcements.php" class="news-filter-link">Announcements</a>
                </div>

                <div class="news-page-grid">
                    <?php foreach ($newsItems as $item): ?>
                        <article class="news-card">
                            <img src="/MUBUGA-TSS/<?php echo htmlspecialchars((string) $item['image']); ?>" alt="<?php echo htmlspecialchars((string) $item['title']); ?>" class="news-image">
                            <div class="news-card-body">
                                <p class="news-tag"><?php echo htmlspecialchars(newsCategoryLabel((string) $item['category'])); ?></p>
                                <h3><?php echo htmlspecialchars((string) $item['title']); ?></h3>
                                <p class="story-date"><?php echo htmlspecialchars((string) $item['published_label']); ?></p>
                                <p><?php echo htmlspecialchars((string) $item['text']); ?></p>
                                <a href="<?php echo htmlspecialchars((string) $item['link']); ?>" class="inline-link">Read More</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>
</main>
<?php renderSiteFooter($schoolName); ?>
