<!DOCTYPE html>
<html>
<head>
    <title>Record Answer</title>
</head>
<body>
    <h3>Record Your Answer</h3>
    <video id="preview" width="480" height="360" autoplay muted></video>
    <br>
    <button id="startBtn">Start</button>
    <button id="stopBtn" disabled>Stop & Upload</button>

    <div id="statusMessage" style="margin-top: 15px; font-weight: bold;"></div>

    <script>
    let mediaRecorder;
    let recordedChunks = [];

    const statusMessage = document.getElementById('statusMessage');

    navigator.mediaDevices.getUserMedia({ video: true, audio: true })
        .then(stream => {
            document.getElementById("preview").srcObject = stream;
            mediaRecorder = new MediaRecorder(stream);
            mediaRecorder.ondataavailable = e => recordedChunks.push(e.data);
            mediaRecorder.onstop = async () => {
                let blob = new Blob(recordedChunks, { type: 'video/webm' });
                let formData = new FormData();
                formData.append('video', blob);

                statusMessage.textContent = 'Uploading... please wait.';
                try {
                    const response = await fetch('upload_video.php', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();
                    if (result.status === 'success') {
                        statusMessage.style.color = 'green';
                        statusMessage.textContent = result.message;
                    } else {
                        statusMessage.style.color = 'red';
                        statusMessage.textContent = 'Error: ' + result.message;
                    }
                } catch (error) {
                    statusMessage.style.color = 'red';
                    statusMessage.textContent = 'Upload failed: ' + error.message;
                }
            };

            document.getElementById("startBtn").onclick = () => {
                recordedChunks = [];
                mediaRecorder.start();
                document.getElementById("stopBtn").disabled = false;
                statusMessage.textContent = '';
            };

            document.getElementById("stopBtn").onclick = () => {
                mediaRecorder.stop();
                document.getElementById("stopBtn").disabled = true;
            };
        })
        .catch(err => {
            statusMessage.style.color = 'red';
            statusMessage.textContent = 'Could not access webcam: ' + err.message;
        });
    </script>
</body>
</html>
