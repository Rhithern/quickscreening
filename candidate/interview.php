<?php
include '../includes/auth_candidate.php';
require_once '../includes/auth.php';  // candidate authentication
require_once '../includes/db.php';

$candidateId = $_SESSION['candidate_id'] ?? null;
$interviewId = $_GET['interview_id'] ?? null;

if (!$candidateId || !$interviewId) {
    header('Location: dashboard.php');
    exit;
}

// Fetch questions for this interview's position
$sql = "SELECT q.* FROM questions q
        JOIN interviews i ON i.position_id = q.position_id
        WHERE i.id = ? ORDER BY q.id ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$interviewId]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch existing answers by this candidate for this interview (if any)
$answers = [];
$answerSql = "SELECT * FROM answers WHERE interview_id = ?";
$answerStmt = $pdo->prepare($answerSql);
$answerStmt->execute([$interviewId]);
foreach ($answerStmt->fetchAll(PDO::FETCH_ASSOC) as $ans) {
    $answers[$ans['question_id']] = $ans;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($questions as $q) {
        $answerText = trim($_POST['answer_text'][$q['id']] ?? '');
        $answerVideo = $_POST['answer_video'][$q['id']] ?? null;

        // Check if answer exists
        $checkStmt = $pdo->prepare("SELECT id FROM answers WHERE interview_id = ? AND question_id = ?");
        $checkStmt->execute([$interviewId, $q['id']]);

        if ($checkStmt->rowCount()) {
            $answerId = $checkStmt->fetchColumn();
            $updateSql = "UPDATE answers SET answer_text = ?, answer_video = ? WHERE id = ?";
            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->execute([$answerText, $answerVideo, $answerId]);
        } else {
            $insertSql = "INSERT INTO answers (interview_id, question_id, answer_text, answer_video) VALUES (?, ?, ?, ?)";
            $insertStmt = $pdo->prepare($insertSql);
            $insertStmt->execute([$interviewId, $q['id'], $answerText, $answerVideo]);
        }
    }

    // Mark interview as completed
    $completeStmt = $pdo->prepare("UPDATE interviews SET completed = 1, completed_at = NOW() WHERE id = ?");
    $completeStmt->execute([$interviewId]);

    // TODO: Optionally send notification email here

    header('Location: dashboard.php?msg=Interview+submitted+successfully');
    exit;
}

$pageTitle = 'Interview - Record Your Answers';
include '../includes/header.php';
?>

<h2>Interview Questions</h2>
<form method="POST" id="interviewForm">
    <?php foreach ($questions as $index => $q): ?>
        <div class="mb-4">
            <label class="form-label"><strong>Question <?= $index + 1 ?>:</strong> <?= htmlspecialchars($q['question_text']) ?></label>

            <textarea name="answer_text[<?= $q['id'] ?>]" class="form-control mb-2" rows="4" placeholder="Type your answer here"><?= htmlspecialchars($answers[$q['id']]['answer_text'] ?? '') ?></textarea>

            <!-- Hidden input to hold uploaded video filename -->
            <input type="hidden" name="answer_video[<?= $q['id'] ?>]" id="video_file_<?= $q['id'] ?>" value="<?= htmlspecialchars($answers[$q['id']]['answer_video'] ?? '') ?>">

            <!-- Show previously uploaded video if any -->
            <?php if (!empty($answers[$q['id']]['answer_video'])): ?>
                <video width="320" height="240" controls class="mb-2" src="/uploads/answers/<?= htmlspecialchars($answers[$q['id']]['answer_video']) ?>"></video><br>
            <?php endif; ?>

            <!-- Video recording UI -->
            <video id="preview_<?= $q['id'] ?>" width="320" height="240" controls style="display:none;"></video><br>

            <button type="button" class="btn btn-sm btn-outline-primary" onclick="startRecording(<?= $q['id'] ?>)">Record Answer</button>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="stopRecording(<?= $q['id'] ?>)" disabled id="stop_btn_<?= $q['id'] ?>">Stop</button>
            <div id="status_<?= $q['id'] ?>" class="mt-1"></div>
        </div>
    <?php endforeach; ?>

    <button type="submit" class="btn btn-success">Submit Interview</button>
</form>

<script>
const mediaRecorders = {};
const recordedChunks = {};

async function startRecording(questionId) {
    const statusEl = document.getElementById(`status_${questionId}`);
    statusEl.textContent = 'Requesting camera/mic access...';

    try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
        const videoEl = document.getElementById(`preview_${questionId}`);
        videoEl.style.display = 'block';
        videoEl.srcObject = stream;
        videoEl.muted = true;
        videoEl.play();

        recordedChunks[questionId] = [];
        const mediaRecorder = new MediaRecorder(stream);
        mediaRecorders[questionId] = mediaRecorder;

        mediaRecorder.ondataavailable = e => {
            if (e.data.size > 0) recordedChunks[questionId].push(e.data);
        };

        mediaRecorder.onstop = async () => {
            statusEl.textContent = 'Uploading video...';

            const blob = new Blob(recordedChunks[questionId], { type: 'video/webm' });
            const formData = new FormData();
            formData.append('video', blob);
            formData.append('question_id', questionId);
            formData.append('interview_id', <?= json_encode($interviewId) ?>);

            try {
                const response = await fetch('/candidate/upload_answer_video.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    statusEl.textContent = 'Video uploaded successfully.';
                    document.getElementById(`video_file_${questionId}`).value = result.filename;
                } else {
                    statusEl.textContent = 'Upload failed: ' + result.error;
                }
            } catch (error) {
                statusEl.textContent = 'Upload error: ' + error.message;
            }
        };

        mediaRecorder.start();

        statusEl.textContent = 'Recording...';
        document.getElementById(`stop_btn_${questionId}`).disabled = false;
    } catch (err) {
        statusEl.textContent = 'Error accessing camera/mic: ' + err.message;
    }
}

function stopRecording(questionId) {
    const mediaRecorder = mediaRecorders[questionId];
    if (mediaRecorder && mediaRecorder.state !== 'inactive') {
        mediaRecorder.stop();

        // Stop all tracks to release camera/mic
        const videoEl = document.getElementById(`preview_${questionId}`);
        const stream = videoEl.srcObject;
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            videoEl.srcObject = null;
            videoEl.style.display = 'none';
        }
        document.getElementById(`stop_btn_${questionId}`).disabled = true;
        document.getElementById(`status_${questionId}`).textContent = 'Processing upload...';
    }
}
</script>

<?php include '../includes/footer.php'; ?>
