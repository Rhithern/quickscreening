import { useEffect, useState } from 'react';

export default function ScheduledLiveInterviews({ user, supabase }) {
  const [interviews, setInterviews] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!user) return;

    const fetchInterviews = async () => {
      const { data, error } = await supabase
        .from('live_interviews')
        .select('id, scheduled_at, status, job:jobs(title)')
        .eq('candidate_id', user.id)
        .gte('scheduled_at', new Date().toISOString())
        .order('scheduled_at', { ascending: true });

      if (error) {
        console.error('Error fetching live interviews:', error);
      } else {
        setInterviews(data);
      }
      setLoading(false);
    };

    fetchInterviews();
  }, [user, supabase]);

  if (loading) return <p>Loading live interviews...</p>;

  if (interviews.length === 0) return <p>No upcoming live interviews.</p>;

  return (
    <section style={{ marginBottom: 30 }}>
      <h2>Upcoming Live Interviews</h2>
      <ul>
        {interviews.map((interview) => (
          <li key={interview.id} style={{ marginBottom: 10 }}>
            <strong>Job:</strong> {interview.job?.title || 'Unknown'} <br />
            <strong>Scheduled At:</strong> {new Date(interview.scheduled_at).toLocaleString()} <br />
            <strong>Status:</strong> {interview.status}
          </li>
        ))}
      </ul>
    </section>
  );
}
