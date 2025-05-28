import { useRouter } from 'next/router';
import { useEffect, useState } from 'react';
import { createClient } from '@supabase/supabase-js';

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY
);

export default function JobDetail() {
  const router = useRouter();
  const { id } = router.query;

  const [job, setJob] = useState(null);
  const [loading, setLoading] = useState(true);
  const [errorMsg, setErrorMsg] = useState(null);

  useEffect(() => {
    if (!id) return;

    async function fetchJob() {
      setLoading(true);
      const { data, error } = await supabase
        .from('jobs')
        .select('*')
        .eq('id', id)
        .single();

      if (error) {
        setErrorMsg('Failed to load job.');
      } else {
        setJob(data);
      }
      setLoading(false);
    }

    fetchJob();
  }, [id]);

  if (loading) return <p>Loading job details...</p>;

  if (errorMsg) return <p style={{ color: 'red' }}>{errorMsg}</p>;

  if (!job) return <p>Job not found.</p>;

  return (
    <div style={{ maxWidth: 700, margin: 'auto', padding: 20 }}>
      <h1>{job.title}</h1>
      <p>{job.description}</p>

      {/* Future: Add Apply or Record Video button here */}

    </div>
  );
}
