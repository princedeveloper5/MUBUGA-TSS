<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/site_data.php';
require_once __DIR__ . '/includes/site_layout.php';
require_once __DIR__ . '/portal/header.php';
require_once __DIR__ . '/portal/footer.php';

$homepageGallery = array_values(array_filter($gallery, static function (array $item): bool {
    return (string) ($item['media_type'] ?? 'image') !== 'video';
}));
$galleryLead = $homepageGallery[0] ?? null;
$galleryHighlights = array_slice($homepageGallery, 1, 3); // Limit to 3 for preview

// Get logo path for footer
$logoPath = (string) ($siteMeta['logo_path'] ?? '');
if ($logoPath === '') {
    $logoPath = 'assets/images/MUBUGA LOGO SN.PNG';
}

// Get social media URLs - ensure they're always defined
$facebookUrl = '#';
$instagramUrl = '#';
$twitterUrl = '#';

// Try to get from siteMeta if available
if (isset($siteMeta['facebook_url'])) {
    $facebookUrl = (string) $siteMeta['facebook_url'];
}
if (isset($siteMeta['instagram_url'])) {
    $instagramUrl = (string) $siteMeta['instagram_url'];
}
if (isset($siteMeta['twitter_url'])) {
    $twitterUrl = (string) $siteMeta['twitter_url'];
}

// Render the proper header
renderSiteHeader('Home', $schoolName, $contacts, 'home', [
    'description' => 'Official website of Mubuga TSS, featuring Software Development and Electrical Technology programs.',
    'image' => 'assets/images/student in practical.jpeg',
]);

