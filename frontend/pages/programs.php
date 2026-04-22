<?php
declare(strict_types=1);
require_once __DIR__ . '/../../shared/site_data.php';
require_once __DIR__ . '/../../shared/site_layout.php';
$page = sitePageContent('our-programs', [
    'title' => 'Our Programs',
    'excerpt' => 'Explore the two Mubuga TSS trades and the practical skills they build.',
    'content' => 'Technical programs for modern careers.',
    'image' => 'assets/images/mb3.jfif',
]);
renderSiteHeader($page['title'], $schoolName, $contacts, 'programs', [
    'description' => $page['excerpt'],
    'image' => $page['image'],
]);
renderInnerHero('OUR PROGRAMS', $page['content'], $page['excerpt'], $page['image']);
?>
<main>
    <section class="section">
        <div class="container">
            <div class="section-heading">
                <p class="eyebrow">Training Pathways</p>
                <h2>Programs designed around practical skill, discipline, and career readiness.</h2>
                <p>Each Mubuga TSS trade is structured to combine technical knowledge with guided practice and clear progression.</p>
            </div>
            <div class="program-grid">
                <?php foreach ($programs as $program): ?>
                    <article class="program-card">
                        <img src="/MUBUGA-TSS/<?php echo htmlspecialchars($program['image']); ?>" alt="<?php echo htmlspecialchars($program['title']); ?>" class="program-image">
                        <div class="program-card-body">
                            <p class="card-label">Technical Trade</p>
                            <h3><?php echo htmlspecialchars($program['title']); ?></h3>
                            <p><?php echo htmlspecialchars($program['summary']); ?></p>
                            <ul>
                                <?php foreach ($program['focus'] as $item): ?>
                                    <li><?php echo htmlspecialchars($item); ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <a href="/MUBUGA-TSS/pages/admissions.php" class="inline-link">Apply for this trade</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>
<?php renderSiteFooter($schoolName); ?>
