import { createClient } from '@supabase/supabase-js';

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.SUPABASE_SERVICE_ROLE_KEY // Use service role key for admin rights
);

export default async function handler(req, res) {
  if (req.method !== 'POST') {
    return res.status(405).json({ error: 'Method not allowed' });
  }

  const { team_id, email, role = 'member' } = req.body;

  if (!team_id || !email) {
    return res.status(400).json({ error: 'Missing team_id or email' });
  }

  try {
    // Check if user already exists
    const { data: user, error: userError } = await supabase.auth.admin.getUserByEmail(email);
    if (userError && userError.code !== 'PGRST116') { // 'PGRST116' = user not found
      throw userError;
    }

    let userId = user?.id;

    // Insert into team_members with null user_id if user not found (invited by email)
    const { error: insertError } = await supabase
      .from('team_members')
      .insert([
        {
          team_id,
          user_id: userId || null,
          email, // keep email for invite tracking
          role,
          status: userId ? 'active' : 'pending', // pending until user signs up
        },
      ]);

    if (insertError) {
      throw insertError;
    }

    // TODO: Send invitation email with link to join and accept invite

    return res.status(200).json({ message: 'Invite sent successfully' });
  } catch (error) {
    console.error(error);
    return res.status(500).json({ error: error.message });
  }
}
