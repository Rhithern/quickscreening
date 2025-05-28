import { useState, useEffect } from 'react';
import { useRouter } from 'next/router';
import { createClient } from '@supabase/supabase-js';
import { useUser } from '@supabase/auth-helpers-react';

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY
);

export default function Apply() {
  const user = useUser();
  const router = useRouter();
  const { job_id } = router.query;

  const [profile, setProfile] = useState(null);
  const [videoFile, setVideoFile] = useState(null);
  const [uploading, setUploading] = useState(false);
  const [success, setSuccess] = useState(false);
  const [errorMsg, setErrorMsg] = useState(null);

  useEffect(() => {
    if (!user) {
      router.push('/login');
    } else {
      fetchProfile();
    }
  }, [user]);

  async function fetchProfile() {
    const { data, error } = await supabase
      .from('profiles')
      .select('*')
      .eq('user_id', user.id)
      .single();

    if (!error) setProfile(data);
  }

  async function handleSubmit(e) {
    e.preventDefault();
    setUploading(true);
    setErrorMsg(null);

    if (!videoFile || !job_id || !profile) {
      setErrorMsg('Missing video, job, or profile info.');
      setUploading(false);
      return;
    }

    const filename = `${user.id}-${Date.now()}.mp4`;
    const { data: uploadData, error: uploadError } = await supabase.storage
      .from('quickscreening')
      .upload(filename, videoFile);

    if (uploadError) {
      setErrorMsg('Failed to upload video.');
      setUploading(false);
      return;
    }

    const { error: insertError } = await supabase.from('videos').insert([
      {
        user_id: profile.id,
        job_id: job_id,
        video_url: uploadData.path,
      },
    ]);

    if (insertError) {
      setErrorMsg('Failed to save video metadata.');
      setUploading(false);
      return;
    }

    setUploading(false);
    setSuccess(true);
  }

  if (!user || !job_id) return <p>Loading...</p>;

  return (
    <div style={{ maxWidth: 600, margin: 'auto', padding: 20 }}>
      <h1>Apply with Video</h1>

      {success ? (
        <div style={{ padding: 20, background: '#e6ffe6', border: '1px solid #00aa00' }}>
          <h2>✅ Success!</h2>
          <p>Your video has been submitted. Thank you!</p>
          <a href="/candidate-dashboard">Go to Dashboard</a>
        </div>
      ) : (
        <form onSubmit={handleSubmit}>
          <label>
            Upload Video (MP4 only):
            <input
              type="file"
              accept="video/mp4"
              onChange={(e) => setVideoFile(e.target.files[0])}
              required
              style={{ display: 'block', marginTop: 10 }}
            />
          </label>

          {errorMsg && <p style={{ color: 'red' }}>{errorMsg}</p>}

          <button type="submit" disabled={uploading} style={{ marginTop: 20 }}>
            {uploading ? 'Submitting...' : 'Submit Application'}
          </button>
        </form>
      )}
    </div>
  );
}
