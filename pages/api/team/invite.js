import { createClient } from '@supabase/supabase-js';

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY
);

export default async function handler(req, res) {
  if (req.method !== 'POST') {
    return res.status(405).json({ error: 'Method not allowed' });
  }

  const { email, role } = req.body;

  if (!email || !role) {
    return res.status(400).json({ error: 'Email and role are required' });
  }

  try {
    // Get the user who is sending the invite (assumed authenticated)
    // You might need to pass user ID in the request headers or session, or validate another way.
    // For now, let's just assume the inviter is the user (you can enhance later).

    // Look up the inviter's profile or team_id to assign invites accordingly
    // For demo, we assume the inviter is team admin and use a hardcoded team_id (replace this!)
    const inviterUserId = req.headers['x-user-id'];
    if (!inviterUserId) {
      return res.status(401).json({ error: 'Unauthorized' });
    }

    // Fetch team_id of inviter from profiles or teams table
    const { data: inviterProfile, error: profileError } = await supabase
      .from('profiles')
      .select('team_id')
      .eq('user_id', inviterUserId)
      .single();

    if (profileError || !inviterProfile?.team_id) {
      return res.status(400).json({ error: 'Unable to find inviter team' });
    }

    const teamId = inviterProfile.team_id;

    // Insert invite record into a "team_invites" table (you need to create this table in your DB)

    const { error: insertError } = await supabase.from('team_invites').insert({
      email,
      role,
      team_id: teamId,
      invited_by: inviterUserId,
      status: 'pending',
      invited_at: new Date().toISOString(),
    });

    if (insertError) {
      return res.status(500).json({ error: 'Failed to save invite' });
    }

    // TODO: Send invitation email with a link to accept (via external mail service or Supabase SMTP)

    return res.status(200).json({ message: 'Invite sent' });
  } catch (error) {
    console.error('Invite API error:', error);
    return res.status(500).json({ error: 'Server error' });
  }
}
