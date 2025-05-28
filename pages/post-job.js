import { useState, useEffect } from 'react';
import { useRouter } from 'next/router';
import { createClient } from '@supabase/supabase-js';
import { useUser } from '@supabase/auth-helpers-react';
import { v4 as uuidv4 } from 'uuid';

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY
);

export default function PostJob() {
  const user = useUser();
  const router = useRouter();

  const [title, setTitle] = useState('');
  const [description, setDescription] = useState('');
  const [videoFile, setVideoFile] = useState(null);
  const [audioFile, setAudioFile] = useState(null);
  const [saving, setSaving] = useState(false);
  const [errorMsg, setErrorMsg] = useState(null);

  useEffect(() => {
    if (!user) {
      router.push('/login');
    }
  }, [user, router]);

  async function handleFileUpload(file, folder) {
    const filename = `${folder}/${uuidv4()}-${file.name}`;
    const { error } = await supabase.storage
      .from('quickscreening')
      .upload(filename, file);

    if (error) {
      throw new Error(`Failed to upload ${folder} file`);
    }

    const { data } = supabase.storage
      .from('quickscreening')
      .getPublicUrl(filename);

    return data.publicUrl;
  }

  async function handleSubmit(e) {
    e.preventDefault();
    setSaving(true);
    setErrorMsg(null);

    try {
      // Get recruiter profile id
      const { data: profile, error: profileError } = await supabase
        .from('profiles')
        .select('id')
        .eq('user_id', user.id)
        .single();

      if (profileError) throw new Error('Failed to find your profile.');

      // Upload files if provided
      let videoUrl = null;
      let audioUrl = null;

      if (videoFile) {
        videoUrl = await handleFileUpload(videoFile, 'video-questions');
      }

      if (audioFile) {
        audioUrl = await handleFileUpload(audioFile, 'audio-questions');
      }

      // Insert job
      const { error } = await supabase.from('jobs').insert([
        {
          title,
          description,
          recruiter_id: profile.id,
          video_question_url: videoUrl,
          audio_question_url: audioUrl
        }
      ]);

      if (error) throw error;

      router.push('/recruiter-dashboard');
    } catch (err) {
      console.error(err);
      setErrorMsg(err.message || 'Error occurred');
    } finally {
      setSaving(false);
    }
  }

  if (!user) return null;

  return (
    <div style={{ maxWidth: 600, margin: 'auto', padding: 20 }}>
      <h1>Post a New Job</h1>
      <form onSubmit={handleSubmit}>
        <div style={{ marginBottom: 12 }}>
          <label>Job Title<br />
            <input
              type="text"
              required
              value={title}
              onChange={(e) => setTitle(e.target.value)}
              style={{ width: '100%', padding: 8 }}
            />
          </label>
        </div>
        <div style={{ marginBottom: 12 }}>
          <label>Job Description<br />
            <textarea
              required
              rows={6}
              value={description}
              onChange={(e) => setDescription(e.target.value)}
              style={{ width: '100%', padding: 8 }}
            />
          </label>
        </div>
        <div style={{ marginBottom: 12 }}>
          <label>Upload Video Question (optional)<br />
            <input
              type="file"
              accept="video/*"
              onChange={(e) => setVideoFile(e.target.files[0])}
            />
          </label>
        </div>
        <div style={{ marginBottom: 12 }}>
          <label>Upload Audio Question (optional)<br />
            <input
              type="file"
              accept="audio/*"
              onChange={(e) => setAudioFile(e.target.files[0])}
            />
          </label>
        </div>
        {errorMsg && <p style={{ color: 'red' }}>{errorMsg}</p>}
        <button type="submit" disabled={saving}>
          {saving ? 'Posting...' : 'Post Job'}
        </button>
      </form>
    </div>
  );
}
