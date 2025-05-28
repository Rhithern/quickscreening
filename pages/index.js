import { useEffect, useState } from 'react';
import { useRouter } from 'next/router';
import { createClient } from '@supabase/supabase-js';

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY
);

export default function Home() {
  const router = useRouter();
  const [session, setSession] = useState(null);
  const [role, setRole] = useState(null);

  useEffect(() => {
    const checkSession = async () => {
      const { data: { session } } = await supabase.auth.getSession();
      if (!session) {
        router.push('/login');
      } else {
        setSession(session);
        // Get role from profiles
        const { data: profile } = await supabase
          .from('profiles')
          .select('role')
          .eq('user_id', session.user.id)
          .single();

        if (profile?.role) setRole(profile.role);
      }
    };

    checkSession();
  }, []);

  if (!session) return <p>Loading...</p>;

  return (
    <div style={{ maxWidth: 600, margin: 'auto', padding: 20 }}>
      <h1>Welcome to QuickScreening</h1>

      <nav style={{ marginBottom: 20 }}>
        {role === 'recruiter' && (
          <>
            <a href="/recruiter-dashboard" style={{ marginRight: 15 }}>
              Recruiter Dashboard
            </a>
            <a href="/schedule-live-interview" style={{ marginRight: 15 }}>
              Schedule Live Interview
            </a>
          </>
        )}

        {role === 'candidate' && (
          <>
            <a href="/candidate-dashboard" style={{ marginRight: 15 }}>
              Candidate Dashboard
            </a>
            <a href="/candidate-live-interviews" style={{ marginRight: 15 }}>
              Live Interviews
            </a>
          </>
        )}
      </nav>
    </div>
  );
}
