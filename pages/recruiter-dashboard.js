import { useEffect, useState } from 'react';
import { useRouter } from 'next/router';
import { createClient } from '@supabase/supabase-js';
import { useUser } from '@supabase/auth-helpers-react';
import TeamInviteForm from '../components/TeamInviteForm';
import TeamMembersList from '../components/TeamMembersList';  // You can keep this if you want to show it still
import TeamMembersManagement from '../components/TeamMembersManagement';  // NEW import

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY
);

export default function RecruiterDashboard() {
  const user = useUser();
  const router = useRouter();

  const [jobs, setJobs] = useState([]);
  const [loadingJobs, setLoadingJobs] = useState(true);
  const [liveInterviews, setLiveInterviews] = useState([]);
  const [loadingInterviews, setLoadingInterviews] = useState(true);

  useEffect(() => {
    if (!user) {
      router.push('/login');
      return;
    }

    async function fetchData() {
      // Get recruiter profile id
      const { data: profileData, error: profileError } = await supabase
        .from('profiles')
        .select('id')
        .eq('user_id', user.id)
        .single();

      if (profileError) {
        console.error(profileError);
        setLoadingJobs(false);
        setLoadingInterviews(false);
        return;
      }

      // Fetch jobs
      const { data: jobsData, error: jobsError } = await supabase
        .from('jobs')
        .select('*')
        .eq('recruiter_id', profileData.id);

      if (jobsError) {
        console.error(jobsError);
      } else {
        setJobs(jobsData);
      }
      setLoadingJobs(false);

      // Fetch upcoming live interviews (only future dates)
      const { data: interviewsData, error: interviewsError } = await supabase
        .from('live_interviews')
        .select(`
          id,
          scheduled_at,
          status,
          candidate_id,
          job:jobs(title)
        `)
        .eq('recruiter_id', profileData.id)
        .gte('scheduled_at', new Date().toISOString())  // only upcoming
        .order('scheduled_at', { ascending: true });

      if (interviewsError) {
        console.error(interviewsError);
      } else {
        setLiveInterviews(interviewsData);
      }
      setLoadingInterviews(false);
    }

    fetchData();
  }, [user, router]);

  if (!user) return null;

  return (
    <div style={{ maxWidth: 800, margin: 'auto', padding: 20 }}>
      <h1>Recruiter Dashboard</h1>

      <nav style={{ marginBottom: 20 }}>
        <a href="/post-job" style={{ marginRight: 15 }}>
          ➕ Post New Job
        </a>
        <a href="/schedule-live-interview" style={{ marginRight: 15 }}>
          📅 Schedule Live Interview
        </a>
      </nav>

      <section style={{ marginBottom: 30 }}>
        <h2>Your Jobs</h2>
        {loadingJobs ? (
          <p>Loading jobs...</p>
        ) : jobs.length === 0 ? (
          <p>You have not posted any jobs yet.</p>
        ) : (
          <ul>
            {jobs.map((job) => (
              <li key={job.id} style={{ marginBottom: 15 }}>
                <strong>{job.title}</strong> <br />
                <a href={`/job/${job.id}`} target="_blank" rel="noopener noreferrer">
                  View job link
                </a>
              </li>
            ))}
          </ul>
        )}
      </section>

      <section style={{ marginBottom: 30 }}>
        <h2>Upcoming Live Interviews</h2>
        {loadingInterviews ? (
          <p>Loading live interviews...</p>
        ) : liveInterviews.length === 0 ? (
          <p>No upcoming live interviews scheduled.</p>
        ) : (
          <ul>
            {liveInterviews.map((interview) => (
              <li key={interview.id} style={{ marginBottom: 15 }}>
                <strong>Job:</strong> {interview.job?.title || 'Unknown'} <br />
                <strong>Candidate ID:</strong> {interview.candidate_id} <br />
                <strong>Scheduled At:</strong>{' '}
                {new Date(interview.scheduled_at).toLocaleString()} <br />
                <strong>Status:</strong> {interview.status}
              </li>
            ))}
          </ul>
        )}
      </section>

      {/* Existing Team Invite Form */}
      <section style={{ marginBottom: 30 }}>
        <h2>Invite Team Members</h2>
        <TeamInviteForm />
      </section>

      {/* Optional: TeamMembersList if you want to keep */}
      <section style={{ marginBottom: 30 }}>
        <TeamMembersList />
      </section>

      {/* NEW: Team Members Management Section */}
      <section style={{ marginBottom: 30 }}>
        <h2>Manage Team Members</h2>
        <TeamMembersManagement />
      </section>
    </div>
  );
}