// Add form feedback if present
if (isset($_GET['form_status']) && isset($_GET['form_message'])) {
    $formStatus = (string) $_GET['form_status'];
    $formMessage = (string) $_GET['form_message'];
    ?>
    <div class="container form-feedback-wrap">
        <div class="form-feedback form-feedback-<?php echo htmlspecialchars($formStatus); ?>">
            <?php echo htmlspecialchars($formMessage); ?>
        </div>
    </div>
    <?php
}
?>

    <main id="main-content">
        <!-- Hero Banner Section -->
        <section class="hero-section" id="home">
            <div class="hero-background" id="hero-background">
                <div class="container">
                    <div class="hero-content">
                        <div class="hero-text">
                            <h1 id="hero-title">Excellence in technical education</h1>
                            <p id="hero-description">Join Mubuga TSS and build your future with practical skills in Software Development and Electrical Technology.</p>
                            <div class="hero-actions">
                                <a href="/MUBUGA-TSS/pages/admissions.php" class="button button-primary">Register</a>
                                <a href="/MUBUGA-TSS/pages/programs.php" class="button button-secondary">Explore Programs</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Arrow Controls -->
                <button class="hero-arrow hero-arrow-prev" id="hero-prev" aria-label="Previous image">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                        <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
                    </svg>
                </button>
                <button class="hero-arrow hero-arrow-next" id="hero-next" aria-label="Next image">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                        <path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/>
                    </svg>
                </button>
            </div>
        </section>

        
        <!-- Welcome Message Section -->
        <section class="section welcome" id="welcome">
            <div class="container">
                <div class="section-heading">
                    <h2>Welcome to Mubuga TSS</h2>
                    <p>Your gateway to technical excellence</p>
                </div>
                
                <div class="welcome-content">
                    <div class="welcome-text">
                        <p>At Mubuga Technical Secondary School, we are committed to providing quality technical education that prepares students for successful careers in the modern workforce. Our comprehensive programs combine theoretical knowledge with practical skills to ensure our graduates are ready for the challenges of today's industries.</p>
                        <p>Join us in shaping the future of technical education and building the next generation of skilled professionals.</p>
                    </div>
                    <div class="welcome-cta">
                        <a href="/MUBUGA-TSS/pages/admissions.php" class="button button-primary">Start Your Journey</a>
                        <a href="/MUBUGA-TSS/pages/programs.php" class="button button-secondary">Explore Programs</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- About Us Section -->
        <section class="section about" id="about">
            <div class="container">
                <div class="section-heading">
                    <h2>About Us</h2>
                    <p>Our foundation and values</p>
                </div>
                
                <div class="about-grid">
                    <div class="about-item">
                        <div class="about-icon">
                            <svg viewBox="0 0 24 24" width="32" height="32" fill="currentColor">
                                <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/>
                            </svg>
                        </div>
                        <div class="about-content">
                            <h3>Vision</h3>
                            <p>To prepare learners for employment, entrepreneurship, and further technical growth through quality training.</p>
                        </div>
                    </div>
                    
                    <div class="about-item">
                        <div class="about-icon">
                            <svg viewBox="0 0 24 24" width="32" height="32" fill="currentColor">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                            </svg>
                        </div>
                        <div class="about-content">
                            <h3>Mission</h3>
                            <p>To transform student potential into practical competence through focused technical learning and strong character formation.</p>
                        </div>
                    </div>
                    
                    <div class="about-item">
                        <div class="about-icon">
                            <svg viewBox="0 0 24 24" width="32" height="32" fill="currentColor">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                        </div>
                        <div class="about-content">
                            <h3>Core Values</h3>
                            <ul>
                                <li>Discipline</li>
                                <li>Integrity</li>
                                <li>Practical Excellence</li>
                                <li>Innovation</li>
                                <li>Teamwork</li>
                                <li>Responsibility</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Programs Section -->
        <section class="section programs" id="programs">
            <div class="container">
                <div class="section-heading">
                    <h2>Our Programs</h2>
                    <p>Two specialized technical trades for modern careers</p>
                </div>
                
                <div class="programs-grid">
                    <?php foreach ($programs as $program): ?>
                        <article class="program-card">
                            <?php if (!empty($program['image'])): ?>
                                <img src="<?php echo htmlspecialchars($program['image']); ?>" alt="<?php echo htmlspecialchars($program['title']); ?>" class="program-image">
                            <?php endif; ?>
                            <div class="program-content">
                                <h3><?php echo htmlspecialchars($program['title']); ?></h3>
                                <p><?php echo htmlspecialchars($program['summary']); ?></p>
                                <ul>
                                    <?php foreach ($program['focus'] as $item): ?>
                                        <li><?php echo htmlspecialchars($item); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <a href="/MUBUGA-TSS/pages/admissions.php" class="button button-primary">Register Now</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        
        
                            <?php 
                    // Get gallery items after the first 3 highlights
                    $previousGalleryItems = array_slice($gallery ?? [], 3, 6);
                    foreach ($previousGalleryItems as $item): 
                    ?>
                        <article class="previous-gallery-card">
                            <div class="previous-gallery-image">
                                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                            </div>
                            <div class="previous-gallery-content">
                                <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                                <p><?php echo htmlspecialchars($item['text']); ?></p>
                                <span class="gallery-category"><?php echo htmlspecialchars($item['category_label'] ?? 'Gallery'); ?></span>
                            </div>
                        </article>
                    <?php endforeach; ?>
                    
                    <!-- Electrical Event Card -->
                    <article class="previous-gallery-card">
                        <div class="previous-gallery-image">
                            <img src="/MUBUGA-TSS/assets/images/electrical technology 2.jpeg" alt="Electrical Technology Event">
                        </div>
                        <div class="previous-gallery-content">
                            <h3>Electrical Technology Event</h3>
                            <p>Students showcase their electrical installation and maintenance skills during our annual technology exhibition.</p>
                            <span class="gallery-category">Electrical Event</span>
                        </div>
                    </article>
                    
                    <!-- Software Computer Lab Card -->
                    <article class="previous-gallery-card">
                        <div class="previous-gallery-image">
                            <img src="/MUBUGA-TSS/assets/images/software development 4.jpeg" alt="Software Computer Lab">
                        </div>
                        <div class="previous-gallery-content">
                            <h3>Software Computer Lab</h3>
                            <p>Students develop programming skills and work on software projects in our modern computer laboratory.</p>
                            <span class="gallery-category">Software Lab</span>
                        </div>
                    </article>
                </div>
                
                <div class="section-more">
                    <a href="/MUBUGA-TSS/pages/gallery.php" class="button button-primary">View Full Gallery</a>
                </div>
            </div>
        </section>

        <!-- Leadership Section -->
        <section class="section leadership" id="leadership">
            <div class="container">
                <div class="section-heading">
                    <h2>Leadership</h2>
                    <p>Meet our school administration and training team</p>
                </div>
                
                <div class="leadership-categories">
                    <article class="leadership-category-card" style="background-image: url('/MUBUGA-TSS/assets/images/school view 8.jpeg');">
                        <div class="category-overlay">
                            <div class="category-icon">
                                <svg viewBox="0 0 24 24" width="40" height="40" fill="currentColor">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                </svg>
                            </div>
                            <div class="category-content">
                                <h3>Administration</h3>
                                <p>School management and administrative staff</p>
                                <a href="/MUBUGA-TSS/pages/team.php" class="button button-primary">View All</a>
                            </div>
                        </div>
                    </article>
                    
                    <article class="leadership-category-card" style="background-image: url('/MUBUGA-TSS/assets/images/software development 4.jpeg');">
                        <div class="category-overlay">
                            <div class="category-icon">
                                <svg viewBox="0 0 24 24" width="40" height="40" fill="currentColor">
                                    <path d="M5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82zM12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/>
                                </svg>
                            </div>
                            <div class="category-content">
                                <h3>Teaching Staff</h3>
                                <p>Experienced teachers and technical instructors</p>
                                <a href="/MUBUGA-TSS/pages/team.php" class="button button-primary">View All</a>
                            </div>
                        </div>
                    </article>
                </div>
                
                <!-- Leadership Details Section (Hidden by default) -->
                <div id="leadership-details" class="leadership-details" style="display: none;">
                    <div class="details-header">
                        <h3 id="details-title">Team Members</h3>
                        <button class="close-button" onclick="hideLeadershipDetails()">×</button>
                    </div>
                    <div class="leadership-grid" id="leadership-members">
                        <!-- Members will be dynamically loaded here -->
                    </div>
                </div>
            </div>
        </section>

        <!-- Admissions Steps Section -->
        <section class="section admissions" id="admissions">
            <div class="container">
                <div class="section-heading">
                    <h2>Admissions Steps</h2>
                    <p>How to join Mubuga TSS</p>
                </div>
                
                <div class="admissions-steps">
                    <div class="admission-step">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h3>Choose Trade</h3>
                            <p>Select the technical trade that matches your interests and career goals.</p>
                        </div>
                    </div>
                    
                    <div class="admission-step">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h3>Prepare Documents</h3>
                            <p>Gather your academic records and prepare the necessary registration documents.</p>
                        </div>
                    </div>
                    
                    <div class="admission-step">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h3>Contact School</h3>
                            <p>Reach out to our admissions office for guidance on reporting and requirements.</p>
                        </div>
                    </div>
                </div>
                <div class="admissions-cta">
                    <a href="/MUBUGA-TSS/pages/admissions.php" class="button button-primary">Start Application</a>
                </div>
            </div>
        </section>

        <!-- Latest News Section -->
        <section class="section news" id="news">
            <div class="container">
                <div class="section-heading">
                    <h2>Latest Updates</h2>
                    <p>News, Events & Announcements</p>
                </div>
                
                <div class="news-grid">
                    <!-- All News Card -->
                    <article class="news-card" style="background-image: url('assets/images/events.jpg');">
                        <div class="news-card-overlay">
                            <div class="news-content">
                                <h3>All News</h3>
                                <p>Stay updated with the latest news and announcements from Mubuga TSS.</p>
                                <a href="/MUBUGA-TSS/pages/news.php" class="button button-primary">View All News</a>
                            </div>
                        </div>
                    </article>
                    
                    <!-- Events Card -->
                    <article class="news-card" style="background-image: url('assets/images/events.jpg');">
                        <div class="news-card-overlay">
                            <div class="news-content">
                                <h3>Upcoming Events</h3>
                                <p>Join us for exciting events and activities at our school.</p>
                                <a href="/MUBUGA-TSS/pages/events.php" class="button button-primary">View All Events</a>
                            </div>
                        </div>
                    </article>
                    
                    <!-- Announcements Card -->
                    <article class="news-card" style="background-image: url('assets/images/events.jpg');">
                        <div class="news-card-overlay">
                            <div class="news-content">
                                <h3>Announcements</h3>
                                <p>Important announcements and updates for students and parents.</p>
                                <a href="/MUBUGA-TSS/pages/announcements.php" class="button button-primary">View All Announcements</a>
                            </div>
                        </div>
                    </article>
                </div>
            </div>
        </section>
    </main>
