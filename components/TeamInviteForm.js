import { useState } from 'react';
import { useUser } from '@supabase/auth-helpers-react';

export default function TeamInviteForm() {
  const [email, setEmail] = useState('');
  const [role, setRole] = useState('member');
  const [message, setMessage] = useState('');
  const user = useUser();

  const handleInvite = async (e) => {
    e.preventDefault();

    const res = await fetch('/api/team/invite', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'x-user-id': user.id,
      },
      body: JSON.stringify({ email, role }),
    });

    const data = await res.json();
    if (res.ok) {
      setMessage('Invite sent successfully!');
    } else {
      setMessage(`Error: ${data.error}`);
    }
  };

  return (
    <form onSubmit={handleInvite} style={{ marginBottom: 20 }}>
      <h3>Invite a Team Member</h3>
      <input
        type="email"
        placeholder="Team member email"
        value={email}
        onChange={(e) => setEmail(e.target.value)}
        required
      />
      <select value={role} onChange={(e) => setRole(e.target.value)}>
        <option value="member">Member</option>
        <option value="admin">Admin</option>
      </select>
      <button type="submit">Send Invite</button>
      {message && <p>{message}</p>}
    </form>
  );
}

