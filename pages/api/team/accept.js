import { createClient } from '@supabase/supabase-js';

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY
);

export default async function handler(req, res) {
  if (req.method !== 'POST') {
    return res.status(405).json({ error: 'Method not allowed' });
  }

  const { token } = req.body;
  if (!token) {
    return res.status(400).json({ error: 'Missing invite token' });
  }

  // Verify invite exists and not accepted yet
  const { data: invite, error: inviteError } = await supabase
    .from('team_invites')
    .select('*')
    .eq('token', token)
    .single();

  if (inviteError || !invite) {
    return res.status(404).json({ error: 'Invite not found' });
  }

  if (invite.status === 'accepted') {
    return res.status(400).json({ error: 'Invite already accepted' });
  }

  // Here you would normally get user info from the auth session
  // For demo, let's assume you get user_id from a session or token
  // You must adapt this with your auth system (e.g. Supabase auth)
  // I'll put a placeholder:
  const user_id = req.headers['x-user-id'];
  if (!user_id) {
    return res.status(401).json({ error: 'Unauthorized: User not logged in' });
  }

  // Fetch profile to get team_id
  const { data: profile, error: profileError } = await supabase
    .from('profiles')
    .select('*')
    .eq('user_id', user_id)
    .single();

  if (profileError || !profile) {
    return res.status(404).json({ error: 'User profile not found' });
  }

  // Update invite status to accepted
  const { error: updateError } = await supabase
    .from('team_invites')
    .update({ status: 'accepted', accepted_at: new Date().toISOString() })
    .eq('id', invite.id);

  if (updateError) {
    return res.status(500).json({ error: 'Failed to update invite status' });
  }

  // Add user to team_members table if not exists
  const { error: memberError } = await supabase
    .from('team_members')
    .upsert({
      team_id: profile.team_id,
      user_id: user_id,
      role: invite.role,
    }, { onConflict: ['team_id', 'user_id'] });

  if (memberError) {
    return res.status(500).json({ error: 'Failed to add user to team' });
  }

  res.status(200).json({ message: 'Invite accepted and user added to team' });
}
