<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/site_data.php';
require_once __DIR__ . '/../includes/site_layout.php';
$page = sitePageContent('about-us', [
    'title' => 'About Us',
    'excerpt' => 'Learn the story, mission, vision, and values that shape Mubuga TSS.',
    'content' => $schoolName . ' is committed to helping learners grow into capable professionals and responsible citizens through strong technical education and a disciplined school culture.',
    'image' => 'assets/images/students.jfif',
]);
renderSiteHeader($page['title'], $schoolName, $contacts, 'about', [
    'description' => $page['excerpt'],
    'image' => $page['image'],
]);
renderInnerHero('WELCOME TO', $schoolName, $page['excerpt'], $page['image']);
?>
<main>
    <section class="section">
        <div class="container section-grid">
            <div class="about-copy">
                <p class="eyebrow">Our Story</p>
                <h2>A focused TVET school with practical ambition.</h2>
                <p><?php echo htmlspecialchars($page['content']); ?></p>
                <p>Our school centers learning around Software Development and Electrical Technology, with a strong emphasis on practical application, teamwork, and career readiness.</p>
                <div class="about-facts">
                    <article class="about-fact">
                        <span>School Character</span>
                        <strong>Disciplined, practical, and future-focused learning</strong>
                    </article>
                    <article class="about-fact">
                        <span>Main Priority</span>
                        <strong>Technical growth with strong student values</strong>
                    </article>
                </div>
            </div>
            <div class="feature-stack">
                <article class="feature-card">
                    <h3>Our Motto</h3>
                    <p>Discipline, competence, and innovation in technical education.</p>
                </article>
                <article class="feature-card">
                    <h3>Vision</h3>
                    <p>To become a respected center of technical excellence that prepares students for opportunity, service, and innovation.</p>
                </article>
                <article class="feature-card">
                    <h3>Mission</h3>
                    <p>To equip learners with practical skills, discipline, and creativity through focused vocational education.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="section values">
        <div class="container">
            <div class="section-heading">
                <p class="eyebrow">Core Values</p>
                <h2>The principles that guide daily school life.</h2>
            </div>
            <div class="values-grid expanded-values">
                <?php foreach ($values as $value): ?>
                    <article class="value-card">
                        <h3><?php echo htmlspecialchars($value); ?></h3>
                        <p>Part of the culture we expect students and staff to live out in learning, leadership, and service.</p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>
<?php renderSiteFooter($schoolName); ?>
