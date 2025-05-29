// /pages/api/accept-invite.js
import { createClient } from '@supabase/supabase-js';

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY
);

export default async function handler(req, res) {
  if (req.method !== 'POST') return res.status(405).end();

  const { token, user_id } = req.body;

  const { data: invite, error } = await supabase
    .from('team_invites')
    .select('*')
    .eq('token', token)
    .eq('accepted', false)
    .single();

  if (error || !invite) {
    return res.status(400).json({ error: 'Invalid or expired invite' });
  }

  const { error: updateProfileError } = await supabase
    .from('profiles')
    .update({ team_id: invite.team_id })
    .eq('user_id', user_id);

  if (updateProfileError) {
    return res.status(500).json({ error: updateProfileError.message });
  }

  await supabase
    .from('team_invites')
    .update({ accepted: true })
    .eq('id', invite.id);

  res.status(200).json({ message: 'Invite accepted' });
}
