import { useState, useEffect } from 'react';
import { useRouter } from 'next/router';
import { createClient } from '@supabase/supabase-js';
import { useUser } from '@supabase/auth-helpers-react';

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY
);

export default function ScheduleLiveInterview() {
  const user = useUser();
  const router = useRouter();
  const [jobs, setJobs] = useState([]);
  const [candidates, setCandidates] = useState([]);
  const [jobId, setJobId] = useState('');
  const [candidateId, setCandidateId] = useState('');
  const [scheduledAt, setScheduledAt] = useState('');
  const [error, setError] = useState(null);
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    if (!user) router.push('/login');
    else fetchJobsAndCandidates();
  }, [user]);

  async function fetchJobsAndCandidates() {
    // Fetch recruiter profile id
    const { data: profile } = await supabase
      .from('profiles')
      .select('id')
      .eq('user_id', user.id)
      .single();

    // Fetch jobs by recruiter
    const { data: jobsData } = await supabase
      .from('jobs')
      .select('id, title')
      .eq('recruiter_id', profile.id);

    setJobs(jobsData || []);

    // Fetch candidates who applied to recruiter's jobs (optional: or all candidates)
    // For simplicity, fetching all profiles with role='candidate'
    const { data: candidateData } = await supabase
      .from('profiles')
      .select('id, name, user_id')
      .eq('role', 'candidate');

    setCandidates(candidateData || []);
  }

  async function handleSubmit(e) {
    e.preventDefault();
    setSaving(true);
    setError(null);

    try {
      // Get recruiter profile id again for safety
      const { data: profile, error: profileError } = await supabase
        .from('profiles')
        .select('id')
        .eq('user_id', user.id)
        .single();

      if (profileError) throw profileError;

      const { error } = await supabase.from('live_interviews').insert([
        {
          job_id: jobId,
          recruiter_id: profile.id,
          candidate_id: candidateId,
          scheduled_at: scheduledAt,
        },
      ]);

      if (error) throw error;

      router.push('/recruiter-dashboard');
    } catch (e) {
      setError(e.message);
    } finally {
      setSaving(false);
    }
  }

  if (!user) return null;

  return (
    <div style={{ maxWidth: 600, margin: 'auto', padding: 20 }}>
      <h1>Schedule Live Interview</h1>
      <form onSubmit={handleSubmit}>
        <div>
          <label>
            Select Job:
            <select value={jobId} onChange={(e) => setJobId(e.target.value)} required>
              <option value="">-- Select a Job --</option>
              {jobs.map((job) => (
                <option key={job.id} value={job.id}>
                  {job.title}
                </option>
              ))}
            </select>
          </label>
        </div>

        <div>
          <label>
            Select Candidate:
            <select
              value={candidateId}
              onChange={(e) => setCandidateId(e.target.value)}
              required
            >
              <option value="">-- Select a Candidate --</option>
              {candidates.map((c) => (
                <option key={c.user_id} value={c.user_id}>
                  {c.name}
                </option>
              ))}
            </select>
          </label>
        </div>

        <div>
          <label>
            Scheduled Date & Time:
            <input
              type="datetime-local"
              value={scheduledAt}
              onChange={(e) => setScheduledAt(e.target.value)}
              required
            />
          </label>
        </div>

        {error && <p style={{ color: 'red' }}>{error}</p>}

        <button type="submit" disabled={saving}>
          {saving ? 'Scheduling...' : 'Schedule Interview'}
        </button>
      </form>
    </div>
  );
}
