import { useState } from 'react';
import { useRouter } from 'next/router';
import { createClient } from '@supabase/supabase-js';
import { useUser } from '@supabase/auth-helpers-react';

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY
);

export default function PostJob() {
  const user = useUser();
  const router = useRouter();

  const [title, setTitle] = useState('');
  const [description, setDescription] = useState('');
  const [errorMsg, setErrorMsg] = useState('');
  const [loading, setLoading] = useState(false);

  // Redirect if not logged in
  if (!user) {
    router.push('/login');
    return null;
  }

  async function handleSubmit(e) {
    e.preventDefault();
    setLoading(true);
    setErrorMsg('');

    // Get profile id of logged-in user (recruiter)
    const { data: profileData, error: profileError } = await supabase
      .from('profiles')
      .select('id')
      .eq('user_id', user.id)
      .single();

    if (profileError) {
      setErrorMsg('Error fetching profile: ' + profileError.message);
      setLoading(false);
      return;
    }

    // Insert job post
    const { error } = await supabase.from('jobs').insert({
      recruiter_id: profileData.id,
      title,
      description,
    });

    if (error) {
      setErrorMsg('Error creating job: ' + error.message);
      setLoading(false);
      return;
    }

    setLoading(false);
    alert('Job posted successfully!');
    router.push('/dashboard'); // redirect to dashboard or jobs list
  }

  return (
    <div style={{ maxWidth: 600, margin: 'auto', padding: 20 }}>
      <h1>Post a New Job</h1>
      <form onSubmit={handleSubmit}>
        <label>
          Job Title<br />
          <input
            type="text"
            value={title}
            onChange={e => setTitle(e.target.value)}
            required
            style={{ width: '100%', padding: 8, marginBottom: 12 }}
          />
        </label>
        <label>
          Job Description<br />
          <textarea
            value={description}
            onChange={e => setDescription(e.target.value)}
            required
            rows={6}
            style={{ width: '100%', padding: 8, marginBottom: 12 }}
          />
        </label>
        {errorMsg && <p style={{ color: 'red' }}>{errorMsg}</p>}
        <button type="submit" disabled={loading}>
          {loading ? 'Posting...' : 'Post Job'}
        </button>
      </form>
    </div>
  );
}
