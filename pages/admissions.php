<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/site_data.php';
require_once __DIR__ . '/../includes/site_layout.php';
require_once __DIR__ . '/../includes/admin_upload.php';

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
    'description' => 'Find the requirements and admission path for joining Mubuga TSS.',
    'image' => 'assets/images/IM8.jpg',
]);
renderInnerHero('ADMISSION', 'Start your journey at Mubuga TSS', 'Find the basic requirements and simple admission pathway for joining one of our technical trades.', 'assets/images/IM8.jpg');
?>
<main>
    <section class="section admission-form-section" id="requirements">
        <div class="container">
            <div class="admission-layout">
            <aside class="admission-side-panel">
                <p class="eyebrow">Admission Guide</p>
                <h2>Join Mubuga TSS in a few clear steps.</h2>
                <p>Check the essentials, choose your program, and complete the form.</p>
                <div class="admission-side-points">
                    <article class="admission-side-item">
                        <strong>Choose a trade</strong>
                        <span>Pick the program that matches your interest.</span>
                    </article>
                    <article class="admission-side-item">
                        <strong>Prepare documents</strong>
                        <span>Keep your learner and guardian details ready.</span>
                    </article>
                    <article class="admission-side-item">
                        <strong>Wait for response</strong>
                        <span>The school will guide you on the next step.</span>
                    </article>
                </div>
            </aside>
            <div class="admission-card">
                <div class="form-heading">
                    <h1>Admission Checklist</h1>
                    <p>Before registration, make sure you have these key details.</p>
                </div>

                <div class="admission-check-grid">
                    <article class="admission-check-card">
                        <strong>Program choice</strong>
                        <span>Choose Software Development or Electrical Technology.</span>
                    </article>
                    <article class="admission-check-card">
                        <strong>Guardian contact</strong>
                        <span>Prepare parent or guardian names and phone number.</span>
                    </article>
                    <article class="admission-check-card">
                        <strong>Previous results</strong>
                        <span>Upload a clear JPG or PNG image of your report.</span>
                    </article>
                </div>

                <div class="admission-staff-strip">
                    <div class="admission-staff-strip-top">
                        <p class="eyebrow">Admission Support</p>
                        <h2>Talk to our team</h2>
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
                        <h2>Student Registration Form</h2>
                        <p>Fill the form carefully and submit once.</p>
                    </div>
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
                                    $optionValue = $pdo instanceof PDO ? htmlspecialchars((string) $program['id']) : htmlspecialchars($program['title']);
                                    $optionLabel = htmlspecialchars((string) ($program['title'] ?? $program['title']));
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
                            <input type="text" id="guardian_name" name="guardian_name" placeholder="Your Guardian's Names">
                        </div>
                        <div class="form-group">
                            <label for="guardian_phone">Guardian's Phone No</label>
                            <input type="tel" id="guardian_phone" name="guardian_phone" placeholder="Guardian's Contact">
                        </div>
                        <div class="form-group">
                            <label for="father_name">Father's Names</label>
                            <input type="text" id="father_name" name="father_name" placeholder="Your Father's Names">
                        </div>
                        <div class="form-group">
                            <label for="mother_name">Mother's Names</label>
                            <input type="text" id="mother_name" name="mother_name" placeholder="Your Mother's Names">
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
                            <label for="results_report">Upload your previous results report</label>
                            <input type="file" id="results_report" name="results_report" accept="image/jpeg,image/png">
                            <span class="file-note">Only JPG, PNG</span>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">SUBMIT</button>
                    </div>
                </form>
            </div>
            </div>
        </div>
    </section>
</main>
<?php renderSiteFooter($schoolName); ?>
