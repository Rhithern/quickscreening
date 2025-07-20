<?php
// upload_video.php
session_start();
if (!isset($_SESSION['candidate_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Upload Answer Video</title>
    <style>
        .preview { margin-top: 15px; }
        video { width: 320px; height: 240px; border: 1px solid #ccc; }
    </style>
</head>
<body>
    <h2>Upload Your Answer</h2>

    <form id="uploadForm" action="process_upload.php" method="POST" enctype="multipart/form-data">
        <input type="file" name="answer_video" id="answer_video" accept="video/*,audio/*" required><br><br>

        <input type="hidden" name="question_id" value="<?= $_GET['question_id'] ?? 0 ?>">
        <input type="hidden" name="interview_id" value="<?= $_GET['interview_id'] ?? 0 ?>">

        <div class="preview">
            <video id="preview" controls></video>
            <audio id="audioPreview" controls style="display:none;"></audio>
        </div>

        <br>
        <button type="submit">Upload</button>
        <button type="button" id="reRecordBtn" style="display:none;">Choose Different File</button>
    </form>

    <script>
        const fileInput = document.getElementById('answer_video');
        const videoPreview = document.getElementById('preview');
        const audioPreview = document.getElementById('audioPreview');
        const reRecordBtn = document.getElementById('reRecordBtn');

        fileInput.addEventListener('change', function () {
            const file = this.files[0];

            if (!file) return;

            const fileType = file.type;
            if (!fileType.startsWith("video/") && !fileType.startsWith("audio/")) {
                alert("Only video/audio files are allowed.");
                this.value = '';
                return;
            }

            const url = URL.createObjectURL(file);
            if (fileType.startsWith("video/")) {
                videoPreview.src = url;
                videoPreview.style.display = 'block';
                audioPreview.style.display = 'none';
            } else {
                audioPreview.src = url;
                audioPreview.style.display = 'block';
                videoPreview.style.display = 'none';
            }

            reRecordBtn.style.display = 'inline-block';
        });

        reRecordBtn.addEventListener('click', () => {
            fileInput.value = '';
            videoPreview.src = '';
            audioPreview.src = '';
            videoPreview.style.display = 'none';
            audioPreview.style.display = 'none';
            reRecordBtn.style.display = 'none';
        });
    </script>
</body>
</html>
