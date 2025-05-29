import { useEffect, useState } from 'react';
import { createClient } from '@supabase/supabase-js';
import { useUser } from '@supabase/auth-helpers-react';

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY
);

export default function TeamMembersManagement() {
  const user = useUser();
  const [members, setMembers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    if (!user) return;

    async function loadMembers() {
      setLoading(true);
      const { data: profile } = await supabase
        .from('profiles')
        .select('team_id')
        .eq('user_id', user.id)
        .single();

      if (!profile?.team_id) {
        setError('No team found for current user');
        setLoading(false);
        return;
      }

      const { data: teamMembers, error } = await supabase
        .from('team_members')
        .select('id, user_id, role, profiles(name, email)')
        .eq('team_id', profile.team_id);

      if (error) {
        setError('Failed to load team members');
      } else {
        setMembers(teamMembers);
      }
      setLoading(false);
    }

    loadMembers();
  }, [user]);

  async function updateRole(memberId, newRole) {
    const res = await fetch(`/api/team/members/${memberId}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ role: newRole }),
    });

    if (res.ok) {
      setMembers((prev) =>
        prev.map((m) => (m.id === memberId ? { ...m, role: newRole } : m))
      );
    } else {
      alert('Failed to update role');
    }
  }

  async function removeMember(memberId) {
    if (!confirm('Are you sure you want to remove this member?')) return;

    const res = await fetch(`/api/team/members/${memberId}`, {
      method: 'DELETE',
    });

    if (res.ok) {
      setMembers((prev) => prev.filter((m) => m.id !== memberId));
    } else {
      alert('Failed to remove member');
    }
  }

  if (loading) return <p>Loading team members...</p>;
  if (error) return <p style={{ color: 'red' }}>{error}</p>;

  return (
    <div>
      <h3>Manage Team Members</h3>
      <ul>
        {members.map((member) => (
          <li key={member.id} style={{ marginBottom: 10 }}>
            <strong>{member.profiles?.name || 'Unnamed'}</strong> - {member.profiles?.email}
            <br />
            Role:{' '}
            <select
              value={member.role}
              onChange={(e) => updateRole(member.id, e.target.value)}
              style={{ marginRight: 10 }}
            >
              <option value="viewer">Viewer</option>
              <option value="editor">Editor</option>
              <option value="admin">Admin</option>
            </select>
            <button onClick={() => removeMember(member.id)}>Remove</button>
          </li>
        ))}
      </ul>
    </div>
  );
}
