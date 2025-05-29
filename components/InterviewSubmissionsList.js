import { useEffect, useState } from 'react';
import { createClient } from '@supabase/supabase-js';

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY
);

const STATUS_OPTIONS = ['Pending', 'Reviewed', 'Shortlisted', 'Rejected'];

export default function InterviewSubmissionsList({ recruiterId }) {
  const [submissions, setSubmissions] = useState([]);
  const [jobs, setJobs] = useState([]);
  const [selectedJobId, setSelectedJobId] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!recruiterId) return;

    const fetchJobsAndSubmissions = async () => {
      setLoading(true);

      const { data: jobsData, error: jobsError } = await supabase
        .from('jobs')
        .select('id, title')
        .eq('recruiter_id', recruiterId);

      if (jobsError) {
        console.error(jobsError);
        return;
      }
      setJobs(jobsData);

      await fetchSubmissions(null);
    };

    fetchJobsAndSubmissions();
  }, [recruiterId]);

  const fetchSubmissions = async (jobId = null) => {
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
      .order('submitted_at', { ascending: false });

    if (jobId) {
      query = query.eq('job.id', jobId);
    } else {
      query = query.eq('job.recruiter_id', recruiterId);
    }

    const { data, error } = await query;

    if (error) console.error(error);
    else setSubmissions(data);

    setLoading(false);
  };

  const handleJobChange = (e) => {
    const jobId = e.target.value || null;
    setSelectedJobId(jobId);
    fetchSubmissions(jobId);
  };

  const updateStatus = async (submissionId, newStatus) => {
    const { error } = await supabase
      .from('interview_submissions')
      .update({ status: newStatus })
      .eq('id', submissionId);

    if (error) {
      console.error('Failed to update status:', error);
    } else {
      setSubmissions((prev) =>
        prev.map((sub) =>
          sub.id === submissionId ? { ...sub, status: newStatus } : sub
        )
      );
    }
  };

  return (
    <div>
      <h2>Candidate Interview Submissions</h2>

      <label>
        Filter by Job:{' '}
        <select onChange={handleJobChange} value={selectedJobId || ''}>
          <option value="">All Jobs</option>
          {jobs.map((job) => (
            <option key={job.id} value={job.id}>
              {job.title}
            </option>
          ))}
        </select>
      </label>

      {loading ? (
        <p>Loading submissions...</p>
      ) : submissions.length === 0 ? (
        <p>No submissions found.</p>
      ) : (
        <ul>
          {submissions.map((sub) => (
            <li key={sub.id} style={{ marginBottom: '30px', borderBottom: '1px solid #ccc', paddingBottom: '20px' }}>
              <strong>Job:</strong> {sub.job?.title || 'N/A'} <br />
              <strong>Question:</strong> {sub.question_text} <br />
              {sub.question_video_url && (
                <video src={sub.question_video_url} controls width="300" />
              )}
              <br />
              <strong>Answer:</strong> <br />
              <video src={sub.answer_video_url} controls width="300" />
              <p><em>Submitted: {new Date(sub.submitted_at).toLocaleString()}</em></p>

              <label>
                Status:{' '}
                <select
                  value={sub.status || 'Pending'}
                  onChange={(e) => updateStatus(sub.id, e.target.value)}
                >
                  {STATUS_OPTIONS.map((status) => (
                    <option key={status} value={status}>
                      {status}
                    </option>
                  ))}
                </select>
              </label>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
