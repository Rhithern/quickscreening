<?php
require_once '../includes/auth_candidate.php'; // Candidate must be logged in
$pageTitle = 'Record Your Answer';
include '../includes/header.php';
?>

<h2><?= htmlspecialchars(SITE_NAME) ?> - Record Your Answer</h2>

<p>Use the buttons below to record your video answer. When finished, click "Stop & Upload" to save.</p>

<video id="preview" width="480" height="360" autoplay muted></video>
<br>
<button id="startBtn" class="btn btn-success">Start Recording</button>
<button id="stopBtn" class="btn btn-danger" disabled>Stop & Upload</button>

<script>
let mediaRecorder;
let recordedChunks = [];

navigator.mediaDevices.getUserMedia({ video: true, audio: true })
  .then(stream => {
    const video = document.getElementById('preview');
    video.srcObject = stream;

    mediaRecorder = new MediaRecorder(stream);

    mediaRecorder.ondataavailable = function(event) {
      if (event.data.size > 0) recordedChunks.push(event.data);
    };

    mediaRecorder.onstop = async function() {
      const blob = new Blob(recordedChunks, { type: 'video/webm' });
      const formData = new FormData();
      formData.append('video', blob);

      try {
        const response = await fetch('upload_video.php', {
          method: 'POST',
          body: formData
        });
        if (response.ok) {
          alert('Your video was uploaded successfully!');
          recordedChunks = []; // Clear after upload
        } else {
          alert('Upload failed.');
        }
      } catch (err) {
        alert('Error uploading video.');
      }
    };

    document.getElementById('startBtn').onclick = () => {
      recordedChunks = [];
      mediaRecorder.start();
      document.getElementById('startBtn').disabled = true;
      document.getElementById('stopBtn').disabled = false;
    };

    document.getElementById('stopBtn').onclick = () => {
      mediaRecorder.stop();
      document.getElementById('stopBtn').disabled = true;
      document.getElementById('startBtn').disabled = false;
    };
  })
  .catch(err => {
    alert('Could not access camera/mic: ' + err.message);
  });
</script>

<?php include '../includes/footer.php'; ?>
