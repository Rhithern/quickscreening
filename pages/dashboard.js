import Link from 'next/link';
import { useEffect, useState } from 'react';
import { useRouter } from 'next/router';
import { supabase } from '../lib/supabase';

export default function Dashboard() {
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

      const { data: list, error } = await supabase
        .storage
        .from('videos')
        .list(session.user.id + '/', {
          limit: 100,
          offset: 0,
          sortBy: { column: 'created_at', order: 'desc' },
        });

      if (error) {
        console.error('Error fetching video list:', error.message);
      } else {
        const urls = await Promise.all(
          list.map(async (file) => {
            const { data: signedUrlData } = await supabase
              .storage
              .from('videos')
              .createSignedUrl(`${session.user.id}/${file.name}`, 3600);

            return { name: file.name, url: signedUrlData?.signedUrl };
          })
        );

        setVideos(urls);
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
      <nav className="flex gap-4 mb-4">
        <Link href="/dashboard" className="text-blue-500 hover:underline">Dashboard</Link>
        <Link href="/profile" className="text-blue-500 hover:underline">My Profile</Link>
        <button onClick={handleLogout} className="text-red-500 hover:underline">
          Logout
        </button>
      </nav>

      <h1 className="text-xl font-bold mb-4">My Video Submissions</h1>

      {videos.length === 0 ? (
        <p>No videos uploaded yet.</p>
      ) : (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          {videos.map((video) => (
            <div key={video.name} className="border p-2 rounded">
              <video src={video.url} controls className="w-full rounded" />
              <p className="mt-2 text-sm text-gray-700">{video.name}</p>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
