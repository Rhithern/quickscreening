import { useState, useEffect } from 'react';
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
  const [questions, setQuestions] = useState(['']);
  const [saving, setSaving] = useState(false);
  const [errorMsg, setErrorMsg] = useState(null);

  useEffect(() => {
    if (!user) {
      router.push('/login');
    }
  }, [user, router]);

  const handleQuestionChange = (index, value) => {
    const updated = [...questions];
    updated[index] = value;
    setQuestions(updated);
  };

  const addQuestionField = () => {
    setQuestions([...questions, '']);
  };

  async function handleSubmit(e) {
    e.preventDefault();
    setSaving(true);
    setErrorMsg(null);

    const { data: profile, error: profileError } = await supabase
      .from('profiles')
      .select('id')
      .eq('user_id', user.id)
      .single();

    if (profileError) {
      setErrorMsg('Failed to find your profile.');
      setSaving(false);
      return;
    }

    const cleanQuestions = questions.map((q) => q.trim()).filter((q) => q);

    const { error } = await supabase.from('jobs').insert([
      {
        title,
        description,
        recruiter_id: profile.id,
        questions: cleanQuestions.length ? cleanQuestions : null,
      },
    ]);

    if (error) {
      setErrorMsg('Failed to post job: ' + error.message);
      setSaving(false);
      return;
    }

    router.push('/recruiter-dashboard');
  }

  if (!user) return null;

  return (
    <div style={{ maxWidth: 600, margin: 'auto', padding: 20 }}>
      <h1>Post a New Job</h1>
      <form onSubmit={handleSubmit}>
        <div style={{ marginBottom: 12 }}>
          <label>
            Job Title <br />
            <input
              type="text"
              required
              value={title}
              onChange={(e) => setTitle(e.target.value)}
              style={{ width: '100%', padding: 8 }}
            />
          </label>
        </div>
        <div style={{ marginBottom: 12 }}>
          <label>
            Job Description <br />
            <textarea
              required
              rows={6}
              value={description}
              onChange={(e) => setDescription(e.target.value)}
              style={{ width: '100%', padding: 8 }}
            />
          </label>
        </div>
        <div style={{ marginBottom: 12 }}>
          <label>Interview Questions</label>
          {questions.map((q, i) => (
            <div key={i} style={{ marginBottom: 8 }}>
              <input
                type="text"
                placeholder={`Question ${i + 1}`}
                value={q}
                onChange={(e) => handleQuestionChange(i, e.target.value)}
                style={{ width: '100%', padding: 8 }}
              />
            </div>
          ))}
          <button type="button" onClick={addQuestionField}>
            ➕ Add another question
          </button>
        </div>
        {errorMsg && <p style={{ color: 'red' }}>{errorMsg}</p>}
        <button type="submit" disabled={saving}>
          {saving ? 'Posting...' : 'Post Job'}
        </button>
      </form>
    </div>
  );
}
