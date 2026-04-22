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

$page = sitePageContent('registration', [
    'title' => 'Student Registration',
    'excerpt' => 'Complete the registration form for admission into Mubuga TSS.',
    'content' => 'Registration form',
    'image' => 'assets/images/school view 4.jpg',
]);

renderSiteHeader($page['title'], $schoolName, $contacts, 'admissions', [
    'description' => $page['excerpt'],
    'image' => $page['image'],
]);
renderInnerHero('REGISTRATION', $page['title'], $page['excerpt'], $page['image']);
?>
<main>
    <section class="section registration-page-section">
        <div class="container">
            <div class="registration-shell">
                <div class="registration-intro">
                    <p class="eyebrow">Apply To Mubuga TSS</p>
                    <h2>Student Registration Form</h2>
                    <p>Fill in the learner and guardian details carefully, choose the right program, and upload the previous results report if available.</p>
                </div>

                <div class="admission-card registration-card">
                    <?php if ($message): ?>
                        <div class="message <?php echo $messageType; ?>">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" class="admission-form" enctype="multipart/form-data">
                        <div class="form-grid form-grid--two">
                            <div class="form-group">
                                <label for="first_name">First Name</label>
                                <input type="text" id="first_name" name="first_name" placeholder="Your First Name" required>
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name</label>
                                <input type="text" id="last_name" name="last_name" placeholder="Your Last Name" required>
                            </div>
                            <div class="form-group">
                                <label for="preferred_program_id">Select The Program</label>
                                <select id="preferred_program_id" name="preferred_program_id" required>
                                    <option value="">Choose a program</option>
                                    <?php
                                    $pdo = getDatabaseConnection();
                                    $programOptions = [];
                                    if ($pdo instanceof PDO) {
                                        $programOptions = $pdo->query('SELECT id, title FROM programs WHERE status = "active" ORDER BY title ASC')->fetchAll();
                                    } else {
                                        $programOptions = $programs;
                                    }
                                    foreach ($programOptions as $program) {
                                        $optionValue = $pdo instanceof PDO ? htmlspecialchars((string) $program['id']) : htmlspecialchars((string) $program['title']);
                                        $optionLabel = htmlspecialchars((string) ($program['title'] ?? 'Program'));
                                        echo '<option value="' . $optionValue . '">' . $optionLabel . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="program_level">Please Select Level</label>
                                <select id="program_level" name="program_level">
                                    <option value="">Select Level</option>
                                    <option value="Level 3">Level 3</option>
                                    <option value="Level 4">Level 4</option>
                                    <option value="Level 5">Level 5</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="guardian_name">Guardian's Names</label>
                                <input type="text" id="guardian_name" name="guardian_name" placeholder="Guardian's Names">
                            </div>
                            <div class="form-group">
                                <label for="guardian_phone">Guardian's Phone No</label>
                                <input type="tel" id="guardian_phone" name="guardian_phone" placeholder="Guardian's Contact">
                            </div>
                            <div class="form-group">
                                <label for="father_name">Father's Names</label>
                                <input type="text" id="father_name" name="father_name" placeholder="Father's Names">
                            </div>
                            <div class="form-group">
                                <label for="mother_name">Mother's Names</label>
                                <input type="text" id="mother_name" name="mother_name" placeholder="Mother's Names">
                            </div>
                        </div>

                        <div class="form-grid form-grid--three">
                            <div class="form-group">
                                <label for="sponsor">Please select your sponsor</label>
                                <select id="sponsor" name="sponsor">
                                    <option value="">Select sponsor</option>
                                    <option value="Parent">Parent</option>
                                    <option value="Guardian">Guardian</option>
                                    <option value="Self">Self</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="sponsor_other">If not listed, please specify</label>
                                <input type="text" id="sponsor_other" name="sponsor_other" placeholder="Who will pay for you?">
                            </div>
                            <div class="form-group file-group">
                                <label for="results_report">Upload previous results report</label>
                                <input type="file" id="results_report" name="results_report" accept="image/jpeg,image/png">
                                <span class="file-note">Only JPG, PNG</span>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Submit Registration</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>
<?php renderSiteFooter($schoolName); ?>
