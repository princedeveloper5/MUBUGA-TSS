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

$programIcons = [
    'Software Development' => 'code',
    'Electrical Technology' => 'bolt',
];
?>
<main id="main-content" class="programs-page">
    <section class="programs-hero-banner">
        <div class="container programs-hero-banner-inner">
            <div class="programs-hero-copy">
                <p class="programs-hero-breadcrumb"><a href="/MUBUGA-TSS/">Home</a><span>&gt;</span><span>Programs</span></p>
                <h1>Our Programs</h1>
                <span class="programs-hero-accent" aria-hidden="true"></span>
                <p>We offer high quality technical and professional programs designed to equip students with practical skills and knowledge for a successful future.</p>
            </div>
            <div class="programs-hero-image-wrap">
                <img src="/MUBUGA-TSS/assets/images/school view 2.jpg" alt="Mubuga TSS campus" class="programs-hero-image photo-viewer">
            </div>
        </div>
    </section>
    <section class="section programs-page-section">
        <div class="container">
            <div class="section-heading programs-page-heading programs-page-heading-centered">
                <h2>Programs We Offer</h2>
                <p>Choose a program that matches your passion and build a career for tomorrow.</p>
            </div>
            <div class="program-showcase-grid">
                <?php foreach ($programs as $program): ?>
                    <?php $iconType = $programIcons[$program['title']] ?? 'code'; ?>
                    <?php $programHighlights = array_slice(array_values(array_unique(array_merge($program['focus'], $program['subjects']))), 0, 4); ?>
                    <article class="program-showcase-card">
                        <div class="program-showcase-media">
                            <img src="/MUBUGA-TSS/<?php echo htmlspecialchars($program['image']); ?>" alt="<?php echo htmlspecialchars($program['title']); ?>" class="program-showcase-image photo-viewer">
                        </div>
                        <div class="program-showcase-body">
                            <div class="program-showcase-icon" aria-hidden="true">
                                <?php if ($iconType === 'bolt'): ?>
                                    <svg viewBox="0 0 24 24" role="presentation" focusable="false">
                                        <path d="M13 2L5 14h5l-1 8 8-12h-5l1-8z" fill="currentColor"></path>
                                    </svg>
                                <?php else: ?>
                                    <svg viewBox="0 0 24 24" role="presentation" focusable="false">
                                        <path d="M8 8h8v2H8V8zm0 6h5v2H8v-2zm10-9H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2zm0 12H6V7h12v10z" fill="currentColor"></path>
                                    </svg>
                                <?php endif; ?>
                            </div>
                            <h3><?php echo htmlspecialchars($program['title']); ?></h3>
                            <p><?php echo htmlspecialchars($program['summary']); ?></p>
                            <ul class="program-showcase-list">
                                <?php foreach ($programHighlights as $item): ?>
                                    <li><?php echo htmlspecialchars($item); ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <a href="/MUBUGA-TSS/pages/admissions.php" class="button button-primary program-showcase-link">Learn More <span aria-hidden="true">&rarr;</span></a>
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
