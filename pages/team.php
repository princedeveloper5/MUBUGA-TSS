<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/site_data.php';
require_once __DIR__ . '/../includes/site_layout.php';
require_once __DIR__ . '/../portal/header.php';
require_once __DIR__ . '/../portal/footer.php';

$page = sitePageContent('our-team', [
    'title' => 'Mubuga TSS - Meet Our Team',
    'excerpt' => 'Dedicated to Guiding & Inspiring Our Students',
    'content' => 'Get to know the leaders and educators of Mubuga TSS',
    'image' => 'assets/images/master.jpeg',
]);

renderSiteHeader($page['title'], $schoolName, $contacts, 'team', [
    'description' => $page['excerpt'],
    'image' => $page['image'],
]);
?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-content">
        <h1>Our Team</h1>
        <p>Professional educators dedicated to excellence</p>
    </div>
</section>

<!-- Team Section -->
<section class="team">
    <div class="team-container">

        <!-- Administration -->
        <div class="team-section admin">
            <h2>Administration</h2>
            <div class="profiles">
                <div class="profile-card">
                    <img src="/MUBUGA-TSS/assets/images/master.jpeg" alt="Principal John Karangwa">
                    <h3>Principal</h3>
                    <p>John Karangwa</p>
                    <button class="btn-blue">View Profile</button>
                </div>
                <div class="profile-card">
                    <img src="/MUBUGA-TSS/assets/images/master.jpeg" alt="Head of ICT Alice Nshimiyimana">
                    <h3>Head of ICT</h3>
                    <p>Alice Nshimiyimana</p>
                    <button class="btn-blue">View Profile</button>
                </div>
                <div class="profile-card">
                    <img src="/MUBUGA-TSS/assets/images/master.jpeg" alt="Academic Director Eric Mugisha">
                    <h3>Academic Director</h3>
                    <p>Eric Mugisha</p>
                    <button class="btn-blue">View Profile</button>
                </div>
                <div class="profile-card">
                    <img src="/MUBUGA-TSS/assets/images/master.jpeg" alt="Finance Officer Grace Uwimana">
                    <h3>Finance Officer</h3>
                    <p>Grace Uwimana</p>
                    <button class="btn-blue">View Profile</button>
                </div>
                <div class="profile-card">
                    <img src="/MUBUGA-TSS/assets/images/master.jpeg" alt="Student Affairs David Mukamana">
                    <h3>Student Affairs</h3>
                    <p>David Mukamana</p>
                    <button class="btn-blue">View Profile</button>
                </div>
                <div class="profile-card">
                    <img src="/MUBUGA-TSS/assets/images/master.jpeg" alt="Administrative Assistant Sarah Niyonzima">
                    <h3>Administrative Assistant</h3>
                    <p>Sarah Niyonzima</p>
                    <button class="btn-blue">View Profile</button>
                </div>
            </div>
        </div>

        <!-- Teaching Staff -->
        <div class="team-section teaching">
            <h2>Teaching Staff</h2>
            <div class="profiles">
                <div class="profile-card">
                    <img src="/MUBUGA-TSS/assets/images/master.jpeg" alt="ICT Instructor Eric Mukamana">
                    <h3>ICT Instructor</h3>
                    <p>Eric Mukamana</p>
                    <button class="btn-yellow">View Profile</button>
                </div>
                <div class="profile-card">
                    <img src="/MUBUGA-TSS/assets/images/master.jpeg" alt="Electrical Instructor David Mugisha">
                    <h3>Electrical Instructor</h3>
                    <p>David Mugisha</p>
                    <button class="btn-yellow">View Profile</button>
                </div>
                <div class="profile-card">
                    <img src="/MUBUGA-TSS/assets/images/master.jpeg" alt="Math Teacher Grace Uwimana">
                    <h3>Math Teacher</h3>
                    <p>Grace Uwimana</p>
                    <button class="btn-yellow">View Profile</button>
                </div>
                <div class="profile-card">
                    <img src="/MUBUGA-TSS/assets/images/master.jpeg" alt="Physics Teacher John Karangwa">
                    <h3>Physics Teacher</h3>
                    <p>John Karangwa</p>
                    <button class="btn-yellow">View Profile</button>
                </div>
                <div class="profile-card">
                    <img src="/MUBUGA-TSS/assets/images/master.jpeg" alt="English Teacher Alice Nshimiyimana">
                    <h3>English Teacher</h3>
                    <p>Alice Nshimiyimana</p>
                    <button class="btn-yellow">View Profile</button>
                </div>
                <div class="profile-card">
                    <img src="/MUBUGA-TSS/assets/images/master.jpeg" alt="Chemistry Teacher Sarah Mukamana">
                    <h3>Chemistry Teacher</h3>
                    <p>Sarah Mukamana</p>
                    <button class="btn-yellow">View Profile</button>
                </div>
                <div class="profile-card">
                    <img src="/MUBUGA-TSS/assets/images/master.jpeg" alt="Biology Teacher Eric Niyonzima">
                    <h3>Biology Teacher</h3>
                    <p>Eric Niyonzima</p>
                    <button class="btn-yellow">View Profile</button>
                </div>
            </div>
        </div>

    </div>

    <!-- Facilities -->
    <div class="facilities">
        <div class="facility-card">
            <img src="/MUBUGA-TSS/assets/images/software development.jpg" alt="ICT Labs">
            <h4>ICT Labs</h4>
            <button class="btn-blue">View Profile</button>
        </div>
        <div class="facility-card">
            <img src="/MUBUGA-TSS/assets/images/electrical technology.JPG" alt="Electrical Workshops">
            <h4>Electrical Workshops</h4>
            <button class="btn-blue">View Profile</button>
        </div>
        <div class="facility-card">
            <img src="/MUBUGA-TSS/assets/images/students.jfif" alt="Student Support">
            <h4>Student Support</h4>
            <button class="btn-blue">View Profile</button>
        </div>
    </div>

    <!-- Footer Buttons -->
    <div class="footer-buttons">
        <button class="btn-blue">View All Administration</button>
        <button class="btn-yellow">View All Teaching Staff</button>
    </div>
</section>

<?php renderSiteFooter($schoolName); ?>
