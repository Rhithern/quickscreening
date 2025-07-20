<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Answer</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        #progressWrapper {
            width: 100%;
            background-color: #eee;
            margin-top: 15px;
        }
        #progressBar {
            width: 0%;
            height: 25px;
            background-color: green;
            text-align: center;
            color: white;
        }
        #preview {
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <h2>Upload Your Answer</h2>
    <form id="uploadForm" enctype="multipart/form-data">
        <input type="file" name="answer" id="answer" accept="video/mp4,video/webm,audio/ogg,audio/mpeg" required><br><br>
        <input type="hidden" name="question_id" value="<?= htmlspecialchars($_GET['question_id'] ?? '') ?>">
        <input type="hidden" name="interview_id" value="<?= htmlspecialchars($_GET['interview_id'] ?? '') ?>">
        <button type="submit">Upload</button>
    </form>

    <div id="progressWrapper">
        <div id="progressBar">0%</div>
    </div>

    <div id="preview"></div>

    <script>
        $('#uploadForm').submit(function (e) {
            e.preventDefault();

            let formData = new FormData(this);
            $.ajax({
                xhr: function () {
                    let xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function (evt) {
                        if (evt.lengthComputable) {
                            let percentComplete = Math.round((evt.loaded / evt.total) * 100);
                            $('#progressBar').css('width', percentComplete + '%');
                            $('#progressBar').text(percentComplete + '%');
                        }
                    }, false);
                    return xhr;
                },
                url: 'upload_answer_video.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    alert('Upload successful!');
                    if (response.endsWith(".mp4") || response.endsWith(".webm")) {
                        $('#preview').html(`<video src="${response}" controls width="400"></video>`);
                    } else if (response.endsWith(".ogg") || response.endsWith(".mp3")) {
                        $('#preview').html(`<audio src="${response}" controls></audio>`);
                    } else {
                        $('#preview').html("Preview not available");
                    }
                    $('#progressBar').css('width', '0%').text('0%');
                },
                error: function () {
                    alert('Upload failed.');
                    $('#progressBar').css('width', '0%').text('0%');
                }
            });
        });
    </script>
</body>
</html>
