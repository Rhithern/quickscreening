import { useEffect, useState } from 'react';
import { useRouter } from 'next/router';
import { createClient } from '@supabase/supabase-js';

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY
);

export default function JobPage() {
  const router = useRouter();
  const { id } = router.query;

  const [job, setJob] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    if (!id) return;

    async function fetchJob() {
      setLoading(true);
      setError(null);
      try {
        const { data, error } = await supabase
          .from('jobs')
          .select('*')
          .eq('id', id)
          .single();

        if (error) throw error;

        setJob(data);
      } catch (err) {
        setError('Failed to load job details');
        console.error(err);
      } finally {
        setLoading(false);
      }
    }

    fetchJob();
  }, [id]);

  if (loading) return <p>Loading job details...</p>;
  if (error) return <p style={{ color: 'red' }}>{error}</p>;
  if (!job) return <p>Job not found.</p>;

  return (
    <div style={{ maxWidth: 700, margin: 'auto', padding: 20 }}>
      <h1>{job.title}</h1>
      <p>{job.description}</p>

      {job.questions && job.questions.length > 0 && (
        <div>
          <h3>Interview Questions:</h3>
          <ul>
            {job.questions.map((q, i) => (
              <li key={i}>{q}</li>
            ))}
          </ul>
        </div>
      )}

      {job.video_question_url && (
        <div style={{ marginTop: 20 }}>
          <h3>Video Question:</h3>
          <video
            src={job.video_question_url}
            controls
            style={{ width: '100%', maxHeight: 400 }}
          />
        </div>
      )}

      {job.audio_question_url && (
        <div style={{ marginTop: 20 }}>
          <h3>Audio Question:</h3>
          <audio src={job.audio_question_url} controls style={{ width: '100%' }} />
        </div>
      )}

      {/* You can add the "Apply" button here to link to the application page */}
      <div style={{ marginTop: 30 }}>
        <a href={`/apply/${job.id}`} style={{ fontWeight: 'bold', fontSize: 18 }}>
          Apply for this job
        </a>
      </div>
    </div>
  );
}
