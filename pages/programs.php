<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/site_data.php';
require_once __DIR__ . '/../includes/site_layout.php';
renderSiteHeader('Our Programs', $schoolName, $contacts, 'programs');
renderInnerHero('OUR PROGRAMS', 'Technical programs for modern careers', 'Explore the two Mubuga TSS trades and the practical skills they build.', 'assets/images/mb3.jfif');
?>
<main>
    <section class="section">
        <div class="container">
            <div class="program-grid">
                <?php foreach ($programs as $program): ?>
                    <article class="program-card">
                        <img src="/MUBUGA-TSS/<?php echo htmlspecialchars($program['image']); ?>" alt="<?php echo htmlspecialchars($program['title']); ?>" class="program-image">
                        <h3><?php echo htmlspecialchars($program['title']); ?></h3>
                        <p><?php echo htmlspecialchars($program['summary']); ?></p>
                        <ul>
                            <?php foreach ($program['focus'] as $item): ?>
                                <li><?php echo htmlspecialchars($item); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>
<?php renderSiteFooter($schoolName); ?>
