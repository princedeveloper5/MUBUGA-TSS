<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/site_data.php';
require_once __DIR__ . '/../includes/site_layout.php';
require_once __DIR__ . '/../portal/header.php';
require_once __DIR__ . '/../portal/footer.php';
$page = sitePageContent('our-programs', [
    'title' => 'Our Programs',
    'excerpt' => 'Learn about the two programs offered at Mubuga TSS and the skills students study.',
    'content' => 'Programs that help students build useful technical skills.',
    'image' => 'assets/images/students.jfif',
]);
renderSiteHeader($page['title'], $schoolName, $contacts, 'programs', [
    'description' => $page['excerpt'],
    'image' => $page['image'],
]);
renderInnerHero('OUR PROGRAMS', $page['content'], $page['excerpt'], $page['image'], false);
?>
<main class="programs-page">
    <section class="section programs-page-section">
        <div class="container">
            <div class="section-heading programs-page-heading">
                <p class="eyebrow">Training Pathways</p>
                <h2>Our programs help students learn in class and practice real technical work.</h2>
                <p>Each program gives students knowledge, practical skills, and preparation for future work or further study.</p>
            </div>
            <div class="program-grid">
                <?php foreach ($programs as $program): ?>
                    <article class="program-card">
                        <div class="program-card-media">
                            <img src="/MUBUGA-TSS/<?php echo htmlspecialchars($program['image']); ?>" alt="<?php echo htmlspecialchars($program['title']); ?>" class="program-image">
                        </div>
                        <div class="program-card-body">
                            <p class="card-label">School Program</p>
                            <h3><?php echo htmlspecialchars($program['title']); ?></h3>
                            <p><?php echo htmlspecialchars($program['summary']); ?></p>
                            <div class="program-meta">
                                <span><strong>Duration:</strong> <?php echo htmlspecialchars($program['duration']); ?></span>
                                <span><strong>Level:</strong> Technical Secondary School</span>
                                <span><strong>Mode:</strong> Theory and practical learning</span>
                                <span><strong>Admission:</strong> Required</span>
                            </div>
                            <div class="program-focus-wrap">
                                <p class="program-focus-title">Key Areas</p>
                                <ul class="program-focus-list">
                                    <?php foreach ($program['focus'] as $item): ?>
                                        <li><span><?php echo htmlspecialchars($item); ?></span></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <div class="program-basic-note">
                                <strong>Basic information:</strong>
                                <span><?php echo htmlspecialchars($program['requirements']); ?></span>
                            </div>
                            <a href="/MUBUGA-TSS/pages/admissions.php" class="inline-link">Apply for this program</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>
<?php renderSiteFooter($schoolName); ?>
