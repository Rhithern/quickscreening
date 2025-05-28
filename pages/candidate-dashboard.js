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

  useEffect(() => {
    if (!user) {
      router.push('/login');
      return;
    }

    async function fetchVideos() {
      setLoading(true);
      const { data, error } = await supabase
        .from('videos')
        .select('id, video_url, created_at, job_id, jobs(title)')
        .eq('user_id', user.id)
        .order('created_at', { ascending: false })
        .limit(20)
        .leftJoin('jobs', 'jobs.id', 'videos.job_id');

      if (error) {
        console.error(error);
      } else {
        setVideos(data);
      }
      setLoading(false);
    }

    fetchVideos();
  }, [user, router]);

  if (!user) return null;

  return (
    <div style={{ maxWidth: 800, margin: 'auto', padding: 20 }}>
      <h1>Your Video Submissions</h1>

      {loading ? (
        <p>Loading your videos...</p>
      ) : videos.length === 0 ? (
        <p>You have not submitted any videos yet.</p>
      ) : (
        <ul style={{ listStyle: 'none', padding: 0 }}>
          {videos.map((video) => (
            <li key={video.id} style={{ marginBottom: 20 }}>
              <p><strong>Job:</strong> {video.jobs?.title || 'Unknown'}</p>
              <video
                src={video.video_url}
                controls
                style={{ width: '100%', maxHeight: 300 }}
              />
              <p style={{ fontSize: '0.8em', color: '#666' }}>
                Submitted on: {new Date(video.created_at).toLocaleString()}
              </p>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
