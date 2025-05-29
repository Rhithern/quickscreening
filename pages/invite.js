import { useEffect, useState } from 'react';
import { useRouter } from 'next/router';
import { createClient } from '@supabase/supabase-js';

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY
);

export default function InvitePage() {
  const router = useRouter();
  const { token } = router.query; // we assume invite links have ?token=...

  const [invite, setInvite] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [accepted, setAccepted] = useState(false);

  useEffect(() => {
    if (!token) return;

    async function fetchInvite() {
      const { data, error } = await supabase
        .from('team_invites')
        .select('id, email, role, status')
        .eq('token', token)
        .single();

      if (error || !data) {
        setError('Invalid or expired invite token.');
      } else if (data.status === 'accepted') {
        setError('This invite has already been accepted.');
      } else {
        setInvite(data);
      }
      setLoading(false);
    }

    fetchInvite();
  }, [token]);

  async function acceptInvite() {
    if (!invite) return;
    setLoading(true);

    // Call API to accept invite (we'll implement this next)
    const res = await fetch('/api/team/accept', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ token }),
    });

    if (res.ok) {
      setAccepted(true);
    } else {
      const data = await res.json();
      setError(data.error || 'Failed to accept invite.');
    }
    setLoading(false);
  }

  if (loading) return <p>Loading invite...</p>;
  if (error) return <p style={{ color: 'red' }}>{error}</p>;
  if (accepted) return <p>Invite accepted! You are now part of the team.</p>;

  return (
    <div style={{ maxWidth: 600, margin: 'auto', padding: 20 }}>
      <h1>You're invited!</h1>
      <p>
        Email: <strong>{invite.email}</strong>
      </p>
      <p>
        Role: <strong>{invite.role}</strong>
      </p>
      <button onClick={acceptInvite}>Accept Invite</button>
    </div>
  );
}
