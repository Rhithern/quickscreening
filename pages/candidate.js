import { useEffect, useState } from 'react';
import { useRouter } from 'next/router';
import { supabase } from '../lib/supabase';

export default function CandidateDashboard() {
  const router = useRouter();
  const [user, setUser] = useState(null);

  useEffect(() => {
    const checkAuth = async () => {
      const {
        data: { session },
      } = await supabase.auth.getSession();

      if (!session) {
        router.push('/login');
        return;
      }

      setUser(session.user);
    };

    checkAuth();
  }, []);

  const handleLogout = async () => {
    await supabase.auth.signOut();
    router.push('/login');
  };

  return (
    <div className="p-4">
      <h1 className="text-xl font-bold mb-4">Candidate Dashboard</h1>
      <p>Welcome, {user?.email}</p>
      <button onClick={handleLogout} className="text-red-500 mt-4">
        Logout
      </button>
    </div>
  );
}
