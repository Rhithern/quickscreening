import { useState, useRef } from 'react';
import { createClient } from '@supabase/supabase-js';

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY
);

export default function Home() {
  const [recording, setRecording] = useState(false);
  const [videoBlob, setVideoBlob] = useState(null);
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [job_role, setJobRole] = useState('');
  const videoRef = useRef(null);
  const mediaRecorderRef = useRef(null);
  const streamRef = useRef(null);
  const chunksRef = useRef([]);

  const startRecording = async () => {
    const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
    streamRef.current = stream;
    videoRef.current.srcObject = stream;
    const mediaRecorder = new MediaRecorder(stream);
    chunksRef.current = [];

    mediaRecorder.ondataavailable = (e) => {
      if (e.data.size > 0) chunksRef.current.push(e.data);
    };

    mediaRecorder.onstop = () => {
      const blob = new Blob(chunksRef.current, { type: 'video/webm' });
      setVideoBlob(blob);
      videoRef.current.srcObject = null;
      videoRef.current.src = URL.createObjectURL(blob);
    };

    mediaRecorderRef.current = mediaRecorder;
    mediaRecorder.start();
    setRecording(true);
  };

  const stopRecording = () => {
    mediaRecorderRef.current.stop();
    streamRef.current.getTracks().forEach((track) => track.stop());
    setRecording(false);
  };

  const uploadVideo = async () => {
    if (!videoBlob) return;
    if (!name || !email || !job_role) {
      alert('Please fill in all fields before uploading.');
      return;
    }

    const fileName = `${Date.now()}.webm`;
    const { data, error } = await supabase.storage.from('videos').upload(fileName, videoBlob);

    if (error) {
      console.error('Upload error:', error);
      alert('Video upload failed.');
      return;
    }

    const { data: publicUrlData } = supabase.storage.from('videos').getPublicUrl(fileName);
    const publicUrl = publicUrlData.publicUrl;

    const { error: insertError } = await supabase
      .from('submissions')
      .insert([
        {
          name,
          email,
          job_role,
          video_url: publicUrl,
        },
      ]);

    if (insertError) {
      console.error('Insert error:', insertError);
      alert('Metadata upload failed.');
    } else {
      alert('Video and metadata uploaded successfully!');
      setName('');
      setEmail('');
      setJobRole('');
      setVideoBlob(null);
    }
  };

  return (
    <div style={{ padding: '2rem' }}>
      <h1>One-Way Interview</h1>

      <div style={{ marginBottom: '1rem' }}>
        <input
          type="text"
          placeholder="Your Name"
          value={name}
          onChange={(e) => setName(e.target.value)}
          style={{ marginRight: '1rem' }}
        />
        <input
          type="email"
          placeholder="Your Email"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          style={{ marginRight: '1rem' }}
        />
        <input
          type="text"
          placeholder="Job Role"
          value={job_role}
          onChange={(e) => setJobRole(e.target.value)}
        />
      </div>

      <video ref={videoRef} autoPlay playsInline controls width="640" height="480" />

      <div style={{ marginTop: '1rem' }}>
        {!recording ? (
          <button onClick={startRecording}>Start Recording</button>
        ) : (
          <button onClick={stopRecording}>Stop Recording</button>
        )}
        <button onClick={uploadVideo} disabled={!videoBlob} style={{ marginLeft: '1rem' }}>
          Upload
        </button>
      </div>
    </div>
  );
}
