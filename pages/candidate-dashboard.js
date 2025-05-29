// pages/candidate-dashboard.js
import { useEffect, useState } from 'react';
import { useRouter } from 'next/router';
import { createClient } from '@supabase/supabase-js';
import { useUser } from '@supabase/auth-helpers-react';

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY
);

export default function CandidateDashboard() {
  const router = useRouter();
  const user = useUser();
  const [checkingAccess, setCheckingAccess] = useState(true);

  useEffect(() => {
    async function verifyAccess() {
      const {
        data: { session },
      } = await supabase.auth.getSession();

      if (!session) {
        router.push('/login');
        return;
      }

      const { data: profile } = await supabase
        .from('profiles')
        .select('role')
        .eq('user_id', session.user.id)
        .single();

      if (profile?.role !== 'candidate') {
        router.push('/login');
      } else {
        setCheckingAccess(false);
      }
    }

    verifyAccess();
  }, [router]);

  if (checkingAccess || !user) return <p>Checking access...</p>;

  return (
    <div style={{ maxWidth: 800, margin: 'auto', padding: 20 }}>
      <h1>Candidate Dashboard</h1>
      {/* Replace with actual candidate features */}
      <p>Welcome! You can view your submitted interviews and job opportunities here.</p>
    </div>
  );
}
