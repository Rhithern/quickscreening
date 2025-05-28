import Link from 'next/link';
import { useRouter } from 'next/router';
import { useEffect } from 'react';
import { supabase } from '../lib/supabase';

export default function Dashboard() {
  const router = useRouter();

  useEffect(() => {
    const checkAuth = async () => {
      const {
        data: { session },
      } = await supabase.auth.getSession();

      if (!session) {
        router.push('/login');
      }
    };

    checkAuth();
  }, []);

  return (
    <div className="p-4">
      <nav className="flex gap-4 mb-4">
        <Link href="/dashboard" className="text-blue-500 hover:underline">Dashboard</Link>
        <Link href="/profile" className="text-blue-500 hover:underline">My Profile</Link>
        <button
          onClick={async () => {
            await supabase.auth.signOut();
            router.push('/login');
          }}
          className="text-red-500 hover:underline"
        >
          Logout
        </button>
      </nav>

      <h1 className="text-xl font-bold">Welcome to your dashboard</h1>
      {/* Add content below as needed */}
    </div>
  );
}
