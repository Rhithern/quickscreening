import { useEffect, useState } from 'react';
import { useRouter } from 'next/router';
import { supabase } from '../lib/supabase';

export default function RecruiterDashboard() {
  const router = useRouter();
  const [videos, setVideos] = useState([]);
  const [user, setUser] = useState(null);

  useEffect(() => {
    const fetchData = async () => {
      const {
        data: { session },
      } = await supabase.auth.getSession();

      if (!session) {
        router.push('/login');
        return;
      }

      setUser(session.user);

      // Ensure logged-in user is a recruiter
      const { data: profile } = await supabase
        .from('profiles')
        .select('role')
        .eq('id', session.user.id)
        .single();

      if (profile?.role !== 'recruiter') {
        router.push('/candidate');
        return;
      }

      const { data, error } = await supabase
        .from('videos')
        .select(`
          id,
          video_url,
          job_role,
          created_at,
          user_id,
          profiles ( full_name, email )
        `)
        .order('created_at', { ascending: false });

      if (error) {
        console.error('Error fetching videos:', error.message);
      } else {
        setVideos(data);
      }
    };

    fetchData();
  }, []);

  const handleLogout = async () => {
    await supabase.auth.signOut();
    router.push('/login');
  };

  return (
    <div className="p-4">
      <h1 className="text-xl font-bold mb-4">Recruiter Dashboard</h1>
      <p>Welcome, {user?.email}</p>

      <h2 className="text-lg font-semibold mt-6 mb-2">Candidate Submissions</h2>
      {videos.length === 0 ? (
        <p>No videos submitted yet.</p>
      ) : (
        <div className="space-y-4">
          {videos.map((video) => (
            <div key={video.id} className="border p-4 rounded-lg shadow">
              <p><strong>Name:</strong> {video.profiles?.full_name || 'N/A'}</p>
              <p><strong>Email:</strong> {video.profiles?.email || 'N/A'}</p>
              <p><strong>Job Role:</strong> {video.job_role}</p>
              <p><strong>Submitted:</strong> {new Date(video.created_at).toLocaleString()}</p>
              <video src={video.video_url} controls className="mt-2 w-full max-w-md" />
            </div>
          ))}
        </div>
      )}

      <button onClick={handleLogout} className="text-red-500 mt-8 block">
        Logout
      </button>
    </div>
  );
}
