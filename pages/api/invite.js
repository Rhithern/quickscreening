// /pages/api/invite.js
import { createClient } from '@supabase/supabase-js';
import { v4 as uuidv4 } from 'uuid';

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY
);

export default async function handler(req, res) {
  if (req.method !== 'POST') return res.status(405).end();

  const { email, team_id, invited_by } = req.body;

  const token = uuidv4();

  const { error } = await supabase.from('team_invites').insert([
    { email, team_id, invited_by, token }
  ]);

  if (error) return res.status(500).json({ error: error.message });

  // (Optional) Send email with token here using external email service

  res.status(200).json({ message: 'Invite sent', token });
}
