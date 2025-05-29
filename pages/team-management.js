import { useEffect, useState } from 'react';
import { useRouter } from 'next/router';
import { createClient } from '@supabase/supabase-js';
import TeamInviteForm from '@/components/TeamInviteForm';

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY
);

export default function TeamManagementPage() {
  const router = useRouter();
  const [user, setUser] = useState(null);
  const [teamId, setTeamId] = useState(null);

  useEffect(() => {
    const fetchData = async () => {
      const {
        data: { user },
      } = await supabase.auth.getUser();

      if (!user) {
        router.push('/login');
        return;
      }

      setUser(user);

      const { data: profile } = await supabase
        .from('profiles')
        .select('team_id')
        .eq('user_id', user.id)
        .single();

      if (profile?.team_id) {
        setTeamId(profile.team_id);
      }
    };

    fetchData();
  }, []);

  if (!user) return <p>Loading...</p>;
  if (!teamId) return <p>🔒 You are not part of a team.</p>;

  return (
    <div style={{ padding: 20 }}>
      <h1>Team Management</h1>
      <TeamInviteForm teamId={teamId} />
    </div>
  );
}
