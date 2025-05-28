import { useRouter } from 'next/router';
import { useEffect, useState, useRef } from 'react';
import { createClient } from '@supabase/supabase-js';
import { useUser } from '@supabase/auth-helpers-react';

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY
);

export default function JobDetail() {
  const router = useRouter();
  const { id } = router.query;
  const user = useUser();

  const [job, setJob] = useState(null);
  const [loading, setLoading] = useState(true);
  const [errorMsg, setErrorMsg] = useState(null);

  // Video recording refs and state
  const videoRef = useRef(null);
  const mediaRecorderRef = useRef(null);
  const [recording, setRecording] = useState(false);
  const [videoBlob, setVideoBlob] = useState(null);
  const [uploading, setUploading] = useState(false);
  const [uploadMsg, setUploadMsg] = useState('');

  useEffect(() => {
    if (!id) return;

    async function fetchJob() {
      setLoading(true);
      const { data, error } = await supabase
        .from('jobs')
        .select('*')
        .eq('id', id)
        .single();

      if (error) {
        setErrorMsg('Failed to load job.');
      } else {
        setJob(data);
      }
      setLoading(false);
    }

    fetchJob();
  }, [id]);

  // Start video recording
  async function startRecording() {
    setUploadMsg('');
    setVideoBlob(null);

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      alert('Your browser does not support video recording.');
      return;
    }

    try {
      const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
      videoRef.current.srcObject = stream;
      videoRef.current.play();

      const mediaRecorder = new MediaRecorder(stream);
      mediaRecorderRef.current = mediaRecorder;
      const chunks = [];

      mediaRecorder.ondataavailable = (e) => {
        if (e.data.size > 0) chunks.push(e.data);
      };

      mediaRecorder.onstop = () => {
        const blob = new Blob(chunks, { type: 'video/webm' });
        setVideoBlob(blob);

        // Stop all tracks so camera is freed
        stream.getTracks().forEach((track) => track.stop());
        videoRef.current.srcObject = null;
      };

      mediaRecorder.start();
      setRecording(true);
    } catch (err) {
      alert('Could not start video recording: ' + err.message);
    }
  }

  // Stop recording
  function stopRecording() {
    if (mediaRecorderRef.current && recording) {
      mediaRecorderRef.current.stop();
      setRecording(false);
    }
  }

  // Upload video to Supabase Storage & record metadata
  async function uploadVideo() {
    if (!videoBlob) return;

    if (!user) {
      alert('You must be logged in to upload a video.');
      router.push('/login');
      return;
    }

    setUploading(true);
    setUploadMsg('');

    try {
      // Generate unique filename
      const fileExt = 'webm';
      const fileName = `${user.id}_${id}_${Date.now()}.${fileExt}`;

      // Upload to Supabase Storage
      const { error: uploadError } = await supabase.storage
        .from('quickscreening')
        .upload(fileName, videoBlob, { contentType: 'video/webm' });

      if (uploadError) throw uploadError;

      // Get public URL for video
      const { data: { publicUrl } } = supabase.storage
        .from('quickscreening')
        .getPublicUrl(fileName);

      // Insert metadata in videos table
      const { error: insertError } = await supabase
        .from('videos')
        .insert([
          {
            user_id: user.id,
            job_id: id,
            video_url: publicUrl,
          },
        ]);

      if (insertError) throw insertError;

      setUploadMsg('Video uploaded successfully!');
      setVideoBlob(null);
    } catch (error) {
      setUploadMsg('Upload failed: ' + error.message);
    } finally {
      setUploading(false);
    }
  }

  if (loading) return <p>Loading job details...</p>;

  if (errorMsg) return <p style={{ color: 'red' }}>{errorMsg}</p>;

  if (!job) return <p>Job not found.</p>;

  return (
    <div style={{ maxWidth: 700, margin: 'auto', padding: 20 }}>
      <h1>{job.title}</h1>
      <p>{job.description}</p>

      <hr style={{ margin: '20px 0' }} />

      <h2>Record your video answer</h2>
      <video ref={videoRef} style={{ width: '100%', maxHeight: 300 }} controls />

      <div style={{ marginTop: 10 }}>
        {!recording && (
          <button onClick={startRecording} disabled={uploading}>
            Start Recording
          </button>
        )}
        {recording && (
          <button onClick={stopRecording} disabled={uploading}>
            Stop Recording
          </button>
        )}
        {videoBlob && !uploading && (
          <button onClick={uploadVideo}>
            Upload Video
          </button>
        )}
        {uploading && <p>Uploading video...</p>}
        {uploadMsg && <p>{uploadMsg}</p>}
      </div>
    </div>
  );
}
