import { useEffect, useState } from 'react';
import { createClient } from '@supabase/supabase-js';

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY
);

export default function CandidateSubmissionHistory({ candidateId }) {
  const [submissions, setSubmissions] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!candidateId) return;

    const fetchSubmissions = async () => {
      const { data, error } = await supabase
        .from('interview_submissions')
        .select(`
          id,
          question_text,
          answer_video_url,
          status,
          submitted_at,
          job:jobs(title)
        `)
        .eq('user_id', candidateId)
        .order('submitted_at', { ascending: false });

      if (error) console.error(error);
      else setSubmissions(data);

      setLoading(false);
    };

    fetchSubmissions();
  }, [candidateId]);

  if (loading) return <p>Loading candidate history...</p>;

  return (
    <div>
      <h3>Submission History for Candidate</h3>
      {submissions.length === 0 ? (
        <p>No submissions found for this candidate.</p>
      ) : (
        <ul>
          {submissions.map((sub) => (
            <li key={sub.id} style={{ marginBottom: '20px' }}>
              <strong>Job:</strong> {sub.job?.title || 'N/A'} <br />
              <strong>Question:</strong> {sub.question_text} <br />
              <strong>Status:</strong> {sub.status || 'Pending'} <br />
              <video src={sub.answer_video_url} controls width="300" />
              <p><em>Submitted: {new Date(sub.submitted_at).toLocaleString()}</em></p>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
