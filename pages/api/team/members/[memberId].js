import { createClient } from '@supabase/supabase-js';

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY
);

export default async function handler(req, res) {
  const {
    query: { memberId },
    method,
  } = req;

  if (!memberId) {
    return res.status(400).json({ error: 'Missing member ID' });
  }

  if (method === 'PUT') {
    // Update member role
    const { role } = req.body;
    if (!role) {
      return res.status(400).json({ error: 'Missing role in request body' });
    }

    const { error } = await supabase
      .from('team_members')
      .update({ role })
      .eq('id', memberId);

    if (error) {
      return res.status(500).json({ error: 'Failed to update role' });
    }

    return res.status(200).json({ message: 'Role updated' });
  }

  if (method === 'DELETE') {
    // Remove member
    const { error } = await supabase
      .from('team_members')
      .delete()
      .eq('id', memberId);

    if (error) {
      return res.status(500).json({ error: 'Failed to remove member' });
    }

    return res.status(200).json({ message: 'Member removed' });
  }

  res.setHeader('Allow', ['PUT', 'DELETE']);
  return res.status(405).json({ error: `Method ${method} Not Allowed` });
}
