import { useEffect, useState } from 'react';
import { createClient } from '@supabase/supabase-js';
import { useUser } from '@supabase/auth-helpers-react';

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY
);

export default function TeamMembersList() {
  const user = useUser();
  const [members, setMembers] = useState([]);

  useEffect(() => {
    if (!user) return;

    const loadTeam = async () => {
      // Get profile to access team_id
      const { data: profile, error: profileError } = await supabase
        .from('profiles')
        .select('team_id')
        .eq('user_id', user.id)
        .single();

      if (profileError || !profile) {
        console.error('Error loading profile:', profileError);
        return;
      }

      // Fetch team members for this team_id
      const { data: teamData, error: teamError } = await supabase
        .from('team_members')
        .select('id, role, profiles(name, email)')
        .eq('team_id', profile.team_id);

      if (teamError) {
        console.error('Error loading team members:', teamError);
        return;
      }

      setMembers(teamData);
    };

    loadTeam();
  }, [user]);

  return (
    <div style={{ marginTop: 30 }}>
      <h2>Team Members</h2>
      <ul>
        {members.map((member) => (
          <li key={member.id}>
            <strong>{member.profiles?.name || 'Unnamed'}</strong> – {member.role}
          </li>
        ))}
      </ul>
    </div>
  );
}
