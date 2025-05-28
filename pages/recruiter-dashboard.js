import { useEffect, useState } from 'react';
import { useRouter } from 'next/router';
import { createClient } from '@supabase/supabase-js';
import { useUser } from '@supabase/auth-helpers-react';

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY
);

export default function RecruiterDashboard() {
  const user = useUser();
  const router = useRouter();
  const [jobs, setJobs] = useState([]);
  const [loading, setLoading] = useState(true);
  const [videosByJob, setVideosByJob] = useState({});

  useEffect(() => {
    if (!user) {
      router.push('/login');
      return;
    }

    async function fetchJobsAndVideos() {
      setLoading(true);

      // Get recruiter profile ID
      const { data: profileData, error: profileError } = await supabase
        .from('profiles')
        .select('id')
        .eq('user_id', user.id)
        .single();

      if (profileError) {
        console.error('Profile fetch error:', profileError);
        setLoading(false);
        return;
      }

      // Fetch jobs posted by recruiter
      const { data: jobsData, error: jobsError } = await supabase
        .from('jobs')
        .select('*')
        .eq('recruiter_id', profileData.id);

      if (jobsError) {
        console.error('Jobs fetch error:', jobsError);
        setLoading(false);
        return;
      }

      setJobs(jobsData);

      // For each job, fetch candidate video submissions
      const videosMap = {};

      for (const job of jobsData) {
        const { data: videosData, error: videosError } = await supabase
          .from('videos')
          .select('id, user_id, url, created_at')
          .eq('job_id', job.id);

        if (videosError) {
          console.error(`Videos fetch error for job ${job.id}:`, videosError);
          videosMap[job.id] = [];
        } else {
          videosMap[job.id] = videosData || [];
        }
      }

      setVideosByJob(videosMap);
      setLoading(false);
    }

    fetchJobsAndVideos();
  }, [user, router]);

  if (!user) return null;

  return (
    <div style={{ maxWidth: 900, margin: 'auto', padding: 20 }}>
      <h1>Recruiter Dashboard</h1>

      <a href="/post-job" style={{ display: 'inline-block', marginBottom: 20 }}>
        ➕ Post New Job
      </a>

      {loading ? (
        <p>Loading jobs and videos...</p>
      ) : jobs.length === 0 ? (
        <p>You have not posted any jobs yet.</p>
      ) : (
        jobs.map((job) => (
          <div key={job.id} style={{ marginBottom: 40 }}>
            <h2>{job.title}</h2>
            <p>{job.description}</p>

            <a href={`/job/${job.id}`} target="_blank" rel="noopener noreferrer">
              View job link
            </a>

            <h3 style={{ marginTop: 20 }}>Candidate Video Submissions:</h3>

            {videosByJob[job.id] && videosByJob[job.id].length > 0 ? (
              videosByJob[job.id].map((video) => (
                <div
                  key={video.id}
                  style={{
                    marginBottom: 15,
                    padding: 10,
                    border: '1px solid #ddd',
                    borderRadius: 4,
                  }}
                >
                  <p>
                    Candidate ID: {video.user_id} <br />
                    Submitted: {new Date(video.created_at).toLocaleString()}
                  </p>
                  <video
                    src={video.url}
                    controls
                    width="100%"
                    style={{ maxWidth: 600, borderRadius: 4 }}
                  />
                </div>
              ))
            ) : (
              <p>No submissions yet for this job.</p>
            )}
          </div>
        ))
      )}
    </div>
  );
}
