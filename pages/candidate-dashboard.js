import { useEffect, useState } from 'react';
import { useRouter } from 'next/router';
import { createClient } from '@supabase/supabase-js';
import { useUser } from '@supabase/auth-helpers-react';

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY
);

export default function CandidateDashboard() {
  const user = useUser();
  const router = useRouter();

  const [videos, setVideos] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    if (!user) {
      router.push('/login');
      return;
    }

    async function fetchVideos() {
      setLoading(true);
      setError(null);

      try {
        // Get candidate profile id
        const { data: profile, error: profileError } = await supabase
          .from('profiles')
          .select('id')
          .eq('user_id', user.id)
          .single();

        if (profileError) throw profileError;

        // Fetch videos with job info
        const { data, error } = await supabase
          .from('videos')
          .select(`
            id,
            video_url,
            question_index,
            submitted_at,
            job:jobs (
              id,
              title
            )
          `)
          .eq('user_id', profile.id)
          .order('submitted_at', { ascending: false });

        if (error) throw error;

        setVideos(data);
      } catch (err) {
        console.error(err);
        setError('Failed to load your submissions');
      } finally {
        setLoading(false);
      }
    }

    fetchVideos();
  }, [user, router]);

  if (!user) return null;

  if (loading) return <p>Loading your submitted videos...</p>;
  if (error) return <p style={{ color: 'red' }}>{error}</p>;

  if (videos.length === 0) {
    return <p>You have not submitted any video answers yet.</p>;
  }

  // Group videos by job
  const videosByJob = videos.reduce((acc, video) => {
    if (!acc[video.job.id]) acc[video.job.id] = { job: video.job, videos: [] };
    acc[video.job.id].videos.push(video);
    return acc;
  }, {});

  return (
    <div style={{ maxWidth: 800, margin: 'auto', padding: 20 }}>
      <h1>Your Video Submissions</h1>

      {Object.values(videosByJob).map(({ job, videos }) => (
        <div key={job.id} style={{ marginBottom: 40 }}>
          <h2>{job.title}</h2>

          {videos.map((vid) => (
            <div key={vid.id} style={{ marginBottom: 20 }}>
              <p><strong>Question #{vid.question_index + 1}</strong></p>
              <video
                src={vid.video_url}
                controls
                style={{ width: '100%', maxHeight: 300 }}
              />
              <p>Submitted: {new Date(vid.submitted_at).toLocaleString()}</p>
            </div>
          ))}
        </div>
      ))}
    </div>
  );
}
