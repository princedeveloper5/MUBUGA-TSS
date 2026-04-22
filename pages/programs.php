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
<main id="main-content" class="programs-page">
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

    <section class="section program-journey-section">
        <div class="container">
            <div class="section-heading programs-page-heading">
                <p class="eyebrow">Learning Journey</p>
                <h2>Each trade combines class teaching, workshop practice, and career preparation.</h2>
                <p>Students do not only study theory. They follow a practical path that helps them build confidence and useful technical ability over time.</p>
            </div>
            <div class="program-journey-grid">
                <article class="journey-card">
                    <span class="journey-step">01</span>
                    <h3>Learn the foundations</h3>
                    <p>Students begin with the core subjects, safe working habits, and technical basics needed for steady progress.</p>
                </article>
                <article class="journey-card">
                    <span class="journey-step">02</span>
                    <h3>Practice with guidance</h3>
                    <p>Lessons move into exercises, projects, and workshop tasks that help students apply what they have learned.</p>
                </article>
                <article class="journey-card">
                    <span class="journey-step">03</span>
                    <h3>Prepare for the future</h3>
                    <p>By the end of the program, learners are ready for further study, employment opportunities, and more advanced technical training.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="section program-details-section">
        <div class="container">
            <div class="section-heading programs-page-heading">
                <p class="eyebrow">Program Details</p>
                <h2>A closer look at what students study, use, and build in each program.</h2>
            </div>
            <div class="program-detail-stack">
                <?php foreach ($programs as $program): ?>
                    <article class="program-detail-card">
                        <div class="program-detail-intro">
                            <p class="card-label">Detailed Overview</p>
                            <h3><?php echo htmlspecialchars($program['title']); ?></h3>
                            <p><?php echo htmlspecialchars($program['summary']); ?></p>
                        </div>
                        <div class="program-detail-grid">
                            <div class="program-detail-block">
                                <h4>Main Subjects</h4>
                                <ul class="program-detail-list">
                                    <?php foreach ($program['subjects'] as $subject): ?>
                                        <li><?php echo htmlspecialchars($subject); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <div class="program-detail-block">
                                <h4>Practical Activities</h4>
                                <ul class="program-detail-list">
                                    <?php foreach ($program['practical'] as $activity): ?>
                                        <li><?php echo htmlspecialchars($activity); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <div class="program-detail-block">
                                <h4>Tools and Learning Resources</h4>
                                <ul class="program-detail-list">
                                    <?php foreach ($program['tools'] as $tool): ?>
                                        <li><?php echo htmlspecialchars($tool); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <div class="program-detail-block">
                                <h4>Future Opportunities</h4>
                                <ul class="program-detail-list">
                                    <?php foreach ($program['careers'] as $career): ?>
                                        <li><?php echo htmlspecialchars($career); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="section programs-cta-section">
        <div class="container">
            <div class="programs-cta-panel">
                <div>
                    <p class="eyebrow">Next Step</p>
                    <h2>Ready to join Mubuga TSS?</h2>
                    <p>Explore admission requirements and begin your application for the program that fits your goals.</p>
                </div>
                <div class="programs-cta-actions">
                    <a href="/MUBUGA-TSS/pages/admissions.php" class="button button-primary">Start Application</a>
                    <a href="/MUBUGA-TSS/pages/contact.php" class="button button-secondary">Ask a Question</a>
                </div>
            </div>
        </div>
    </section>
</main>
<?php renderSiteFooter($schoolName); ?>
