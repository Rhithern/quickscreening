import { useRouter } from 'next/router';
import { useEffect, useState } from 'react';
import { createClient } from '@supabase/supabase-js';
import { useUser } from '@supabase/auth-helpers-react';

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY
);

export default function JobDetail() {
  const router = useRouter();
  const user = useUser();
  const { id } = router.query;

  const [job, setJob] = useState(null);
  const [profile, setProfile] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!id) return;

    async function fetchJobAndProfile() {
      // Fetch job
      const { data: jobData, error: jobError } = await supabase
        .from('jobs')
        .select('*')
        .eq('id', id)
        .single();

      if (jobError) {
        console.error('Error fetching job:', jobError.message);
        return;
      }
      setJob(jobData);

      // Fetch profile if user is logged in
      if (user) {
        const { data: profileData, error: profileError } = await supabase
          .from('profiles')
          .select('*')
          .eq('user_id', user.id)
          .single();

        if (!profileError) {
          setProfile(profileData);
        }
      }

      setLoading(false);
    }

    fetchJobAndProfile();
  }, [id, user]);

  if (loading) return <p>Loading...</p>;
  if (!job) return <p>Job not found.</p>;

  return (
    <div style={{ maxWidth: 600, margin: 'auto', padding: 20 }}>
      <h1>{job.title}</h1>
      <p>{job.description}</p>

      {/* Show apply button if candidate is logged in */}
      {profile?.role === 'candidate' && (
        <button
          onClick={() => router.push(`/apply?job_id=${job.id}`)}
          style={{ marginTop: 20 }}
        >
          🎥 Apply with Video
        </button>
      )}

      {/* Prompt login if not logged in */}
      {!user && (
        <p>
          <a href="/login">Log in</a> to apply for this job.
        </p>
      )}
    </div>
  );
}
