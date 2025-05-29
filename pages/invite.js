import { useRouter } from 'next/router';
import { useEffect, useState } from 'react';
import { createClient } from '@supabase/supabase-js';
import { useUser } from '@supabase/auth-helpers-react';

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY
);

export default function AcceptInvite() {
  const { query } = useRouter();
  const user = useUser();
  const [message, setMessage] = useState('Processing invite...');

  useEffect(() => {
    if (!query.token || !user) return;

    const acceptInvite = async () => {
      const { data, error } = await fetch('/api/team/accept', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ token: query.token, user_id: user.id }),
      }).then((res) => res.json());

      if (error || data?.error) {
        setMessage('Failed to accept invite.');
      } else {
        setMessage('You have successfully joined the team.');
      }
    };

    acceptInvite();
  }, [query.token, user]);

  return (
    <div style={{ padding: 30 }}>
      <h1>{message}</h1>
    </div>
  );
}
