import { useEffect, useState } from 'react';

export default function Notifications({ user, supabase }) {
  const [notifications, setNotifications] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!user) return;

    const fetchNotifications = async () => {
      const { data, error } = await supabase
        .from('notifications')
        .select('*')
        .eq('user_id', user.id)
        .order('created_at', { ascending: false })
        .limit(10);

      if (error) {
        console.error('Error fetching notifications:', error);
      } else {
        setNotifications(data);
      }
      setLoading(false);
    };

    fetchNotifications();
  }, [user, supabase]);

  if (loading) return <p>Loading notifications...</p>;

  if (notifications.length === 0) return <p>No notifications.</p>;

  return (
    <section style
