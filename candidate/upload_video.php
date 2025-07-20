<?php
session_start();
if (!isset($_SESSION['candidate_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Upload Answer</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <style>
    #preview {
      margin-top: 10px;
      max-width: 100%;
    }
    .progress {
      margin-top: 10px;
    }
  </style>
</head>
<body class="bg-light">
  <div class="container mt-5">
    <h2 class="mb-4">Upload Your Answer</h2>

    <form id="uploadForm">
      <div class="form-group">
        <label for="video">Select Video/Audio (.mp4, .webm, .ogg, .mp3):</label>
        <input type="file" class="form-control-file" id="video" name="video" accept="video/*,audio/*" required>
      </div>

      <div id="previewContainer"></div>

      <div class="progress">
        <div class="progress-bar progress-bar-striped" role="progressbar" style="width: 0%" id="progressBar">0%</div>
      </div>

      <button type="submit" class="btn btn-primary mt-3">Upload</button>
      <button type="button" class="btn btn-warning mt-3 ml-2" id="reRecordBtn" style="display:none;">Re-record/Overwrite</button>
    </form>
  </div>

  <!-- Success Modal -->
  <div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content border-success">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title" id="successModalLabel">Upload Successful</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          Your answer has been uploaded successfully.
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-success" data-dismiss="modal">OK</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const uploadForm = document.getElementById('uploadForm');
    const videoInput = document.getElementById('video');
    const previewContainer = document.getElementById('previewContainer');
    const progressBar = document.getElementById('progressBar');
    const reRecordBtn = document.getElementById('reRecordBtn');

    videoInput.addEventListener('change', function () {
      const file = this.files[0];
      if (file) {
        const type = file.type.startsWith('video') ? 'video' : 'audio';
        const preview = document.createElement(type);
        preview.src = URL.createObjectURL(file);
        preview.controls = true;
        preview.id = 'preview';
        previewContainer.innerHTML = '';
        previewContainer.appendChild(preview);
        reRecordBtn.style.display = 'inline-block';
      }
    });

    reRecordBtn.addEventListener('click', () => {
      videoInput.value = '';
      previewContainer.innerHTML = '';
      progressBar.style.width = '0%';
      progressBar.textContent = '0%';
      reRecordBtn.style.display = 'none';
    });

    uploadForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const file = videoInput.files[0];
      if (!file) {
        alert("Please select a file before uploading.");
        return;
      }

      const formData = new FormData();
      formData.append('video', file);

      const xhr = new XMLHttpRequest();
      xhr.open('POST', 'upload_answer_video.php', true);

      xhr.upload.onprogress = function (e) {
        if (e.lengthComputable) {
          const percent = Math.round((e.loaded / e.total) * 100);
          progressBar.style.width = percent + '%';
          progressBar.textContent = percent + '%';
        }
      };

      xhr.onload = function () {
        if (xhr.status === 200) {
          progressBar.style.width = '100%';
          progressBar.textContent = '100%';
          $('#successModal').modal('show');
        } else {
          alert('Upload failed: ' + xhr.responseText);
        }
      };

      xhr.onerror = function () {
        alert('An error occurred during the upload.');
      };

      xhr.send(formData);
    });
  </script>
</body>
</html>
