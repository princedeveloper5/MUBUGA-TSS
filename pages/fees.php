<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/site_data.php';
require_once __DIR__ . '/../includes/site_layout.php';

$page = sitePageContent('fees', [
    'title' => 'Fees & Requirements',
    'excerpt' => 'This page is ready for your fee structure image or document when you upload it later.',
    'content' => 'Fee structure',
    'image' => 'assets/images/school view 3.jpg',
]);

renderSiteHeader($page['title'], $schoolName, $contacts, 'admissions');
renderInnerHero('FEES & REQUIREMENTS', $page['title'], $page['excerpt'], $page['image']);
?>
<main>
    <section class="section fees-page-section">
        <div class="container">
            <div class="section-heading">
                <p class="eyebrow">Prepared For Your Upload</p>
                <h2>Your fee structure page is ready.</h2>
                <p>You said you will upload your own fee photo later, so this page is set up as a clean placeholder for that image.</p>
            </div>

            <div class="fees-layout">
                <article class="fees-placeholder-card">
                    <div class="fees-placeholder-frame">
                        <div class="fees-placeholder-sheet">
                            <strong>Fee structure image will appear here</strong>
                            <span>Upload your photo later and we can place it in this section exactly.</span>
                        </div>
                    </div>
                    <div class="fees-placeholder-actions">
                        <a href="/MUBUGA-TSS/pages/registration.php" class="button button-primary">Register Now</a>
                        <a href="/MUBUGA-TSS/pages/contact.php" class="button button-secondary">Contact School</a>
                    </div>
                </article>

                <aside class="fees-note-card">
                    <p class="eyebrow">What Comes Next</p>
                    <h3>Add your own fees image later.</h3>
                    <p>Once you share the fee structure photo, we can place it here, keep the same page, and make it open or zoom clearly for parents and students.</p>
                    <ul class="fees-note-list">
                        <li>Separate fees page already created</li>
                        <li>Ready for one image or document preview</li>
                        <li>Linked with registration action</li>
                    </ul>
                </aside>
            </div>
        </div>
    </section>
</main>
<?php renderSiteFooter($schoolName); ?>
