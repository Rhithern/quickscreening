import { useEffect, useState } from 'react';
import { useRouter } from 'next/router';
import { useUser } from '@supabase/auth-helpers-react';
import { createClient } from '@supabase/supabase-js';

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

      // Get the profile ID for the logged-in user
      const { data: profileData, error: profileError } = await supabase
        .from('profiles')
        .select('id')
        .eq('user_id', user.id)
        .single();

      if (profileError) {
        console.error('Profile fetch error:', profileError);
        setLoading(false);
        return;
      }

      // Get all video submissions by this user
      const { data: videosData, error: videosError } = await supabase
        .from('videos')
        .select('id, video_url, job_id, created_at, jobs(title)')
        .eq('user_id', profileData.id)
        .order('created_at', { ascending: false });

      if (videosError) {
        console.error('Videos fetch error:', videosError);
        setLoading(false);
        return;
      }

      setVideos(videosData);
      setLoading(false);
    }

    fetchVideos();
  }, [user, router]);

  if (!user) return null;

  return (
    <div style={{ maxWidth: 800, margin: 'auto', padding: 20 }}>
      <h1>Candidate Dashboard</h1>

      {loading ? (
        <p>Loading your submissions...</p>
      ) : videos.length === 0 ? (
        <p>You haven't submitted any video interviews yet.</p>
      ) : (
        <ul style={{ listStyle: 'none', paddingLeft: 0 }}>
          {videos.map((video) => (
            <li key={video.id} style={{ marginBottom: 30 }}>
              <h3>{video.jobs?.title || 'Job'}</h3>
              <video
                src={video.video_url}
                controls
                style={{ width: '100%', maxHeight: 240 }}
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