<?php renderSiteFooter($schoolName); ?>

<script>
// Hero Background Rotation with Corresponding Text
const heroData = [
    {
        image: '/MUBUGA-TSS/assets/images/school view 8.jpeg',
        title: 'Excellence in technical education',
        description: 'Join Mubuga TSS and build your future with practical skills in Software Development and Electrical Technology.'
    },
    {
        image: '/MUBUGA-TSS/assets/images/software development 4.jpeg',
        title: 'Master Software Development',
        description: 'Learn programming, web development, and digital problem-solving skills for the modern tech world.'
    },
    {
        image: '/MUBUGA-TSS/assets/images/electrical technology 2.jpeg',
        title: 'Excel in Electrical Technology',
        description: 'Gain hands-on experience in electrical installation, maintenance, and practical workshop training.'
    }
];

let currentSlide = 0;
const heroBackground = document.getElementById('hero-background');
const heroTitle = document.getElementById('hero-title');
const heroDescription = document.getElementById('hero-description');

function updateHeroContent(index) {
    const slide = heroData[index];
    
    // Update background image
    heroBackground.style.backgroundImage = `url('${slide.image}')`;
    
    // Update text with fade effect
    heroTitle.style.opacity = '0';
    heroDescription.style.opacity = '0';
    
    setTimeout(() => {
        heroTitle.textContent = slide.title;
        heroDescription.textContent = slide.description;
        heroTitle.style.opacity = '1';
        heroDescription.style.opacity = '1';
    }, 300);
}

