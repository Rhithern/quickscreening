import { useEffect, useState } from 'react';

export default function CandidateJobsList({ user, supabase }) {
  const [jobs, setJobs] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!user) return;

    const fetchJobs = async () => {
      // Fetch jobs candidate applied to or jobs open for application
      const { data, error } = await supabase
        .from('jobs')
        .select('*')
        .order('created_at', { ascending: false });

      if (error) {
        console.error('Error fetching jobs:', error);
      } else {
        setJobs(data);
      }
      setLoading(false);
    };

    fetchJobs();
  }, [user, supabase]);

  if (loading) return <p>Loading jobs...</p>;

  if (jobs.length === 0) return <p>No available jobs at the moment.</p>;

  return (
    <section style={{ marginBottom: 30 }}>
      <h2>Available Jobs</h2>
      <ul>
        {jobs.map((job) => (
          <li key={job.id} style={{ marginBottom: 10 }}>
            <strong>{job.title}</strong> <br />
            <a href={`/job/${job.id}`} target="_blank" rel="noopener noreferrer">
              View Job Details
            </a>
          </li>
        ))}
      </ul>
    </section>
  );
}
