<?php
session_start();
require_once '../includes/auth_candidate.php';
require_once '../includes/db.php';

$candidateId = $_SESSION['candidate_id'] ?? null;
if (!$candidateId) {
    header('Location: /login.php');
    exit;
}

// Get interview ID from GET param and validate
$interviewId = $_GET['id'] ?? null;
if (!$interviewId || !is_numeric($interviewId)) {
    die("Invalid interview ID.");
}

// Fetch interview and check ownership
$stmt = $pdo->prepare("SELECT * FROM interviews WHERE id = ? AND candidate_id = ?");
$stmt->execute([$interviewId, $candidateId]);
$interview = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$interview) {
    die("Interview not found or unauthorized.");
}

// Fetch interview questions
$stmt = $pdo->prepare("
    SELECT iq.id AS interview_question_id, q.id AS question_id, q.question_text
    FROM interview_questions iq
    JOIN questions q ON iq.question_id = q.id
    WHERE iq.interview_id = ?
    ORDER BY iq.id ASC
");
$stmt->execute([$interviewId]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (!$questions) {
    die("No questions found for this interview.");
}

// Fetch existing answers for this interview & candidate
$stmt = $pdo->prepare("SELECT question_id, video_path FROM answers WHERE interview_id = ? AND candidate_id = ?");
$stmt->execute([$interviewId, $candidateId]);
$existingAnswersRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);
$existingAnswers = [];
foreach ($existingAnswersRaw as $ans) {
    $existingAnswers[$ans['question_id']] = $ans['video_path'];
}

// Optional: Interview time limit (in minutes)
$timeLimitMinutes = intval($interview['time_limit_minutes'] ?? 0);
$interviewStartTime = strtotime($interview['started_at'] ?? null);
$timeLeftSeconds = 0;
if ($timeLimitMinutes > 0 && $interviewStartTime) {
    $endTime = $interviewStartTime + ($timeLimitMinutes * 60);
    $now = time();
    $timeLeftSeconds = max(0, $endTime - $now);
}

// Page title
$pageTitle = "Interview: " . htmlspecialchars($interview['position_title'] ?? '');

// Include header
include '../includes/header.php';
?>

<h2>Interview for: <?= htmlspecialchars($interview['position_title'] ?? 'Position') ?></h2>

<?php if ($timeLimitMinutes > 0): ?>
  <div id="timer" class="alert alert-info mb-4">
    Time Remaining: <span id="time-left"><?= gmdate("i:s", $timeLeftSeconds) ?></span>
  </div>
<?php endif; ?>

<div id="interview-container">

  <div id="question-nav" class="mb-3">
    <?php foreach ($questions as $idx => $q): 
        $answeredClass = isset($existingAnswers[$q['question_id']]) ? 'btn-success' : 'btn-outline-secondary';
    ?>
      <button class="btn <?= $answeredClass ?> question-nav-btn" data-index="<?= $idx ?>">
        Q<?= $idx + 1 ?>
      </button>
    <?php endforeach; ?>
  </div>

  <?php foreach ($questions as $idx => $q): 
    $videoPath = $existingAnswers[$q['question_id']] ?? null;
  ?>
    <div class="question-block" style="display: <?= $idx === 0 ? 'block' : 'none' ?>;" data-index="<?= $idx ?>">
      <h5>Question <?= $idx + 1 ?> of <?= count($questions) ?></h5>
      <p><?= nl2br(htmlspecialchars($q['question_text'])) ?></p>

      <div>
        <?php if ($videoPath): ?>
          <p><strong>Your Answer:</strong></p>
          <video width="320" height="240" controls>
            <source src="/uploads/answers/<?= htmlspecialchars($videoPath) ?>" type="video/mp4">
            Your browser does not support the video tag.
          </video>
        <?php else: ?>
          <p><em>No answer recorded yet.</em></p>
        <?php endif; ?>
      </div>

      <form class="upload-answer-form" data-question-id="<?= $q['question_id'] ?>" data-interview-id="<?= $interviewId ?>">
        <div class="mb-3">
          <label for="answer-file-<?= $idx ?>" class="form-label">Upload or Record Your Answer (mp4, webm, ogg, mp3):</label>
          <input type="file" class="form-control answer-file" id="answer-file-<?= $idx ?>" accept="video/mp4,video/webm,audio/ogg,audio/mp3" required>
        </div>
        <div class="progress mb-3" style="height: 20px; display:none;">
          <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
        </div>
        <button type="submit" class="btn btn-primary">Upload / Overwrite Answer</button>
      </form>

      <div class="upload-status mt-2"></div>

      <div class="mt-3">
        <button class="btn btn-secondary prev-question" <?= $idx === 0 ? 'disabled' : '' ?>>Previous</button>
        <button class="btn btn-secondary next-question" <?= $idx === count($questions) - 1 ? 'disabled' : '' ?>>Next</button>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const questionBlocks = document.querySelectorAll('.question-block');
  const navButtons = document.querySelectorAll('.question-nav-btn');

  let currentIndex = 0;

  function showQuestion(index) {
    currentIndex = index;
    questionBlocks.forEach((block, i) => {
      block.style.display = i === index ? 'block' : 'none';
    });
    navButtons.forEach((btn, i) => {
      btn.classList.toggle('btn-primary', i === index);
      btn.classList.toggle('btn-outline-secondary', i !== index);
    });
  }

  navButtons.forEach((btn, i) => {
    btn.addEventListener('click', () => {
      showQuestion(i);
    });
  });

  document.querySelectorAll('.next-question').forEach(btn => {
    btn.addEventListener('click', () => {
      if (currentIndex < questionBlocks.length - 1) {
        showQuestion(currentIndex + 1);
      }
    });
  });

  document.querySelectorAll('.prev-question').forEach(btn => {
    btn.addEventListener('click', () => {
      if (currentIndex > 0) {
        showQuestion(currentIndex - 1);
      }
    });
  });

  // Handle upload form submissions with AJAX
  document.querySelectorAll('.upload-answer-form').forEach(form => {
    form.addEventListener('submit', e => {
      e.preventDefault();

      const fileInput = form.querySelector('.answer-file');
      const file = fileInput.files[0];
      if (!file) {
        alert('Please select a file to upload.');
        return;
      }

      const allowedTypes = ['video/mp4', 'video/webm', 'audio/ogg', 'audio/mp3'];
      if (!allowedTypes.includes(file.type)) {
        alert('Unsupported file type. Allowed: mp4, webm, ogg, mp3');
        return;
      }

      const progressBarContainer = form.querySelector('.progress');
      const progressBar = progressBarContainer.querySelector('.progress-bar');
      progressBarContainer.style.display = 'block';
      progressBar.style.width = '0%';

      const formData = new FormData();
      formData.append('answer_video', file);
      formData.append('question_id', form.dataset.questionId);
      formData.append('interview_id', form.dataset.interviewId);

      fetch('/candidate/upload_answer_video.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
      })
      .then(response => response.json())
      .then(data => {
        progressBar.style.width = '100%';

        if (data.success) {
          // Show success and update video preview
          const statusDiv = form.querySelector('.upload-status');
          statusDiv.innerHTML = `<div class="alert alert-success">${data.message}</div>`;

          // Update video preview
          const questionBlock = form.closest('.question-block');
          let videoEl = questionBlock.querySelector('video');
          if (!videoEl) {
            videoEl = document.createElement('video');
            videoEl.controls = true;
            videoEl.width = 320;
            videoEl.height = 240;
            questionBlock.insertBefore(videoEl, statusDiv);
          }
          videoEl.src = '/uploads/answers/' + data.filename + '?t=' + new Date().getTime();

          // Update nav button style to show answered
          const idx = parseInt(questionBlock.dataset.index);
          navButtons[idx].classList.remove('btn-outline-secondary');
          navButtons[idx].classList.add('btn-success');
        } else {
          alert(data.error || 'Upload failed');
        }
      })
      .catch(() => alert('Upload failed. Please try again.'))
      .finally(() => {
        progressBarContainer.style.display = 'none';
      });
    });
  });

  // Countdown timer (if applicable)
  <?php if ($timeLimitMinutes > 0 && $timeLeftSeconds > 0): ?>
  let timeLeft = <?= $timeLeftSeconds ?>;
  const timerEl = document.getElementById('time-left');
  const timerInterval = setInterval(() => {
    if (timeLeft <= 0) {
      clearInterval(timerInterval);
      alert('Interview time is up!');
      // Optionally redirect or disable forms here
    } else {
      timeLeft--;
      const minutes = Math.floor(timeLeft / 60).toString().padStart(2, '0');
      const seconds = (timeLeft % 60).toString().padStart(2, '0');
      timerEl.textContent = minutes + ':' + seconds;
    }
  }, 1000);
  <?php endif; ?>
});
</script>

<style>
.question-block {
  border: 1px solid #ccc;
  padding: 15px;
  margin-bottom: 1rem;
  border-radius: 5px;
}
#question-nav button {
  margin-right: 5px;
  margin-bottom: 10px;
}
</style>

<?php include '../includes/footer.php'; ?>
