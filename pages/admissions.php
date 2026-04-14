<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/site_data.php';
require_once __DIR__ . '/../includes/site_layout.php';
renderSiteHeader('Admissions', $schoolName, $contacts, 'admissions');
renderInnerHero('ADMISSION', 'Start your journey at Mubuga TSS', 'Find the basic requirements and simple admission pathway for joining one of our technical trades.', 'assets/images/IM8.jpg');
?>
<main>
    <section class="section">
        <div class="container admissions-grid">
            <div>
                <p class="eyebrow">Fees & Requirements</p>
                <h2>What you need to begin.</h2>
                <p>Contact the school for official fees, required documents, and current reporting dates. We recommend choosing the trade that best fits your interests and long-term goals.</p>
            </div>
            <div class="admission-steps">
                <?php foreach ($admissions as $index => $step): ?>
                    <article class="admission-step">
                        <span>0<?php echo $index + 1; ?></span>
                        <p><?php echo htmlspecialchars($step); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>
<?php renderSiteFooter($schoolName); ?>
