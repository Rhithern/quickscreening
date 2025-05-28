import { useEffect, useRef, useState } from 'react';
import { createClient } from '@supabase/supabase-js';

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY
);

export default function Home() {
  const videoRef = useRef(null);
  const [recording, setRecording] = useState(false);
  const [mediaRecorder, setMediaRecorder] = useState(null);
  const [videoURL, setVideoURL] = useState(null);

  useEffect(() => {
    async function setupCamera() {
      const stream = await navigator.mediaDevices.getUserMedia({ video: true });
      videoRef.current.srcObject = stream;
    }
    setupCamera();
  }, []);

  const startRecording = () => {
    const stream = videoRef.current.srcObject;
    const recorder = new MediaRecorder(stream);
    const chunks = [];

    recorder.ondataavailable = (e) => chunks.push(e.data);
    recorder.onstop = async () => {
      const blob = new Blob(chunks, { type: 'video/webm' });
      const file = new File([blob], 'interview.webm', { type: 'video/webm' });

      // Upload to Supabase Storage
      const { data, error } = await supabase.storage
        .from('videos')
        .upload(`recordings/${Date.now()}_interview.webm`, file);

      if (error) {
        console.error('Upload error:', error.message);
      } else {
        const { data: publicUrlData } = supabase.storage
          .from('videos')
          .getPublicUrl(data.path);
        setVideoURL(publicUrlData.publicUrl);
      }
    };

    recorder.start();
    setMediaRecorder(recorder);
    setRecording(true);
  };

  const stopRecording = () => {
    mediaRecorder.stop();
    setRecording(false);
  };

  return (
    <div style={{ padding: '2rem' }}>
      <h1>QuickScreening – One-Way Video Interview</h1>
      <video ref={videoRef} autoPlay playsInline muted width={640} height={480} />
      <div style={{ marginTop: '1rem' }}>
        {!recording ? (
          <button onClick={startRecording}>Start Recording</button>
        ) : (
          <button onClick={stopRecording}>Stop Recording</button>
        )}
      </div>
      {videoURL && (
        <div style={{ marginTop: '1rem' }}>
          <h2>Uploaded Video:</h2>
          <video src={videoURL} controls width={640} />
        </div>
      )}
    </div>
  );
}
