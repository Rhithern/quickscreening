import { useEffect, useState } from 'react';
import { createClient } from '@supabase/supabase-js';
import CandidateSubmissionHistory from './CandidateSubmissionHistory';

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY
);

export default function InterviewSubmissionsList({ recruiterId }) {
  const [submissions, setSubmissions] = useState([]);
  const [loading, setLoading] = useState(true);
  const [selectedCandidateId, setSelectedCandidateId] = useState(null);

  useEffect(() => {
    if (!recruiterId) return;

    const fetchSubmissions = async () => {
      const { data, error } = await supabase
        .from('interview_submissions')
        .select(`
          id,
          question_text,
          question_video_url,
          answer_video_url,
          submitted_at,
          user_id,
          job:jobs(title, recruiter_id)
        `)
        .eq('job.recruiter_id', recruiterId)
        .order('submitted_at', { ascending: false });

      if (error) console.error(error);
      else setSubmissions(data);

      setLoading(false);
    };

    fetchSubmissions();
  }, [recruiterId]);

  if (loading) return <p>Loading submissions...</p>;

  return (
    <div>
      <h2>Candidate Interview Submissions</h2>
      {submissions.length === 0 ? (
        <p>No submissions found.</p>
      ) : (
        <ul>
          {submissions.map((sub) => (
            <li key={sub.id} style={{ marginBottom: '20px' }}>
              <strong>Job:</strong> {sub.job?.title || 'N/A'} <br />
              <strong>Question:</strong> {sub.question_text} <br />
              {sub.question_video_url && (
                <video src={sub.question_video_url} controls width="300" />
              )}
              <br />
              <strong>Answer:</strong> <br />
              <video src={sub.answer_video_url} controls width="300" />
              <p><em>Submitted: {new Date(sub.submitted_at).toLocaleString()}</em></p>

              <button
                onClick={() =>
                  setSelectedCandidateId(
                    selectedCandidateId === sub.user_id ? null : sub.user_id
                  )
                }
                style={{ marginTop: 10 }}
              >
                {selectedCandidateId === sub.user_id
                  ? 'Hide Candidate History'
                  : 'View Candidate History'}
              </button>

              {/* Show Candidate Submission History if selected */}
              {selectedCandidateId === sub.user_id && (
                <div style={{ marginTop: 20, paddingLeft: 20, borderLeft: '2px solid #ccc' }}>
                  <CandidateSubmissionHistory candidateId={sub.user_id} />
                </div>
              )}
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
