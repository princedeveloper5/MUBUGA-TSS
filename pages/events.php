<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/site_data.php';
require_once __DIR__ . '/../includes/site_layout.php';

$page = sitePageContent('events', [
    'title' => 'Events',
    'excerpt' => 'Follow school visits, open days, ceremonies, and important activities at Mubuga TSS.',
    'content' => 'School events',
    'image' => 'assets/images/school view 1.jpg',
]);

$eventItems = array_values(array_filter(array_map(function (array $item): array {
    $item['category'] = normalizeNewsCategory((string) ($item['category'] ?? 'news'));
    $item['published_label'] = formatPublishedDate((string) ($item['published_at'] ?? ''));
    return $item;
}, $news), static function (array $item): bool {
    return ($item['category'] ?? 'news') === 'events';
}));

renderSiteHeader($page['title'], $schoolName, $contacts, 'news', [
    'description' => $page['excerpt'],
    'image' => $page['image'],
]);
renderInnerHero('EVENTS', $page['title'], $page['excerpt'], $page['image']);
?>
<main>
    <section class="section story-list-section">
        <div class="container">
            <div class="section-heading">
                <p class="eyebrow">Event Calendar</p>
                <h2>Moments that bring the school community together.</h2>
                <p>These stories cover visits, open days, ceremonies, outreach, and other important Mubuga TSS activities.</p>
            </div>

            <div class="news-filter-bar">
                <a href="/MUBUGA-TSS/pages/news.php" class="news-filter-link">All News</a>
                <a href="/MUBUGA-TSS/pages/events.php" class="news-filter-link is-active">Events</a>
                <a href="/MUBUGA-TSS/pages/announcements.php" class="news-filter-link">Announcements</a>
            </div>

            <div class="story-list">
                <?php foreach ($eventItems as $item): ?>
                    <article class="story-card">
                        <div class="story-card-media">
                            <img src="/MUBUGA-TSS/<?php echo htmlspecialchars((string) $item['image']); ?>" alt="<?php echo htmlspecialchars((string) $item['title']); ?>" class="story-card-image">
                        </div>
                        <div class="story-card-body">
                            <p class="news-tag"><?php echo htmlspecialchars(newsCategoryLabel((string) $item['category'])); ?></p>
                            <h3><?php echo htmlspecialchars((string) $item['title']); ?></h3>
                            <p class="story-date"><?php echo htmlspecialchars((string) $item['published_label']); ?></p>
                            <p><?php echo htmlspecialchars((string) $item['text']); ?></p>
                            <a href="<?php echo htmlspecialchars((string) $item['link']); ?>" class="inline-link">Read full event</a>
                        </div>
                    </article>
                <?php endforeach; ?>

                <?php if ($eventItems === []): ?>
                    <article class="feature-card article-card">
                        <h2>No events published yet</h2>
                        <p>When you publish school events in the admin panel, they will appear here automatically.</p>
                    </article>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>
<?php renderSiteFooter($schoolName); ?>
