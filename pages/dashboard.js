import { useEffect } from 'react';
import { useRouter } from 'next/router';
import { supabase } from '../lib/supabase';

export default function Dashboard() {
  const router = useRouter();

  useEffect(() => {
    const checkRoleAndRedirect = async () => {
      const {
        data: { session },
      } = await supabase.auth.getSession();

      if (!session) {
        router.push('/login');
        return;
      }

      const { data: profile, error } = await supabase
        .from('profiles')
        .select('role')
        .eq('id', session.user.id)
        .single();

      if (error || !profile?.role) {
        console.error('Error fetching profile role:', error?.message);
        return;
      }

      if (profile.role === 'recruiter') {
        router.push('/recruiter');
      } else {
        router.push('/candidate');
      }
    };

    checkRoleAndRedirect();
  }, []);

  return <p>Redirecting...</p>;
}
