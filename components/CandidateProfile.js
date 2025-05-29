import { useState, useEffect } from 'react';

export default function CandidateProfile({ user, supabase }) {
  const [profile, setProfile] = useState({ name: '', email: '' });
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!user) return;

    const fetchProfile = async () => {
      const { data, error } = await supabase
        .from('profiles')
        .select('name, email')
        .eq('user_id', user.id)
        .single();

      if (error) {
        console.error('Error fetching profile:', error);
      } else {
        setProfile(data);
      }
      setLoading(false);
    };

    fetchProfile();
  }, [user, supabase]);

  const handleChange = (e) => {
    setProfile({ ...profile, [e.target.name]: e.target.value });
  };

  const handleSave = async () => {
    setLoading(true);
    const { error } = await supabase
      .from('profiles')
      .update({ name: profile.name, email: profile.email })
      .eq('user_id', user.id);

    if (error) {
      console.error('Error updating profile:', error);
    }
    setLoading(false);
  };

  if (loading) return <p>Loading profile...</p>;

  return (
    <section style={{ marginBottom: 30 }}>
      <h2>Your Profile</h2>
      <label>
        Name: <br />
        <input type="text" name="name" value={profile.name} onChange={handleChange} />
      </label>
      <br />
      <label>
        Email: <br />
        <input type="email" name="email" value={profile.email} onChange={handleChange} />
      </label>
      <br />
      <button onClick={handleSave}>Save Profile</button>
    </section>
  );
}
