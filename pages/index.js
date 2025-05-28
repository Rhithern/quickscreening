import { useEffect, useRef, useState } from 'react';

export default function Home() {
  const videoRef = useRef(null);
  const mediaRecorderRef = useRef(null);
  const [recording, setRecording] = useState(false);
  const [videoUrl, setVideoUrl] = useState(null);

  useEffect(() => {
    navigator.mediaDevices.getUserMedia({ video: true, audio: true })
      .then((stream) => {
        videoRef.current.srcObject = stream;
      });
  }, []);

  const startRecording = () => {
    const stream = videoRef.current.srcObject;
    const recorder = new MediaRecorder(stream);
    let chunks = [];

    recorder.ondataavailable = (e) => chunks.push(e.data);
    recorder.onstop = async () => {
      const blob = new Blob(chunks, { type: 'video/webm' });
      const url = URL.createObjectURL(blob);
      setVideoUrl(url);

      // Upload to Supabase
      const formData = new FormData();
      formData.append('video', blob, 'interview.webm');
      await fetch('/api/upload', {
        method: 'POST',
        body: formData
      });
    };

    mediaRecorderRef.current = recorder;
    recorder.start();
    setRecording(true);
  };

  const stopRecording = () => {
    mediaRecorderRef.current.stop();
    setRecording(false);
  };

  return (
    <div style={{ padding: 20 }}>
      <h1>QuickScreening</h1>
      <video ref={videoRef} autoPlay playsInline muted style={{ width: '100%', maxWidth: 600 }} />
      <div style={{ marginTop: 10 }}>
        {recording ? (
          <button onClick={stopRecording}>Stop Recording</button>
        ) : (
          <button onClick={startRecording}>Start Recording</button>
        )}
      </div>
      {videoUrl && (
        <div style={{ marginTop: 10 }}>
          <h3>Preview:</h3>
          <video src={videoUrl} controls style={{ width: '100%', maxWidth: 600 }} />
        </div>
      )}
    </div>
  );
}