function rotateHero() {
    currentSlide = (currentSlide + 1) % heroData.length;
    updateHeroContent(currentSlide);
}

// Manual navigation functions
function goToSlide(index) {
    currentSlide = index;
    updateHeroContent(currentSlide);
}

function nextSlide() {
    currentSlide = (currentSlide + 1) % heroData.length;
    updateHeroContent(currentSlide);
}

function prevSlide() {
    currentSlide = (currentSlide - 1 + heroData.length) % heroData.length;
    updateHeroContent(currentSlide);
}

// Add event listeners for arrow controls
const heroPrev = document.getElementById('hero-prev');
const heroNext = document.getElementById('hero-next');

if (heroPrev) {
    heroPrev.addEventListener('click', (e) => {
        e.preventDefault();
        prevSlide();
    });
}

if (heroNext) {
    heroNext.addEventListener('click', (e) => {
        e.preventDefault();
        nextSlide();
    });
}

// Initialize first slide
updateHeroContent(0);

// Set up rotation every 5 seconds
const rotationInterval = setInterval(rotateHero, 5000);

// Pause auto-rotation when user interacts
function pauseRotation() {
    clearInterval(rotationInterval);
}

// Resume auto-rotation after 10 seconds of inactivity
let resumeTimeout;
function resumeRotation() {
    clearTimeout(resumeTimeout);
    resumeTimeout = setInterval(rotateHero, 5000);
}

// Add event listeners to pause/resume rotation
[heroPrev, heroNext].forEach(button => {
    if (button) {
        button.addEventListener('click', () => {
            pauseRotation();
            resumeRotation();
        });
    }
});

// Add transition styles for text
heroTitle.style.transition = 'opacity 0.5s ease-in-out';
heroDescription.style.transition = 'opacity 0.5s ease-in-out';

// Leadership Details Functionality
function showLeadershipDetails(category) {
    const detailsSection = document.getElementById('leadership-details');
    const detailsTitle = document.getElementById('details-title');
    const leadershipMembers = document.getElementById('leadership-members');
    
    // Update title based on category
    if (category === 'administration') {
        detailsTitle.textContent = 'Administration Team';
    } else if (category === 'teaching') {
        detailsTitle.textContent = 'Teaching Staff';
    }
    
    // Sample leadership data (this should come from your PHP data)
    const leadershipData = [
        {
            name: 'John Smith',
            role: 'School Director',
            photo: '/MUBUGA-TSS/assets/images/master.jpeg',
            text: 'Leading the school with vision and dedication to technical education excellence.',
            category: 'administration'
        },
        {
            name: 'Jane Doe',
            role: 'Academic Coordinator',
            photo: '/MUBUGA-TSS/assets/images/master.jpeg',
            text: 'Managing curriculum development and academic programs.',
            category: 'administration'
        },
        {
            name: 'Robert Johnson',
            role: 'Software Development Instructor',
            photo: '/MUBUGA-TSS/assets/images/master.jpeg',
            text: 'Teaching programming and web development with practical industry experience.',
            category: 'teaching'
        },
        {
            name: 'Sarah Williams',
            role: 'Electrical Technology Instructor',
            photo: '/MUBUGA-TSS/assets/images/master.jpeg',
            text: 'Providing hands-on training in electrical installation and maintenance.',
            category: 'teaching'
        }
    ];
    
    // Filter members by category
    const filteredMembers = leadershipData.filter(member => member.category === category);
    
    // Generate HTML for filtered members
    let membersHTML = '';
    filteredMembers.forEach(member => {
        membersHTML += `
            <article class="leader-card">
                <img src="${member.photo}" alt="${member.name}" class="leader-image">
                <div class="leader-content">
                    <h3>${member.name}</h3>
                    <span class="leader-role">${member.role}</span>
                    <p>${member.text}</p>
                </div>
            </article>
        `;
    });
    
    // Update the leadership members container
    leadershipMembers.innerHTML = membersHTML;
    
    // Show the details section with animation
    detailsSection.style.display = 'block';
    detailsSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function hideLeadershipDetails() {
    const detailsSection = document.getElementById('leadership-details');
    detailsSection.style.display = 'none';
}
</script>
<script src="/MUBUGA-TSS/assets/js/site.js"></script>
</body>
</html>
