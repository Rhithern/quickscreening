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

  useEffect(() => {
    const checkSession = async () => {
      const { data: { session } } = await supabase.auth.getSession();
      if (!session) {
        router.push('/login');
      } else {
        setSession(session);
      }
    };

    checkSession();
  }, [router]);

  if (!session) return <p>Loading...</p>;

  return (
    <div style={{ maxWidth: 800, margin: 'auto', padding: 20 }}>
      <h1>Welcome to QuickScreening</h1>

      <nav style={{ marginBottom: 20 }}>
        <a href="/recruiter-dashboard" style={{ marginRight: 20 }}>
          Recruiter Dashboard
        </a>
        <a href="/candidate-dashboard" style={{ marginRight: 20 }}>
          Candidate Dashboard
        </a>
      </nav>

      <button
        onClick={async () => {
          await supabase.auth.signOut();
          router.push('/login');
        }}
      >
        Logout
      </button>

      {/* You can add more UI below */}
    </div>
  );
}
