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

  useEffect(() => {
    if (!user) {
      router.push('/login');
    }
  }, [user, router]);

  if (!user) return null;

  return (
    <div style={{ maxWidth: 800, margin: 'auto', padding: 20 }}>
      <h1>Candidate Dashboard</h1>

      <CandidateProfile user={user} supabase={supabase} />

      <CandidateJobsList user={user} supabase={supabase} />

      <InterviewSubmission user={user} supabase={supabase} />

      <ScheduledLiveInterviews user={user} supabase={supabase} />

      <Notifications user={user} supabase={supabase} />
    </div>
  );
}
