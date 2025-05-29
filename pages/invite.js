import { useEffect, useState } from 'react';
import { useRouter } from 'next/router';
import { useUser } from '@supabase/auth-helpers-react';
import { createClient } from '@supabase/supabase-js';

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY
);

export default function AcceptInvite() {
  const user = useUser();
  const router = useRouter();
  const { token } = router.query;
  const [message, setMessage] = useState('Processing invite...');

  useEffect(() => {
    if (!token || !user) return;

    const acceptInvite = async () => {
      const { data: invite, error } = await supabase
        .from('team_invites')
        .select('*')
        .eq('token', token)
        .eq('status', 'pending')
        .single();

      if (error || !invite) {
        setMessage('Invalid or expired invite.');
        return;
      }

      // Add user to team_members
      const { error: insertError } = await supabase.from('team_members').insert([
        {
          team_id: invite.team_id,
          user_id: user.id,
          role: invite.role,
        },
      ]);

      if (insertError) {
        setMessage('Failed to join team.');
        return;
      }

      // Mark invite as accepted
      await supabase
        .from('team_invites')
        .update({ status: 'accepted' })
        .eq('id', invite.id);

      setMessage('You have successfully joined the team!');
    };

    acceptInvite();
  }, [token, user]);

  return (
    <div style={{ padding: 20 }}>
      <h1>Team Invitation</h1>
      <p>{message}</p>
    </div>
  );
}
