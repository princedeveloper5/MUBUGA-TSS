<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/site_data.php';
require_once __DIR__ . '/../includes/site_layout.php';
renderSiteHeader('Contacts', $schoolName, $contacts, 'contact');
renderInnerHero('CONTACTS', 'Reach Mubuga TSS for more information', 'Contact the school office for admissions, fees, or general information about our programs.', 'assets/images/mb2.jfif');
?>
<main>
    <section class="section cta">
        <div class="container contact-grid">
            <div class="cta-panel">
                <div>
                    <p class="eyebrow">For More Info</p>
                    <h2>Reach the school office.</h2>
                    <p>We are ready to guide students, parents, and guardians on admissions and school information.</p>
                </div>
                <div class="cta-actions">
                    <a href="mailto:<?php echo htmlspecialchars($contacts[0]['value']); ?>" class="button button-primary">Email The School</a>
                    <a href="/MUBUGA-TSS/pages/admissions.php" class="button button-secondary">Admission Info</a>
                </div>
            </div>

            <aside class="contact-card">
                <p class="eyebrow">Contact Details</p>
                <h3>Office information</h3>
                <div class="contact-list">
                    <?php foreach ($contacts as $contact): ?>
                        <div class="contact-item">
                            <strong><?php echo htmlspecialchars($contact['label']); ?></strong>
                            <span><?php echo htmlspecialchars($contact['value']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </aside>
        </div>
    </section>
</main>
<?php renderSiteFooter($schoolName); ?>
