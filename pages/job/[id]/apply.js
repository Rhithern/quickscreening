import { useState, useEffect } from 'react';
import { useRouter } from 'next/router';
import { createClient } from '@supabase/supabase-js';
import { useUser } from '@supabase/auth-helpers-react';
import { v4 as uuidv4 } from 'uuid';

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY
);

export default function ApplyToJob() {
  const user = useUser();
  const router = useRouter();
  const { id: jobId } = router.query;

  const [job, setJob] = useState(null);
  const [videoFile, setVideoFile] = useState(null);
  const [uploading, setUploading] = useState(false);
  const [errorMsg, setErrorMsg] = useState('');

  useEffect(() => {
    if (!user) {
      router.push('/login');
    } else if (jobId) {
      supabase
        .from('jobs')
        .select('*')
        .eq('id', jobId)
        .single()
        .then(({ data, error }) => {
          if (error) {
            setErrorMsg('Job not found');
          } else {
            setJob(data);
          }
        });
    }
  }, [user, jobId]);

  async function handleSubmit(e) {
    e.preventDefault();
    setUploading(true);
    setErrorMsg('');

    if (!videoFile || !jobId) {
      setErrorMsg('Please select a video file.');
      setUploading(false);
      return;
    }

    const fileExt = videoFile.name.split('.').pop();
    const fileName = `${uuidv4()}.${fileExt}`;
    const filePath = `${user.id}/${fileName}`;

    // Upload to Supabase Storage
    const { error: uploadError } = await supabase.storage
      .from('quickscreening')
      .upload(filePath, videoFile);

    if (uploadError) {
      setErrorMsg('Video upload failed.');
      setUploading(false);
      return;
    }

    // Insert into videos table
    const { error: insertError } = await supabase.from('videos').insert([
      {
        job_id: jobId,
        user_id: user.id,
        video_path: filePath,
      },
    ]);

    if (insertError) {
      setErrorMsg('Failed to save video metadata.');
      setUploading(false);
      return;
    }

    alert('Video submitted successfully!');
    router.push('/candidate-dashboard');
  }

  if (!user || !job) return <p>Loading...</p>;

  return (
    <div style={{ maxWidth: 600, margin: 'auto', padding: 20 }}>
      <h1>Apply to: {job.title}</h1>
      <p>{job.description}</p>

      <form onSubmit={handleSubmit}>
        <input
          type="file"
          accept="video/*"
          onChange={(e) => setVideoFile(e.target.files[0])}
          required
        />
        <br /><br />
        <button type="submit" disabled={uploading}>
          {uploading ? 'Uploading...' : 'Submit Video'}
        </button>
      </form>

      {errorMsg && <p style={{ color: 'red' }}>{errorMsg}</p>}
    </div>
  );
}
