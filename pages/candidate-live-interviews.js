import { useEffect, useState } from 'react';
import { useRouter } from 'next/router';
import { createClient } from '@supabase/supabase-js';
import { useUser } from '@supabase/auth-helpers-react';

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY
);

export default function CandidateLiveInterviews() {
  const user = useUser();
  const router = useRouter();
  const [liveInterviews, setLiveInterviews] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!user) {
      router.push('/login');
      return;
    }

    async function fetchInterviews() {
      const { data, error } = await supabase
        .from('live_interviews')
        .select(`
          id,
          scheduled_at,
          status,
          job:jobs(title)
        `)
        .eq('candidate_id', user.id)
        .gte('scheduled_at', new Date().toISOString())  // upcoming only
        .order('scheduled_at', { ascending: true });

      if (error) {
        console.error(error);
      } else {
        setLiveInterviews(data);
      }
      setLoading(false);
    }

    fetchInterviews();
  }, [user, router]);

  if (!user) return null;

  return (
    <div style={{ maxWidth: 800, margin: 'auto', padding: 20 }}>
      <h1>Your Scheduled Live Interviews</h1>
      {loading ? (
        <p>Loading live interviews...</p>
      ) : liveInterviews.length === 0 ? (
        <p>You have no upcoming live interviews scheduled.</p>
      ) : (
        <ul>
          {liveInterviews.map((interview) => (
            <li key={interview.id} style={{ marginBottom: 15 }}>
              <strong>Job:</strong> {interview.job?.title || 'Unknown'} <br />
              <strong>Scheduled At:</strong>{' '}
              {new Date(interview.scheduled_at).toLocaleString()} <br />
              <strong>Status:</strong> {interview.status}
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
