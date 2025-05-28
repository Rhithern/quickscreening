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

  useEffect(() => {
    if (id) {
      fetchJob();
    }
  }, [id]);

  async function fetchJob() {
    const { data, error } = await supabase
      .from('jobs')
      .select('*')
      .eq('id', id)
      .single();

    if (error) {
      console.error('Error fetching job:', error.message);
    } else {
      setJob(data);
    }
    setLoading(false);
  }

  if (loading) return <p>Loading job...</p>;
  if (!job) return <p>Job not found</p>;

  return (
    <div style={{ maxWidth: 700, margin: 'auto', padding: 20 }}>
      <h1>{job.title}</h1>
      <p>{job.description}</p>

      {Array.isArray(job.questions) && job.questions.length > 0 && (
        <div style={{ marginTop: 30 }}>
          <h3>Interview Questions</h3>
          <ul>
            {job.questions.map((q, index) => (
              <li key={index}>{q}</li>
            ))}
          </ul>
        </div>
      )}

      <button
        style={{ marginTop: 20 }}
        onClick={() => router.push(`/apply?id=${job.id}`)}
      >
        Apply
      </button>
    </div>
  );
}
