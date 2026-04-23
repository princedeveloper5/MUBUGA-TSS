<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/site_data.php';
require_once __DIR__ . '/../includes/site_layout.php';
require_once __DIR__ . '/../includes/admin_upload.php';
require_once __DIR__ . '/../portal/header.php';
require_once __DIR__ . '/../portal/footer.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getDatabaseConnection();
    $filePath = '';

    if ($pdo instanceof PDO) {
        if (!empty($_FILES['results_report']) && isset($_FILES['results_report']['tmp_name']) && is_uploaded_file($_FILES['results_report']['tmp_name'])) {
            $file = $_FILES['results_report'];
            $allowedExtensions = ['jpg', 'jpeg', 'png'];
            $extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
            if (in_array($extension, $allowedExtensions, true)) {
                $uploadDir = dirname(__DIR__) . '/assets/uploads';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $baseName = preg_replace('/[^a-zA-Z0-9_-]+/', '-', pathinfo((string) ($file['name'] ?? ''), PATHINFO_FILENAME));
                $baseName = trim((string) $baseName, '-');
                if ($baseName === '') {
                    $baseName = 'report';
                }
                $targetName = $baseName . '-' . date('YmdHis') . '.' . $extension;
                $targetPath = $uploadDir . '/' . $targetName;
                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    $filePath = 'assets/uploads/' . $targetName;
                }
            }
        }

        try {
            $applicantName = trim(sprintf('%s %s', $_POST['first_name'] ?? '', $_POST['last_name'] ?? ''));
            $notesParts = [];
            $notesParts[] = 'Program level: ' . trim((string) ($_POST['program_level'] ?? ''));
            $notesParts[] = 'Father: ' . trim((string) ($_POST['father_name'] ?? ''));
            $notesParts[] = 'Mother: ' . trim((string) ($_POST['mother_name'] ?? ''));
            $notesParts[] = 'Sponsor: ' . trim((string) ($_POST['sponsor'] ?? ''));
            $notesParts[] = 'Sponsor detail: ' . trim((string) ($_POST['sponsor_other'] ?? ''));
            if ($filePath !== '') {
                $notesParts[] = 'Results report: ' . $filePath;
            }
            $notes = implode(' | ', array_filter($notesParts));

            $stmt = $pdo->prepare(
                'INSERT INTO admissions (applicant_name, gender, date_of_birth, parent_name, parent_phone, email, address, previous_school, preferred_program_id, intake_year, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $applicantName,
                $_POST['gender'] ?? null,
                $_POST['date_of_birth'] ?? null,
                $_POST['guardian_name'] ?? '',
                $_POST['guardian_phone'] ?? '',
                $_POST['email'] ?? '',
                '',
                '',
                $_POST['preferred_program_id'] ?? null,
                null,
                $notes,
            ]);

            $message = 'Your registration has been submitted successfully. We will contact you soon.';
            $messageType = 'success';
        } catch (PDOException $e) {
            $message = 'There was an error submitting your registration. Please try again.';
            $messageType = 'error';
        }
    } else {
        $message = 'Database connection error. Please try again later.';
        $messageType = 'error';
    }
}

