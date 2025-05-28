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
  const [profile, setProfile] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const checkSessionAndProfile = async () => {
      const { data: { session } } = await supabase.auth.getSession();
      if (!session) {
        router.push('/login');
        return;
      }

      setSession(session);

      const { data: profileData, error } = await supabase
        .from('profiles')
        .select('*')
        .eq('user_id', session.user.id)
        .single();

      if (error) {
        console.error('Error fetching profile:', error);
      } else {
        setProfile(profileData);
      }

      setLoading(false);
    };

    checkSessionAndProfile();
  }, [router]);

  if (loading || !session) return <p>Loading...</p>;

  return (
    <div style={{ padding: 20 }}>
      <h1>Welcome to QuickScreening</h1>

      {/* 👇 Role-based dashboard links */}
      {profile && (
        <>
          {profile.role === 'candidate' && (
            <a href="/candidate-dashboard" style={{ display: 'block', marginBottom: 10 }}>
              🎥 My Dashboard
            </a>
          )}
          {profile.role === 'recruiter' && (
            <a href="/recruiter-dashboard" style={{ display: 'block', marginBottom: 10 }}>
              📋 Recruiter Dashboard
            </a>
          )}
        </>
      )}

      <button
        onClick={async () => {
          await supabase.auth.signOut();
          router.push('/login');
        }}
      >
        Logout
      </button>
    </div>
  );
}
