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
        .select('id, job_id, url, created_at')
        .eq('user_id', user.id)
        .order('created_at', { ascending: false });

      if (error) {
        console.error('Error fetching videos:', error);
        setVideos([]);
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
      <h1>Your Submitted Videos</h1>

      {loading ? (
        <p>Loading your videos...</p>
      ) : videos.length === 0 ? (
        <p>You have not submitted any videos yet.</p>
      ) : (
        videos.map((video) => (
          <div
            key={video.id}
            style={{ marginBottom: 20, border: '1px solid #ccc', padding: 12, borderRadius: 6 }}
          >
            <p>
              Job ID: {video.job_id} <br />
              Submitted on: {new Date(video.created_at).toLocaleString()}
            </p>
            <video src={video.url} controls width="100%" style={{ borderRadius: 6 }} />
          </div>
        ))
      )}
    </div>
  );
}
