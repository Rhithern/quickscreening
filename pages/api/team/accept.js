import { createClient } from '@supabase/supabase-js';

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY,
  { global: { headers: { Authorization: `Bearer ${process.env.SUPABASE_SERVICE_ROLE_KEY}` } } }
);

export default async function handler(req, res) {
  if (req.method !== 'POST') return res.status(405).end();

  const { token } = req.body;
  const userId = req.headers['x-user-id'];

  const { data: invite, error: inviteError } = await supabase
    .from('team_invites')
    .select('*')
    .eq('token', token)
    .eq('status', 'pending')
    .single();

  if (inviteError || !invite) {
    return res.status(400).json({ error: 'Invalid or expired invite.' });
  }

  const { error: insertError } = await supabase.from('team_members').insert({
    team_id: invite.team_id,
    user_id: userId,
    role: invite.role,
  });

  if (insertError) {
    return res.status(500).json({ error: 'Could not add to team.' });
  }

  await supabase.from('team_invites').update({ status: 'accepted' }).eq('id', invite.id);

  res.status(200).json({ message: 'Invite accepted' });
}

