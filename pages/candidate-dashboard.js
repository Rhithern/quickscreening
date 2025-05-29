import { useUser } from '@supabase/auth-helpers-react';
import { useRouter } from 'next/router';
import InterviewSubmission from '../components/InterviewSubmission';

export default function CandidateDashboard() {
  const user = useUser();
  const router = useRouter();

  // Redirect to login if not logged in
  if (!user) {
    router.push('/login');
    return null;
  }

  const sampleQuestion = {
    text: "Tell us about a challenging project you worked on.",
    videoUrl: null,
  };

  return (
    <div style={{ maxWidth: 700, margin: 'auto', padding: 20 }}>
      <h1>Candidate Dashboard</h1>

      {/* Other candidate dashboard components here */}

      <section style={{ marginTop: 40 }}>
        <h2>One-Way Interview</h2>
        <InterviewSubmission question={sampleQuestion} />
      </section>
    </div>
  );
}
