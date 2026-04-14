<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/site_data.php';
require_once __DIR__ . '/../includes/site_layout.php';
renderSiteHeader('Our Team', $schoolName, $contacts, 'team');
renderInnerHero('OUR TEAM', 'Meet our school leaders', 'The Mubuga TSS team is committed to student growth, technical excellence, and strong school management.', 'assets/images/master.jpeg');
?>
<main>
    <section class="section leadership">
        <div class="container">
            <div class="leadership-grid">
                <?php foreach ($leadership as $member): ?>
                    <article class="leader-card">
                        <img src="/MUBUGA-TSS/<?php echo htmlspecialchars($member['photo']); ?>" alt="<?php echo htmlspecialchars($member['name']); ?>" class="leader-image">
                        <span><?php echo htmlspecialchars($member['role']); ?></span>
                        <h3><?php echo htmlspecialchars($member['name']); ?></h3>
                        <p><?php echo htmlspecialchars($member['text']); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>
<?php renderSiteFooter($schoolName); ?>
