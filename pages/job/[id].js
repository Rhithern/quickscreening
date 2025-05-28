import { useState, useRef } from 'react';
import { createClient } from '@supabase/supabase-js';
import { useUser } from '@supabase/auth-helpers-react';

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY
);

export default function JobDetail() {
  // ...existing code...

  const user = useUser();
  const [recording, setRecording] = useState(false);
  const [videoBlob, setVideoBlob] = useState(null);
  const videoRef = useRef(null);
  const mediaRecorderRef = useRef(null);
  const chunksRef = useRef([]);

  // Start recording function
  const startRecording = async () => {
    setRecording(true);
    chunksRef.current = [];

    const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
    videoRef.current.srcObject = stream;
    videoRef.current.play();

    mediaRecorderRef.current = new MediaRecorder(stream);
    mediaRecorderRef.current.ondataavailable = (e) => chunksRef.current.push(e.data);
    mediaRecorderRef.current.onstop = () => {
      const blob = new Blob(chunksRef.current, { type: 'video/webm' });
      setVideoBlob(blob);
      videoRef.current.srcObject.getTracks().forEach(track => track.stop());
      videoRef.current.srcObject = null;
      setRecording(false);
    };

    mediaRecorderRef.current.start();
  };

  // Stop recording
  const stopRecording = () => {
    mediaRecorderRef.current.stop();
  };

  // Upload video to Supabase Storage and save metadata
  const uploadVideo = async () => {
    if (!videoBlob || !user) return;

    const fileName = `videos/${user.id}_${job.id}_${Date.now()}.webm`;

    // Upload to bucket (replace 'quickscreening' with your bucket name)
    const { data, error } = await supabase.storage
      .from('quickscreening')
      .upload(fileName, videoBlob, { contentType: 'video/webm' });

    if (error) {
      alert('Upload failed: ' + error.message);
      return;
    }

    // Get public URL
    const { publicURL } = supabase.storage.from('quickscreening').getPublicUrl(fileName);

    // Save record in videos table
    const { error: insertError } = await supabase.from('videos').insert([
      {
        job_id: job.id,
        user_id: user.id,
        video_url: publicURL,
      },
    ]);

    if (insertError) {
      alert('Failed to save video metadata: ' + insertError.message);
      return;
    }

    alert('Video uploaded successfully!');
    setVideoBlob(null);
  };

  return (
    <div style={{ maxWidth: 700, margin: 'auto', padding: 20 }}>
      {/* Job details */}
      <h1>{job.title}</h1>
      <p>{job.description}</p>

      {/* Video recorder */}
      <div>
        <video ref={videoRef} width="320" height="240" controls />

        {!recording && (
          <button onClick={startRecording}>Start Recording</button>
        )}
        {recording && (
          <button onClick={stopRecording}>Stop Recording</button>
        )}
      </div>

      {/* Upload button */}
      {videoBlob && (
        <>
          <p>Video ready to upload</p>
          <button onClick={uploadVideo}>Upload Video</button>
        </>
      )}
    </div>
  );
}
