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
      const { data, error } = await supabase
        .from('videos')
        .select('*')
        .eq('user_id', user.id)
        .order('created_at', { ascending: false });

      if (error) {
        console.error('Error fetching videos:', error);
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
      {/* Navigation */}
      <nav style={{ marginBottom: 20 }}>
        <a href="/candidate-dashboard" style={{ marginRight: 15, fontWeight: 'bold' }}>
          Candidate Dashboard
        </a>
        <a href="/candidate-live-interviews" style={{ marginRight: 15 }}>
          📅 Live Interviews
        </a>
        <button
          onClick={async () => {
            await supabase.auth.signOut();
            router.push('/login');
          }}
          style={{ cursor: 'pointer' }}
        >
          Logout
        </button>
      </nav>

      <h1>Your Submitted Videos</h1>

      {loading ? (
        <p>Loading videos...</p>
      ) : videos.length === 0 ? (
        <p>You have not submitted any videos yet.</p>
      ) : (
        <ul>
          {videos.map((video) => (
            <li key={video.id} style={{ marginBottom: 20 }}>
              <video width="320" height="240" controls>
                <source src={video.video_url} type="video/mp4" />
                Your browser does not support the video tag.
              </video>
              <p>
                <strong>Job ID:</strong> {video.job_id} <br />
                <small>Submitted on: {new Date(video.created_at).toLocaleString()}</small>
              </p>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
