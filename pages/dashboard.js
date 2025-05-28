import { useEffect, useState } from 'react';
import { useRouter } from 'next/router';
import { createClient } from '@supabase/supabase-js';

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY
);

export default function Dashboard() {
  const router = useRouter();
  const [session, setSession] = useState(null);
  const [userRole, setUserRole] = useState(null); // 'recruiter' or 'candidate'

  useEffect(() => {
    const currentSession = supabase.auth.getSession().then(({ data }) => {
      if (!data.session) {
        router.push('/login');
      } else {
        setSession(data.session);
        // Fetch user metadata or role from Supabase
        fetchUserRole(data.session.user.id);
      }
    });

    // Listen for auth changes (optional)
    const { data: listener } = supabase.auth.onAuthStateChange((event, session) => {
      if (!session) {
        router.push('/login');
      } else {
        setSession(session);
        fetchUserRole(session.user.id);
      }
    });

    return () => listener?.subscription.unsubscribe();
  }, [router]);

  async function fetchUserRole(userId) {
    // Example: fetch role from a 'profiles' table in Supabase
    let { data, error } = await supabase
      .from('profiles')
      .select('role')
      .eq('id', userId)
      .single();

    if (error) {
      console.error('Error fetching user role:', error);
      return;
    }
    setUserRole(data.role);
  }

  async function logout() {
    await supabase.auth.signOut();
    router.push('/login');
  }

  if (!session) return <div>Loading...</div>;

  return (
    <div>
      <h1>Dashboard</h1>
      <p>Welcome, {session.user.email}</p>
      <p>Your role: {userRole || 'loading...'}</p>

      {userRole === 'recruiter' && (
        <div>
          <h2>Recruiter Panel</h2>
          <p>Here you can view candidate interviews, manage jobs, etc.</p>
          {/* Add recruiter features here */}
        </div>
      )}

      {userRole === 'candidate' && (
        <div>
          <h2>Candidate Panel</h2>
          <p>Here you can record or view your interviews.</p>
          {/* Add candidate features here */}
        </div>
      )}

      <button onClick={logout}>Logout</button>
    </div>
  );
}
