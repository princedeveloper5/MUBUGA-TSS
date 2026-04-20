<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/site_data.php';
require_once __DIR__ . '/../includes/site_layout.php';

$page = sitePageContent('announcements', [
    'title' => 'Announcements',
    'excerpt' => 'Read important public notices, school updates, and admission information from Mubuga TSS.',
    'content' => 'School announcements',
    'image' => 'assets/images/school view 2.jpg',
]);

$announcementItems = array_values(array_filter(array_map(function (array $item): array {
    $item['category'] = normalizeNewsCategory((string) ($item['category'] ?? 'news'));
    $item['published_label'] = formatPublishedDate((string) ($item['published_at'] ?? ''));
    return $item;
}, $news), static function (array $item): bool {
    return ($item['category'] ?? 'news') === 'announcements';
}));

renderSiteHeader($page['title'], $schoolName, $contacts, 'news', [
    'description' => $page['excerpt'],
    'image' => $page['image'],
]);
renderInnerHero('ANNOUNCEMENTS', $page['title'], $page['excerpt'], $page['image']);
?>
<main>
    <section class="section story-list-section">
        <div class="container">
            <div class="section-heading">
                <p class="eyebrow">Notice Board</p>
                <h2>Important updates for students, parents, and applicants.</h2>
                <p>This page is for notices, fee updates, admission reminders, and other official communication from the school.</p>
            </div>

            <div class="news-filter-bar">
                <a href="/MUBUGA-TSS/pages/news.php" class="news-filter-link">All News</a>
                <a href="/MUBUGA-TSS/pages/events.php" class="news-filter-link">Events</a>
                <a href="/MUBUGA-TSS/pages/announcements.php" class="news-filter-link is-active">Announcements</a>
            </div>

            <div class="announcement-grid">
                <?php foreach ($announcementItems as $item): ?>
                    <article class="announcement-card">
                        <div class="announcement-card-media">
                            <img src="/MUBUGA-TSS/<?php echo htmlspecialchars((string) $item['image']); ?>" alt="<?php echo htmlspecialchars((string) $item['title']); ?>" class="announcement-card-image">
                        </div>
                        <div class="announcement-card-body">
                            <p class="news-tag"><?php echo htmlspecialchars(newsCategoryLabel((string) $item['category'])); ?></p>
                            <h3><?php echo htmlspecialchars((string) $item['title']); ?></h3>
                            <p class="story-date"><?php echo htmlspecialchars((string) $item['published_label']); ?></p>
                            <p><?php echo htmlspecialchars((string) $item['text']); ?></p>
                            <a href="<?php echo htmlspecialchars((string) $item['link']); ?>" class="inline-link">Open announcement</a>
                        </div>
                    </article>
                <?php endforeach; ?>

                <?php if ($announcementItems === []): ?>
                    <article class="feature-card article-card">
                        <h2>No announcements published yet</h2>
                        <p>When you publish announcements in the admin panel, they will appear here automatically.</p>
                    </article>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>
<?php renderSiteFooter($schoolName); ?>
