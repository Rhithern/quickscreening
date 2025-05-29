import { useEffect, useState } from 'react';
import { useRouter } from 'next/router';
import { createClient } from '@supabase/supabase-js';
import { useUser } from '@supabase/auth-helpers-react';

import CandidateProfile from '../components/CandidateProfile';
import CandidateJobsList from '../components/CandidateJobsList';
import InterviewSubmission from '../components/InterviewSubmission';
import ScheduledLiveInterviews from '../components/ScheduledLiveInterviews';
import Notifications from '../components/Notifications';

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY
);

export default function CandidateDashboard() {
  const user = useUser();
  const router = useRouter();

  const [profile, setProfile] = useState(null);
  const [loadingProfile, setLoadingProfile] = useState(true);

  useEffect(() => {
    if (!user) {
      router.push('/login');
      return;
    }

    async function fetchProfile() {
      const { data, error } = await supabase
        .from('profiles')
        .select('*')
        .eq('user_id', user.id)
        .single();

      if (error) {
        console.error('Error fetching profile:', error);
      } else {
        setProfile(data);
      }
      setLoadingProfile(false);
    }

    fetchProfile();
  }, [user, router]);

  if (!user) return null;

  // Sample question to pass to InterviewSubmission
  const sampleQuestion = {
    text: 'Please introduce yourself and explain why you are a good fit for this job.',
    videoUrl: null, // or add a video URL if available
  };

  return (
    <div style={{ maxWidth: 800, margin: 'auto', padding: 20 }}>
      <h1>Candidate Dashboard</h1>

      <section style={{ marginBottom: 30 }}>
        <h2>Your Profile</h2>
        {loadingProfile ? (
          <p>Loading profile...</p>
        ) : profile ? (
          <CandidateProfile profile={profile} />
        ) : (
          <p>No profile data found.</p>
        )}
      </section>

      <section style={{ marginBottom: 30 }}>
        <h2>Jobs</h2>
        <CandidateJobsList userId={user.id} />
      </section>

      <section style={{ marginBottom: 30 }}>
        <h2>Interview Submission</h2>
        <InterviewSubmission question={sampleQuestion} />
      </section>

      <section style={{ marginBottom: 30 }}>
        <h2>Scheduled Live Interviews</h2>
        <ScheduledLiveInterviews userId={user.id} />
      </section>

      <section>
        <h2>Notifications</h2>
        <Notifications userId={user.id} />
      </section>
    </div>
  );
}
