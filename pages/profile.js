import { useState, useEffect } from 'react';
import { useRouter } from 'next/router';
import { createClient } from '@supabase/supabase-js';

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY
);

export default function ProfilePage() {
  const router = useRouter();
  const [session, setSession] = useState(null);
  const [loading, setLoading] = useState(true);
  const [profile, setProfile] = useState({
    full_name: '',
    email: '',
    role: ''
  });

  useEffect(() => {
    const getSession = async () => {
      const { data: { session } } = await supabase.auth.getSession();
      if (!session) {
        router.push('/login');
      } else {
        setSession(session);
        fetchProfile(session.user.id);
      }
    };

    getSession();
  }, []);

  const fetchProfile = async (userId) => {
    const { data, error } = await supabase
      .from('profiles')
      .select('*')
      .eq('id', userId)
      .single();

    if (data) {
      setProfile({
        full_name: data.full_name || '',
        email: data.email || '',
        role: data.role || ''
      });
    }
    setLoading(false);
  };

  const handleChange = (e) => {
    setProfile({ ...profile, [e.target.name]: e.target.value });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    const updates = {
      id: session.user.id,
      ...profile,
      updated_at: new Date()
    };

    const { error } = await supabase
      .from('profiles')
      .upsert(updates);

    if (error) {
      alert('Error saving profile');
      console.error(error);
    } else {
      alert('Profile saved!');
      router.push('/dashboard'); // redirect to dashboard
    }
  };

  if (loading) return <p>Loading...</p>;

  return (
    <div style={{ padding: '2rem' }}>
      <h1>Edit Profile</h1>
      <form onSubmit={handleSubmit}>
        <label>Full Name:</label>
        <input type="text" name="full_name" value={profile.full_name} onChange={handleChange} required />
        <br />
        <label>Email:</label>
        <input type="email" name="email" value={profile.email} onChange={handleChange} required />
        <br />
        <label>Role:</label>
        <select name="role" value={profile.role} onChange={handleChange} required>
          <option value="">Select role</option>
          <option value="recruiter">Recruiter</option>
          <option value="candidate">Candidate</option>
        </select>
        <br /><br />
        <button type="submit">Save Profile</button>
      </form>
    </div>
  );
}
