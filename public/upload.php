<?php
require_once __DIR__ . '/../src/bootstrap.php';
Auth::requireLogin();

$config = require __DIR__ . '/../config/config.php';
$maxBytes = $config['app']['upload_max_mb'] * 1024 * 1024;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file = $_FILES['resume'] ?? null;

    if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Please choose a file to upload.';
    } elseif ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Upload failed. Please try again.';
    } elseif ($file['size'] > $maxBytes) {
        $errors[] = 'File is too large. Maximum size is ' . $config['app']['upload_max_mb'] . 'MB.';
    } else {
        $originalName = $file['name'];
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($extension, ['pdf', 'docx'], true)) {
            $errors[] = 'Only PDF and DOCX files are supported.';
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedMimes = [
            'pdf' => ['application/pdf'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
        ];

        if (!$errors && !in_array($mime, $allowedMimes[$extension] ?? [], true)) {
            $errors[] = 'The uploaded file does not look like a valid ' . strtoupper($extension) . ' file.';
        }

        if (!$errors) {
            $storedName = bin2hex(random_bytes(16)) . '.' . $extension;
            $destination = __DIR__ . '/uploads/' . $storedName;

            if (!move_uploaded_file($file['tmp_name'], $destination)) {
                $errors[] = 'Could not save the uploaded file.';
            } else {
                try {
                    $text = ResumeParser::extractText($destination, $extension);

                    $db = Database::connection();
                    $stmt = $db->prepare(
                        'INSERT INTO resumes (user_id, original_filename, stored_filename, file_type, extracted_text)
                         VALUES (?, ?, ?, ?, ?)'
                    );
                    $stmt->execute([Auth::user()['id'], $originalName, $storedName, $extension, $text]);
                    $resumeId = (int) $db->lastInsertId();

                    $grok = new GrokService();
                    $analysis = $grok->analyzeResume($text);

                    $stmt = $db->prepare(
                        'INSERT INTO resume_analysis (
                            resume_id, personal_info, education, skills, technical_skills, soft_skills,
                            experience, projects, certifications, achievements, resume_summary,
                            resume_score, ats_score, strengths, weaknesses, missing_skills,
                            suggestions, recommended_roles, raw_ai_response
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
                    );
                    $stmt->execute([
                        $resumeId,
                        json_encode($analysis['personal_info']),
                        json_encode($analysis['education']),
                        json_encode($analysis['skills']),
                        json_encode($analysis['technical_skills']),
                        json_encode($analysis['soft_skills']),
                        json_encode($analysis['experience']),
                        json_encode($analysis['projects']),
                        json_encode($analysis['certifications']),
                        json_encode($analysis['achievements']),
                        $analysis['resume_summary'],
                        $analysis['resume_score'],
                        $analysis['ats_score'],
                        json_encode($analysis['strengths']),
                        json_encode($analysis['weaknesses']),
                        json_encode($analysis['missing_skills']),
                        json_encode($analysis['suggestions']),
                        json_encode($analysis['recommended_roles']),
                        $analysis['_raw_ai_response'],
                    ]);

                    header('Location: resume_result.php?id=' . $resumeId);
                    exit;
                } catch (ResumeParserException|GrokException $e) {
                    unlink($destination);
                    $errors[] = $e->getMessage();
                }
            }
        }
    }
}

$pageTitle = 'Upload Resume';
require __DIR__ . '/partials/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card p-4 mt-4">
            <h3 class="mb-3">Upload your resume</h3>
            <p class="text-muted">PDF or DOCX, up to <?= $config['app']['upload_max_mb'] ?>MB. We'll extract the text and
                send it to Grok for a full analysis: skills, scores, strengths/weaknesses, and suggestions.</p>
            <?php foreach ($errors as $error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
            <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <input type="file" name="resume" class="form-control" accept=".pdf,.docx" required>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-cloud-arrow-up"></i> Analyze Resume
                </button>
            </form>
        </div>
    </div>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
