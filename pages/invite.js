import { useEffect, useState } from 'react';
import { useRouter } from 'next/router';
import { useUser } from '@supabase/auth-helpers-react';

export default function AcceptInvite() {
  const router = useRouter();
  const { token } = router.query;
  const user = useUser();
  const [message, setMessage] = useState('');

  useEffect(() => {
    if (!token || !user) return;

    const acceptInvite = async () => {
      const res = await fetch('/api/team/accept', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'x-user-id': user.id,
        },
        body: JSON.stringify({ token }),
      });

      const data = await res.json();
      if (res.ok) {
        setMessage('Invite accepted! You are now part of the team.');
        router.push('/recruiter-dashboard');
      } else {
        setMessage(data.error || 'Something went wrong.');
      }
    };

    acceptInvite();
  }, [token, user]);

  return <div style={{ padding: 20 }}>{message || 'Accepting invite...'}</div>;
}

