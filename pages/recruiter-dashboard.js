import { useEffect, useState } from 'react';
import { useRouter } from 'next/router';
import { createClient } from '@supabase/supabase-js';
import { useUser } from '@supabase/auth-helpers-react';

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY
);

export default function RecruiterDashboard() {
  const user = useUser();
  const router = useRouter();
  const [jobs, setJobs] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!user) {
      router.push('/login');
      return;
    }
    async function fetchJobs() {
      const { data: profileData, error: profileError } = await supabase
        .from('profiles')
        .select('id')
        .eq('user_id', user.id)
        .single();

      if (profileError) {
        console.error(profileError);
        setLoading(false);
        return;
      }

      const { data: jobsData, error: jobsError } = await supabase
        .from('jobs')
        .select('*')
        .eq('recruiter_id', profileData.id);

      if (jobsError) {
        console.error(jobsError);
        setLoading(false);
        return;
      }

      setJobs(jobsData);
      setLoading(false);
    }

    fetchJobs();
  }, [user, router]);

  if (!user) return null;

  return (
    <div style={{ maxWidth: 800, margin: 'auto', padding: 20 }}>
      <h1>Recruiter Dashboard</h1>

      <a href="/post-job" style={{ display: 'inline-block', marginBottom: 20 }}>
        ➕ Post New Job
      </a>

      {loading ? (
        <p>Loading jobs...</p>
      ) : jobs.length === 0 ? (
        <p>You have not posted any jobs yet.</p>
      ) : (
        <ul>
          {jobs.map((job) => (
            <li key={job.id} style={{ marginBottom: 15 }}>
              <strong>{job.title}</strong> <br />
              <a
                href={`/job/${job.id}`}
                target="_blank"
                rel="noopener noreferrer"
              >
                View job link
              </a>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
