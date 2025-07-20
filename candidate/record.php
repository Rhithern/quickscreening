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

    <script>
    let mediaRecorder;
    let recordedChunks = [];

    navigator.mediaDevices.getUserMedia({ video: true, audio: true })
        .then(stream => {
            document.getElementById("preview").srcObject = stream;
            mediaRecorder = new MediaRecorder(stream);
            mediaRecorder.ondataavailable = e => recordedChunks.push(e.data);
            mediaRecorder.onstop = async () => {
                let blob = new Blob(recordedChunks, { type: 'video/webm' });
                let formData = new FormData();
                formData.append('video', blob);
                await fetch('upload_video.php', { method: 'POST', body: formData });
                alert('Uploaded!');
            };

            document.getElementById("startBtn").onclick = () => {
                recordedChunks = [];
                mediaRecorder.start();
                document.getElementById("stopBtn").disabled = false;
            };

            document.getElementById("stopBtn").onclick = () => {
                mediaRecorder.stop();
                document.getElementById("stopBtn").disabled = true;
            };
        });
    </script>
</body>
</html>

