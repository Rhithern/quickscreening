import { useEffect, useState } from 'react';
import { useRouter } from 'next/router';
import Link from 'next/link';  // <--- add this
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

      {/* Use Link for client-side navigation */}
      <Link href="/post-job" passHref>
        <a style={{ display: 'inline-block', marginBottom: 20, textDecoration: 'none', color: 'blue' }}>
          ➕ Post New Job
        </a>
      </Link>

      {loading ? (
        <p>Loading jobs...</p>
      ) : jobs.length === 0 ? (
        <p>You have not posted any jobs yet.</p>
      ) : (
        <ul>
          {jobs.map((job) => (
            <li key={job.id} style={{ marginBottom: 15 }}>
              <strong>{job.title}</strong> <br />
              <Link href={`/job/${job.id}`} passHref>
                <a target="_blank" rel="noopener noreferrer" style={{ color: 'blue' }}>
                  View job link
                </a>
              </Link>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
