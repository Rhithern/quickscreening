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
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    if (!user) return;

    const loadTeam = async () => {
      setLoading(true);
      setError(null);

      try {
        // Get user profile including team_id
        const { data: profile, error: profileError } = await supabase
          .from('profiles')
          .select('team_id')
          .eq('user_id', user.id)
          .single();

        if (profileError || !profile?.team_id) {
          setError('Could not fetch your team information');
          setLoading(false);
          return;
        }

        // Get all team members in this team
        const { data: teamData, error: teamError } = await supabase
          .from('team_members')
          .select('id, role, profiles(name, email)')
          .eq('team_id', profile.team_id);

        if (teamError) {
          setError('Error fetching team members');
        } else {
          setMembers(teamData);
        }
      } catch (err) {
        setError('Unexpected error');
      }
      setLoading(false);
    };

    loadTeam();
  }, [user]);

  if (loading) return <p>Loading team members...</p>;
  if (error) return <p style={{ color: 'red' }}>{error}</p>;

  return (
    <div>
      <h3>Team Members</h3>
      {members.length === 0 ? (
        <p>No team members found.</p>
      ) : (
        <ul>
          {members.map((member) => (
            <li key={member.id}>
              {member.profiles?.name || 'Unnamed'} - {member.role}
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}