renderSiteHeader('Admissions', $schoolName, $contacts, 'admissions', [
    'description' => 'Complete admission guide for Mubuga TSS - requirements, programs, and application process.',
    'image' => 'assets/images/IM8.jpg',
]);
renderInnerHero('ADMISSIONS', 'Your Path to Technical Excellence', 'Join Mubuga TSS and build your future with industry-relevant technical programs.', 'assets/images/IM8.jpg');
?>
<main>
    <!-- Admission Overview Section -->
    <section class="section admission-overview-section">
        <div class="container">
            <div class="section-intro">
                <p class="eyebrow">Why Choose Mubuga TSS</p>
                <h2>Technical Excellence</h2>
                <p>Industry training. Job-ready.</p>
            </div>
            
            <div class="admission-highlights-grid">
                <div class="admission-highlight-card">
                    <h3>Industry Skills</h3>
                    <p>Industry skills. Practical.</p>
                </div>
                
                <div class="admission-highlight-card">
                    <h3>Expert Faculty</h3>
                    <p>Expert faculty. Experience.</p>
                </div>
                
                <div class="admission-highlight-card">
                    <h3>Modern Facilities</h3>
                    <p>Modern labs. Tech.</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Available Programs Section -->
    <section class="section programs-section">
        <div class="container">
            <div class="section-intro">
                <p class="eyebrow">Our Programs</p>
                <h2>Technical Programs</h2>
                <p>Specialized programs with practical training. Industry certifications. Real project experience.</p>
            </div>
            
            <div class="programs-grid">
                <div class="program-card">
                    <h3>Software Development</h3>
                    <div class="program-content">
                        <div class="program-info">
                            <h4>Core Technologies</h4>
                            <p>Python, JavaScript, Java, C++</p>
                        </div>
                        <div class="program-info">
                            <h4>Applications</h4>
                            <p>Web development, databases, cloud computing, mobile apps</p>
                        </div>
                        <div class="program-details">
                            <span><strong>Duration:</strong> 2 Years</span>
                            <span><strong>Levels:</strong> 3, 4, 5</span>
                            <span><strong>Certifications:</strong> Available</span>
                        </div>
                    </div>
                </div>
                
                <div class="program-card">
                    <h3>Electrical Technology</h3>
                    <div class="program-content">
                        <div class="program-info">
                            <h4>Core Technologies</h4>
                            <p>Electrical systems, circuit design, power distribution</p>
                        </div>
                        <div class="program-info">
                            <h4>Applications</h4>
                            <p>Industrial automation, control systems, smart grid</p>
                        </div>
                        <div class="program-details">
                            <span><strong>Duration:</strong> 2 Years</span>
                            <span><strong>Levels:</strong> 3, 4, 5</span>
                            <span><strong>Certifications:</strong> Available</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Admission Requirements Section -->
    <section class="section admission-requirements-section">
        <div class="container">
            <div class="section-intro">
                <p class="eyebrow">Requirements</p>
                <h2>Admission Criteria</h2>
                <p>Meet our requirements. We select motivated students with academic potential.</p>
            </div>
            
            <div class="requirements-grid">
                <div class="requirement-card">
                    <h3>Academic Requirements</h3>
                    <ul>
                        <li>3+ credits in Math, Physics, Chemistry, or Technical subjects</li>
                        <li>Pass in Mathematics and English</li>
                        <li>Technical drawing or computer science advantage</li>
                        <li>Minimum age: 16 years</li>
                        <li>Good conduct certificate</li>
                        <li>Completed secondary education</li>
                    </ul>
                </div>
                
                <div class="requirement-card">
                    <h3>Required Documents</h3>
                    <ul>
                        <li>Birth certificate or national ID</li>
                        <li>School leaving certificate</li>
                        <li>Academic transcripts</li>
                        <li>Guardian identification</li>
                        <li>2 passport photos</li>
                        <li>Medical fitness certificate</li>
                        <li>Character reference</li>
                    </ul>
                </div>
                
                <div class="requirement-card">
                    <h3>Application Process</h3>
                    <ul>
                        <li>Complete online application</li>
                        <li>Submit all documents</li>
                        <li>Pay application fee</li>
                        <li>Attend entrance exam</li>
                        <li>Attend interview</li>
                        <li>Receive admission offer</li>
                        <li>Complete registration</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Timeline Section -->
    <section class="section admission-timeline-section">
        <div class="container">
            <div class="section-intro">
                <p class="eyebrow">Important Dates</p>
                <h2>Admission Timeline</h2>
                <p>Mark these dates. Our process ensures fair consideration.</p>
            </div>
            
            <div class="timeline-grid">
                <div class="timeline-item">
                    <div class="timeline-date">Jan 15 - Mar 30</div>
                    <div class="timeline-content">
                        <h4>Application Period</h4>
                        <p>Submit complete application with all documents. Early applications encouraged.</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-date">Apr 1 - Apr 15</div>
                    <div class="timeline-content">
                        <h4>Exams & Interviews</h4>
                        <p>Attend written exams and interviews. Bring original documents.</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-date">Apr 30</div>
                    <div class="timeline-content">
                        <h4>Results Announcement</h4>
                        <p>Successful candidates notified via email and SMS.</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-date">May 1 - May 15</div>
                    <div class="timeline-content">
                        <h4>Registration & Orientation</h4>
                        <p>Complete registration, pay fees, attend orientation.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Career Opportunities Section -->
    <section class="section career-opportunities-section">
        <div class="container">
            <div class="section-intro">
                <p class="eyebrow">Career Pathways</p>
                <h2>Job Opportunities</h2>
                <p>Graduates get excellent jobs with competitive salaries.</p>
            </div>
            
            <div class="careers-grid">
                <div class="career-card">
                    <h3>Software Development Jobs</h3>
                    <ul>
                        <li>Full-Stack Developer</li>
                        <li>Mobile App Developer</li>
                        <li>Web Developer</li>
                        <li>Database Administrator</li>
                        <li>Software QA Engineer</li>
                        <li>DevOps Engineer</li>
                        <li>Cloud Specialist</li>
                        <li>IT Project Manager</li>
                    </ul>
                    <p><strong>Industries:</strong> Tech, Finance, Healthcare</p>
                </div>
                
                <div class="career-card">
                    <h3>Electrical Technology Jobs</h3>
                    <ul>
                        <li>Electrical Engineer</li>
                        <li>Industrial Automation Tech</li>
                        <li>Power Systems Tech</li>
                        <li>Renewable Energy Specialist</li>
                        <li>Control Systems Engineer</li>
                        <li>Maintenance Supervisor</li>
                        <li>Electrical Project Manager</li>
                        <li>Smart Grid Tech</li>
                    </ul>
                    <p><strong>Industries:</strong> Manufacturing, Energy, Construction</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Student Life Section -->
    <section class="section student-life-section">
        <div class="container">
            <div class="section-intro">
                <p class="eyebrow">Campus Life</p>
                <h2>Student Experience</h2>
                <p>Modern facilities, sports programs, clubs, supportive community.</p>
            </div>
            
            <div class="student-life-grid">
                <div class="life-card">
                    <h3>Academic Support</h3>
                    <p>Tutoring, academic advising, study groups, library resources.</p>
                </div>
                
                <div class="life-card">
                    <h3>Sports & Recreation</h3>
                    <p>Football field, basketball courts, volleyball, athletics track.</p>
                </div>
                
                <div class="life-card">
                    <h3>Clubs & Organizations</h3>
                    <p>Coding club, engineering society, entrepreneurship club, debate team.</p>
                </div>
                
                <div class="life-card">
                    <h3>Housing & Dining</h3>
                    <p>Safe hostel accommodation with meal plans, 24/7 security.</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- FAQ Section -->
    <section class="section faq-section">
        <div class="container">
            <div class="section-intro">
                <p class="eyebrow">FAQ</p>
                <h2>Admission Questions</h2>
                <p>Find answers about admission, programs, requirements.</p>
            </div>
            
            <div class="faq-grid">
                <div class="faq-item">
                    <h4>What are tuition fees and payment options?</h4>
                    <p>Fees vary by program and level. We offer installment payments, scholarships.</p>
                </div>
                
                <div class="faq-item">
                    <h4>Are there internship opportunities?</h4>
                    <p>Yes, we partner with leading companies for industrial attachments.</p>
                </div>
                
                <div class="faq-item">
                    <h4>What certification will I receive?</h4>
                    <p>Graduates receive nationally recognized technical certificates, industry certifications.</p>
                </div>
                
                <div class="faq-item">
                    <h4>Is accommodation available?</h4>
                    <p>Yes, we provide secure hostel accommodation with separate facilities.</p>
                </div>
                
                <div class="faq-item">
                    <h4>What support services are available?</h4>
                    <p>We offer academic counseling, career guidance, health services, mentorship.</p>
                </div>
                
                <div class="faq-item">
                    <h4>Can I transfer credits from another institution?</h4>
                    <p>Yes, we accept transfer credits from accredited institutions.</p>
                </div>
            </div>
        </div>
    </section>
    <!-- Application Form Section -->
    <section class="section admission-form-section" id="application">
        <div class="container">
            <div class="section-intro">
                <p class="eyebrow">Apply Now</p>
                <h2>Start Your Application</h2>
                <p>Ready to join Mubuga TSS? Complete the application form below.</p>
                <div class="cta-urgent">
                    <p><strong>Application Deadline: March 30</strong></p>
                    <a href="/MUBUGA-TSS/apply.php" class="btn btn-primary btn-large">Apply Today</a>
                </div>
            </div>
            
            <div class="admission-layout">
            <aside class="admission-side-panel">
                <p class="eyebrow">Admission Guide</p>
                <h2>Join Mubuga TSS</h2>
                <p>Follow these simple steps to complete your admission process.</p>
                <div class="admission-side-points">
                    <article class="admission-side-item">
                        <strong>Select Program</strong>
                        <span>Choose your preferred technical program.</span>
                    </article>
                    <article class="admission-side-item">
                        <strong>Prepare Documents</strong>
                        <span>Gather academic records and guardian information.</span>
                    </article>
                    <article class="admission-side-item">
                        <strong>Submit Application</strong>
                        <span>Complete the form and await confirmation.</span>
                    </article>
                </div>
            </aside>
            <div class="admission-card">
                <div class="form-heading">
                    <h1>Admission Requirements</h1>
                    <p>Ensure you have the following information ready before applying.</p>
                </div>

                <div class="admission-check-grid">
                    <article class="admission-check-card">
                        <strong>Program Selection</strong>
                        <span>Choose from Software Development or Electrical Technology programs.</span>
                    </article>
                    <article class="admission-check-card">
                        <strong>Guardian Information</strong>
                        <span>Parent or guardian names and contact details required.</span>
                    </article>
                    <article class="admission-check-card">
                        <strong>Academic Records</strong>
                        <span>Previous results report in JPG or PNG format.</span>
                    </article>
                </div>

                <div class="admission-staff-strip">
                    <div class="admission-staff-strip-top">
                        <p class="eyebrow">Support Team</p>
                        <h2>Contact Our Staff</h2>
                    </div>
                    <div class="admission-staff-grid">
                        <?php foreach (array_slice($leadership, 0, 3) as $member): ?>
                            <article class="admission-staff-card">
                                <img src="/MUBUGA-TSS/<?php echo htmlspecialchars((string) $member['photo']); ?>" alt="<?php echo htmlspecialchars((string) $member['name']); ?>" class="admission-staff-photo">
                                <div>
                                    <strong><?php echo htmlspecialchars((string) $member['name']); ?></strong>
                                    <span><?php echo htmlspecialchars((string) $member['role']); ?></span>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php if ($message): ?>
                    <div class="message <?php echo $messageType; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="admission-form" id="admissionForm" enctype="multipart/form-data">
                    <div id="registration" class="form-heading form-heading-left">
                        <h2>Student Application Form</h2>
                        <p>Please complete all required fields accurately.</p>
                    </div>
                    <div class="form-grid form-grid--two">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" placeholder="Enter your first name" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" placeholder="Enter your last name" required>
                        </div>
                        <div class="form-group">
                            <label for="preferred_program_id">Program Selection</label>
                            <select id="preferred_program_id" name="preferred_program_id" required>
                                <option value="">Select a program</option>
                                <?php
                                $pdo = getDatabaseConnection();
                                $programOptions = [];
                                if ($pdo instanceof PDO) {
                                    $programOptions = $pdo->query('SELECT id, title FROM programs WHERE status = "active" ORDER BY title ASC')->fetchAll();
                                } else {
                                    $programOptions = $programs;
                                }
                                foreach ($programOptions as $program) {
                                    $optionValue = $pdo instanceof PDO ? htmlspecialchars((string) $program['id']) : htmlspecialchars($program['title']);
                                    $optionLabel = htmlspecialchars((string) ($program['title'] ?? $program['title']));
                                    echo '<option value="' . $optionValue . '">' . $optionLabel . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="program_level">Academic Level</label>
                            <select id="program_level" name="program_level">
                                <option value="">Select level</option>
                                <option value="Level 3">Level 3</option>
                                <option value="Level 4">Level 4</option>
                                <option value="Level 5">Level 5</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="guardian_name">Guardian Full Name</label>
                            <input type="text" id="guardian_name" name="guardian_name" placeholder="Enter guardian's full name">
                        </div>
                        <div class="form-group">
                            <label for="guardian_phone">Guardian Phone Number</label>
                            <input type="tel" id="guardian_phone" name="guardian_phone" placeholder="Enter guardian's phone number">
                        </div>
                        <div class="form-group">
                            <label for="father_name">Father's Full Name</label>
                            <input type="text" id="father_name" name="father_name" placeholder="Enter father's full name">
                        </div>
                        <div class="form-group">
                            <label for="mother_name">Mother's Full Name</label>
                            <input type="text" id="mother_name" name="mother_name" placeholder="Enter mother's full name">
                        </div>
                    </div>

                    <div class="form-grid form-grid--three">
                        <div class="form-group">
                            <label for="sponsor">Sponsor Information</label>
                            <select id="sponsor" name="sponsor">
                                <option value="">Select sponsor type</option>
                                <option value="Parent">Parent</option>
                                <option value="Guardian">Guardian</option>
                                <option value="Self">Self</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="sponsor_other">Other Sponsor Details</label>
                            <input type="text" id="sponsor_other" name="sponsor_other" placeholder="Specify sponsor if not listed">
                        </div>
                        <div class="form-group file-group">
                            <label for="results_report">Previous Academic Results</label>
                            <input type="file" id="results_report" name="results_report" accept="image/jpeg,image/png">
                            <span class="file-note">Accepted formats: JPG, PNG (Max 5MB)</span>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Submit Application</button>
                    </div>
                </form>
            </div>
            </div>
        </div>
    </section>
</main>
<?php renderSiteFooter($schoolName); ?>
