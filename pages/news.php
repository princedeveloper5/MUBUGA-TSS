<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/site_data.php';
require_once __DIR__ . '/../includes/site_layout.php';

$requestedSlug = trim((string) ($_GET['slug'] ?? ''));
$requestedType = normalizeNewsCategory((string) ($_GET['type'] ?? 'news'));
$selectedNews = null;
$newsGroups = [
    'events' => [],
    'announcements' => [],
    'news' => [],
];

foreach ($news as $item) {
    $category = normalizeNewsCategory((string) ($item['category'] ?? 'news'));
    $item['category'] = $category;
    $newsGroups[$category][] = $item;
}

if ($requestedSlug !== '') {
    foreach ($news as $item) {
        if (($item['slug'] ?? '') === $requestedSlug) {
            $item['category'] = normalizeNewsCategory((string) ($item['category'] ?? 'news'));
            $selectedNews = $item;
            break;
        }
    }
}

if ($selectedNews !== null) {
    renderSiteHeader($selectedNews['title'], $schoolName, $contacts, 'news');
    renderInnerHero(newsCategoryLabel((string) $selectedNews['category']), $selectedNews['title'], $selectedNews['text'], $selectedNews['image']);
} else {
    $page = sitePageContent('news', [
        'title' => 'News',
        'excerpt' => 'Find the latest events, announcements and school updates.',
        'content' => 'School updates',
        'image' => 'assets/images/students.jfif',
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
                    <p class="news-tag"><?php echo htmlspecialchars(newsCategoryLabel((string) $selectedNews['category'])); ?></p>
                    <h2><?php echo htmlspecialchars($selectedNews['title']); ?></h2>
                    <p><?php echo nl2br(htmlspecialchars($selectedNews['content'] ?? $selectedNews['text'])); ?></p>
                    <p><a href="/MUBUGA-TSS/pages/news.php" class="inline-link">Back to all news</a></p>
                </article>
            </div>
        </section>
    <?php else: ?>
        <section class="section news">
            <div class="container">
                <div class="section-heading">
                    <p class="eyebrow">Update Center</p>
                    <h2>Events, announcements, and school news.</h2>
                    <p>Choose a section and open the full story.</p>
                </div>

                <div class="news-filter-bar">
                    <a href="/MUBUGA-TSS/pages/news.php?type=events" class="news-filter-link<?php echo $requestedType === 'events' ? ' is-active' : ''; ?>">Events</a>
                    <a href="/MUBUGA-TSS/pages/news.php?type=announcements" class="news-filter-link<?php echo $requestedType === 'announcements' ? ' is-active' : ''; ?>">Announcements</a>
                    <a href="/MUBUGA-TSS/pages/news.php?type=news" class="news-filter-link<?php echo $requestedType === 'news' ? ' is-active' : ''; ?>">News</a>
                </div>

                <section class="news-collection">
                    <div class="news-collection-header">
                        <p class="eyebrow"><?php echo htmlspecialchars(newsCategoryLabel($requestedType)); ?></p>
                        <h3><?php echo htmlspecialchars(newsCategoryLabel($requestedType)); ?> at Mubuga TSS</h3>
                    </div>
                    <div class="news-grid">
                        <?php foreach ($newsGroups[$requestedType] as $item): ?>
                            <article class="news-card">
                                <img src="/MUBUGA-TSS/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="news-image">
                                <div class="news-card-body">
                                    <p class="news-tag"><?php echo htmlspecialchars(newsCategoryLabel((string) $item['category'])); ?></p>
                                    <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                                    <p><?php echo htmlspecialchars($item['text']); ?></p>
                                    <a href="<?php echo htmlspecialchars($item['link']); ?>" class="inline-link">Read More</a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="news-section-stack">
                    <?php foreach (['events', 'announcements', 'news'] as $category): ?>
                        <article class="news-section-card">
                            <div class="news-section-card-top">
                                <p class="eyebrow"><?php echo htmlspecialchars(newsCategoryLabel($category)); ?></p>
                                <h3><?php echo htmlspecialchars(newsCategoryLabel($category)); ?></h3>
                            </div>
                            <div class="news-mini-list">
                                <?php foreach (array_slice($newsGroups[$category], 0, 2) as $item): ?>
                                    <a href="<?php echo htmlspecialchars($item['link']); ?>" class="news-mini-item">
                                        <strong><?php echo htmlspecialchars($item['title']); ?></strong>
                                        <span><?php echo htmlspecialchars($item['text']); ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </section>
            </div>
        </section>
    <?php endif; ?>
</main>
<?php renderSiteFooter($schoolName); ?>
