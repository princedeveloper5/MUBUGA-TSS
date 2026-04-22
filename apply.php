<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/site_data.php';
require_once __DIR__ . '/includes/site_layout.php';
require_once __DIR__ . '/portal/header.php';
require_once __DIR__ . '/portal/footer.php';

$page = sitePageContent('apply', [
    'title' => 'Apply for Admission',
    'excerpt' => 'Join Mubuga TSS and start your journey in Software Development or Electrical Technology.',
    'content' => 'Application Form',
    'image' => 'assets/images/student in practical.jpeg',
]);

renderSiteHeader($page['title'], $schoolName, $contacts, 'apply', [
    'description' => $page['excerpt'],
    'image' => $page['image'],
]);
?>

<main>
    <section class="section apply-section">
        <div class="container">
            <div class="apply-content">
                <div class="apply-header">
                    <h1>Apply for Admission</h1>
                    <p>Fill out the form below to start your application process at Mubuga TSS.</p>
                </div>

                <?php
                // Display form feedback if present
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

                <div class="application-form">
                    <form action="/MUBUGA-TSS/handlers/site_forms.php" method="POST" class="registration-form">
                        <input type="hidden" name="form_action" value="student_registration">
                        <input type="hidden" name="redirect_to" value="/MUBUGA-TSS/apply.php">

                        <div class="form-section">
                            <h2>Personal Information</h2>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="first_name">First Name *</label>
                                    <input type="text" id="first_name" name="first_name" required>
                                </div>
                                <div class="form-group">
                                    <label for="last_name">Last Name *</label>
                                    <input type="text" id="last_name" name="last_name" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email Address *</label>
                                    <input type="email" id="email" name="email" required>
                                </div>
                                <div class="form-group">
                                    <label for="phone">Phone Number *</label>
                                    <input type="tel" id="phone" name="phone" required>
                                </div>
                                <div class="form-group">
                                    <label for="date_of_birth">Date of Birth *</label>
                                    <input type="date" id="date_of_birth" name="date_of_birth" required>
                                </div>
                                <div class="form-group">
                                    <label for="gender">Gender *</label>
                                    <select id="gender" name="gender" required>
                                        <option value="">Select Gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h2>Program Selection</h2>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="program">Program of Interest *</label>
                                    <select id="program" name="program" required>
                                        <option value="">Select Program</option>
                                        <option value="software_development">Software Development</option>
                                        <option value="electrical_technology">Electrical Technology</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="start_date">Preferred Start Date *</label>
                                    <select id="start_date" name="start_date" required>
                                        <option value="">Select Start Date</option>
                                        <option value="january_2024">January 2024</option>
                                        <option value="march_2024">March 2024</option>
                                        <option value="september_2024">September 2024</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h2>Educational Background</h2>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="last_school">Last School Attended *</label>
                                    <input type="text" id="last_school" name="last_school" required>
                                </div>
                                <div class="form-group">
                                    <label for="graduation_year">Graduation Year *</label>
                                    <input type="number" id="graduation_year" name="graduation_year" min="1950" max="2024" required>
                                </div>
                                <div class="form-group">
                                    <label for="qualification">Highest Qualification *</label>
                                    <select id="qualification" name="qualification" required>
                                        <option value="">Select Qualification</option>
                                        <option value="o_level">O-Level</option>
                                        <option value="a_level">A-Level</option>
                                        <option value="certificate">Certificate</option>
                                        <option value="diploma">Diploma</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h2>Additional Information</h2>
                            <div class="form-group full-width">
                                <label for="address">Current Address *</label>
                                <textarea id="address" name="address" rows="3" required></textarea>
                            </div>
                            <div class="form-group full-width">
                                <label for="motivation">Why do you want to join Mubuga TSS? *</label>
                                <textarea id="motivation" name="motivation" rows="4" required></textarea>
                            </div>
                            <div class="form-group full-width">
                                <label for="additional_info">Additional Information (Optional)</label>
                                <textarea id="additional_info" name="additional_info" rows="3"></textarea>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Submit Application</button>
                            <button type="reset" class="btn btn-secondary">Clear Form</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>

<?php renderSiteFooter($schoolName); ?>
