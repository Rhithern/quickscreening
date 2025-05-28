import { useRef, useState } from 'react';
import { createClient } from '@supabase/supabase-js';

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL!,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY!
);

export default function Home() {
  const videoRef = useRef<HTMLVideoElement>(null);
  const mediaRecorderRef = useRef<MediaRecorder | null>(null);
  const chunksRef = useRef<Blob[]>([]);
  const [recording, setRecording] = useState(false);
  const [uploading, setUploading] = useState(false);

  const startRecording = async () => {
    const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
    videoRef.current!.srcObject = stream;
    videoRef.current!.play();

    const mediaRecorder = new MediaRecorder(stream);
    mediaRecorderRef.current = mediaRecorder;
    chunksRef.current = [];

    mediaRecorder.ondataavailable = (e) => chunksRef.current.push(e.data);
    mediaRecorder.onstop = async () => {
      const blob = new Blob(chunksRef.current, { type: 'video/webm' });
      await uploadVideo(blob);
    };

    mediaRecorder.start();
>>>>>>> e2bcfe1 (Removed TypeScript files and added JavaScript version)
    setRecording(true);
  };

  const stopRecording = () => {
<<<<<<< HEAD
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
=======
    mediaRecorderRef.current?.stop();
    setRecording(false);

    const stream = videoRef.current?.srcObject as MediaStream;
    stream?.getTracks().forEach((track) => track.stop());
    videoRef.current!.srcObject = null;
  };

  const uploadVideo = async (blob: Blob) => {
    setUploading(true);
    const file = new File([blob], `video-${Date.now()}.webm`, { type: 'video/webm' });
    const { data, error } = await supabase.storage.from('videos').upload(file.name, file);
    if (error) alert('Upload failed: ' + error.message);
    else alert('Upload successful!');
    setUploading(false);
  };

  return (
    <main style={{ padding: 40, textAlign: 'center' }}>
      <h1>QuickScreening</h1>
      <video ref={videoRef} style={{ width: '80%', marginBottom: 20 }} />
      <br />
      {!recording ? (
        <button onClick={startRecording} disabled={uploading}>🎥 Start Recording</button>
      ) : (
        <button onClick={stopRecording}>⏹ Stop Recording</button>
      )}
      {uploading && <p>Uploading video...</p>}
    </main>
>>>>>>> e2bcfe1 (Removed TypeScript files and added JavaScript version)
  );
}
