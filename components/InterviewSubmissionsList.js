import { useEffect, useState } from 'react';
import { createClient } from '@supabase/supabase-js';

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY
);

export default function InterviewSubmissionsList({ recruiterId }) {
  const [submissions, setSubmissions] = useState([]);
  const [loading, setLoading] = useState(true);
  const [jobs, setJobs] = useState([]);
  const [selectedJobId, setSelectedJobId] = useState(null);

  useEffect(() => {
    if (!recruiterId) return;

    const fetchJobs = async () => {
      const { data, error } = await supabase
        .from('jobs')
        .select('id, title')
        .eq('recruiter_id', recruiterId);

      if (error) console.error('Error fetching jobs:', error);
      else setJobs(data);
    };

    fetchJobs();
  }, [recruiterId]);

  useEffect(() => {
    if (!recruiterId) return;

    const fetchSubmissions = async () => {
      setLoading(true);

      let query = supabase
        .from('interview_submissions')
        .select(`
          id,
          question_text,
          question_video_url,
          answer_video_url,
          submitted_at,
          user_id,
          status,
          job:jobs(id, title, recruiter_id)
        `)
        .eq('job.recruiter_id', recruiterId)
        .order('submitted_at', { ascending: false });

      if (selectedJobId) {
        query = query.eq('job.id', selectedJobId);
      }

      const { data, error } = await query;

      if (error) console.error('Error fetching submissions:', error);
      else setSubmissions(data);

      setLoading(false);
    };

    fetchSubmissions();
  }, [recruiterId, selectedJobId]);

  const handleStatusChange = async (submissionId, newStatus) => {
    const { error } = await supabase
      .from('interview_submissions')
      .update({ status: newStatus })
      .eq('id', submissionId);

    if (error) {
      console.error('Error updating status:', error);
      return;
    }

    // Update local state
    setSubmissions((prev) =>
      prev.map((sub) =>
        sub.id === submissionId ? { ...sub, status: newStatus } : sub
      )
    );
  };

  return (
    <div>
      <h2>Candidate Interview Submissions</h2>

      <div style={{ marginBottom: 20 }}>
        <label htmlFor="jobFilter"><strong>Filter by Job:</strong>{' '}</label>
        <select
          id="jobFilter"
          onChange={(e) => setSelectedJobId(e.target.value || null)}
          value={selectedJobId || ''}
        >
          <option value="">All Jobs</option>
          {jobs.map((job) => (
            <option key={job.id} value={job.id}>
              {job.title}
            </option>
          ))}
        </select>
      </div>

      {loading ? (
        <p>Loading submissions...</p>
      ) : submissions.length === 0 ? (
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
              <div>
                <label><strong>Status: </strong></label>
                <select
                  value={sub.status || 'New'}
                  onChange={(e) => handleStatusChange(sub.id, e.target.value)}
                >
                  <option value="New">New</option>
                  <option value="Reviewed">Reviewed</option>
                  <option value="Shortlisted">Shortlisted</option>
                  <option value="Rejected">Rejected</option>
                </select>
              </div>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
