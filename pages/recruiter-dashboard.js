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
  const [videosByJob, setVideosByJob] = useState({});
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!user) {
      router.push('/login');
      return;
    }

    async function fetchJobs() {
      setLoading(true);

      // Get recruiter profile id
      const { data: profileData, error: profileError } = await supabase
        .from('profiles')
        .select('id')
        .eq('user_id', user.id)
        .single();

      if (profileError) {
        console.error('Profile error:', profileError);
        setLoading(false);
        return;
      }

      // Fetch jobs posted by this recruiter
      const { data: jobsData, error: jobsError } = await supabase
        .from('jobs')
        .select('*')
        .eq('recruiter_id', profileData.id);

      if (jobsError) {
        console.error('Jobs error:', jobsError);
        setLoading(false);
        return;
      }

      setJobs(jobsData);
      setLoading(false);

      if (jobsData.length > 0) {
        // Fetch candidate video submissions for these jobs
        const jobIds = jobsData.map((job) => job.id);

        const { data: videosData, error: videosError } = await supabase
          .from('videos')
          .select('id, video_url, user_id, job_id, created_at, profiles(full_name, email)')
          .in('job_id', jobIds);

        if (videosError) {
          console.error('Videos error:', videosError);
          return;
        }

        // Group videos by job_id
        const grouped = {};
        videosData.forEach((video) => {
          if (!grouped[video.job_id]) grouped[video.job_id] = [];
          grouped[video.job_id].push(video);
        });

        setVideosByJob(grouped);
      }
    }

    fetchJobs();
  }, [user, router]);

  if (!user) return null;

  return (
    <div style={{ maxWidth: 800, margin: 'auto', padding: 20 }}>
      <h1>Recruiter Dashboard</h1>

      <a href="/post-job" style={{ display: 'inline-block', marginBottom: 20 }}>
        ➕ Post New Job
      </a>

      {loading ? (
        <p>Loading jobs...</p>
      ) : jobs.length === 0 ? (
        <p>You have not posted any jobs yet.</p>
      ) : (
        <ul style={{ paddingLeft: 0 }}>
          {jobs.map((job) => (
            <li key={job.id} style={{ marginBottom: 30, listStyle: 'none' }}>
              <strong>{job.title}</strong> <br />
              <a href={`/job/${job.id}`} target="_blank" rel="noopener noreferrer">
                View job link
              </a>

              <div style={{ marginTop: 10 }}>
                <h4>Candidate Submissions:</h4>
                {videosByJob[job.id]?.length > 0 ? (
                  <ul style={{ listStyle: 'none', paddingLeft: 0 }}>
                    {videosByJob[job.id].map((video) => (
                      <li key={video.id} style={{ marginBottom: 15 }}>
                        <p>
                          <strong>{video.profiles?.full_name || 'Unknown Candidate'}</strong> ({video.profiles?.email || 'No email'})
                        </p>
                        <video
                          src={video.video_url}
                          controls
                          style={{ width: '100%', maxHeight: 200 }}
                        />
                        <p style={{ fontSize: '0.8em', color: '#666' }}>
                          Submitted on: {new Date(video.created_at).toLocaleString()}
                        </p>
                      </li>
                    ))}
                  </ul>
                ) : (
                  <p>No submissions yet.</p>
                )}
              </div>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
