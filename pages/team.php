<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/site_data.php';
require_once __DIR__ . '/../includes/site_layout.php';
require_once __DIR__ . '/../portal/header.php';
require_once __DIR__ . '/../portal/footer.php';

$page = sitePageContent('our-team', [
    'title' => 'Our Team',
    'excerpt' => 'Meet our team.',
    'content' => 'Technical education experts.',
    'image' => 'assets/images/master.jpeg',
]);

renderSiteHeader($page['title'], $schoolName, $contacts, 'team', [
    'description' => $page['excerpt'],
    'image' => $page['image'],
]);
renderInnerHero('OUR TEAM', $page['content'], $page['excerpt'], $page['image']);
?>
<main>
    <!-- Team Main Section -->
    <section class="section team-main-section">
        <div class="container">
            <div class="section-intro">
                <p class="eyebrow">Meet Our Team</p>
                <h2>Professional Excellence</h2>
                <p>Technical education experts for student success.</p>
            </div>
            
            <!-- Team Unified Image -->
            <div class="team-unified-image">
                <img src="assets/images/team-photo.jpg" alt="Mubuga TSS Team" class="team-main-image">
                <div class="team-image-overlay">
                    <h3>Our Professional Team</h3>
                    <p>Educators and staff for your success</p>
                </div>
            </div>
            
            <!-- Team Categories Grid -->
            <div class="team-categories-grid">
                <div class="team-category-card">
                    <h3>Teaching Staff</h3>
                    <ul>
                        <li>Technical Instructors</li>
                        <li>Lab Coordinators</li>
                        <li>Curriculum Developers</li>
                        <li>Academic Advisors</li>
                    </ul>
                </div>
                
                <div class="team-category-card">
                    <h3>Administrative Team</h3>
                    <ul>
                        <li>School Principal</li>
                        <li>Academic Director</li>
                        <li>Student Affairs</li>
                        <li>Finance Administrator</li>
                    </ul>
                </div>
                
                <div class="team-category-card">
                    <h3>Support Staff</h3>
                    <ul>
                        <li>Counselors</li>
                        <li>Librarians</li>
                        <li>Health Officer</li>
                        <li>Sports Coaches</li>
                    </ul>
                </div>
            </div>
            
            <!-- Team Members Grid -->
            <div class="team-members-grid">
                <?php 
                $count = 0;
                foreach ($leadership as $member): 
                    if ($count < 2): // Show only first 2 members
                ?>
                    <article class="team-member-card">
                        <div class="member-photo">
                            <img src="/MUBUGA-TSS/<?php echo htmlspecialchars($member['photo']); ?>" alt="<?php echo htmlspecialchars($member['name']); ?>" class="member-image">
                        </div>
                        <div class="member-info">
                            <div class="member-role"><?php echo htmlspecialchars($member['role']); ?></div>
                            <h3><?php echo htmlspecialchars($member['name']); ?></h3>
                            <p><?php echo htmlspecialchars($member['text']); ?></p>
                        </div>
                    </article>
                <?php 
                    endif;
                    $count++;
                    endforeach; 
                ?>
            </div>
        </div>
    </section>
</main>
<?php renderSiteFooter($schoolName); ?>
