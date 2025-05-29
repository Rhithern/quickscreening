import { createClient } from '@supabase/supabase-js';

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY
);

export default async function handler(req, res) {
  if (req.method !== 'POST') {
    return res.status(405).json({ error: 'Method not allowed' });
  }

  const { token, user_id } = req.body;

  const { data: invite, error: inviteError } = await supabase
    .from('team_invites')
    .select('*')
    .eq('token', token)
    .single();

  if (inviteError || !invite) {
    return res.status(400).json({ error: 'Invalid or expired invite token' });
  }

  const { data: newMember, error: memberError } = await supabase
    .from('team_members')
    .insert([{ team_id: invite.team_id, user_id, role: invite.role }]);

  if (memberError) {
    return res.status(500).json({ error: 'Failed to add team member' });
  }

  // Optionally delete the invite
  await supabase.from('team_invites').delete().eq('token', token);

  return res.status(200).json({ success: true });
}
