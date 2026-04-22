<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/site_data.php';
require_once __DIR__ . '/../includes/site_layout.php';
require_once __DIR__ . '/../portal/header.php';
require_once __DIR__ . '/../portal/footer.php';
$page = sitePageContent('contacts', [
    'title' => 'Contacts',
    'excerpt' => 'Contact the school office for admissions, fees, or general information about our programs.',
    'content' => 'Reach Mubuga TSS for more information.',
    'image' => 'assets/images/mb2.jfif',
]);
renderSiteHeader($page['title'], $schoolName, $contacts, 'contact', [
    'description' => $page['excerpt'],
    'image' => $page['image'],
]);
renderInnerHero('CONTACTS', $page['content'], $page['excerpt'], $page['image']);
?>
<main>
    <section class="section cta">
        <div class="container contact-grid">
            <div class="cta-panel">
                <div class="cta-copy">
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
                <div class="contact-extra">
                    <div class="contact-extra-item">
                        <strong>Office Hours</strong>
                        <span>Monday to Friday, 7AM - 5PM</span>
                    </div>
                    <div class="contact-extra-item">
                        <strong>School Guidance</strong>
                        <span>Contact the office for admissions, programs, and student support information.</span>
                    </div>
                </div>
            </aside>
        </div>
    </section>

    <section class="section">
        <div class="container section-grid">
            <div class="about-copy">
                <p class="eyebrow">Send A Message</p>
                <h2>Contact the school directly.</h2>
                <p>Use the form below to send us your question, request more information, or follow up on admissions.</p>
                <div class="about-facts">
                    <article class="about-fact">
                        <span>Response Type</span>
                        <strong>General questions, admissions, and follow-up support</strong>
                    </article>
                    <article class="about-fact">
                        <span>Best Practice</span>
                        <strong>Include clear contact details so the school can respond quickly</strong>
                    </article>
                </div>
            </div>
            <div class="feature-card">
                <form method="post" action="/MUBUGA-TSS/handlers/site_forms.php" class="public-form">
                    <input type="hidden" name="form_action" value="contact_message">
                    <input type="hidden" name="redirect_to" value="/MUBUGA-TSS/pages/contact.php">
                    <label>
                        <span>Full Name</span>
                        <input type="text" name="full_name" required>
                    </label>
                    <label>
                        <span>Email</span>
                        <input type="email" name="email" required>
                    </label>
                    <label>
                        <span>Phone</span>
                        <input type="text" name="phone">
                    </label>
                    <label>
                        <span>Subject</span>
                        <input type="text" name="subject">
                    </label>
                    <label>
                        <span>Message</span>
                        <textarea name="message_body" rows="6" required></textarea>
                    </label>
                    <button type="submit" class="button button-primary">Send Message</button>
                </form>
            </div>
        </div>
    </section>
</main>
<?php renderSiteFooter($schoolName); ?>
