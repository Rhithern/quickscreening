import { useState } from "react";
import { useRouter } from "next/router";
import { createClient } from "@supabase/supabase-js";

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY
);

export default function NewJob() {
  const router = useRouter();
  const [title, setTitle] = useState("");
  const [description, setDescription] = useState("");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  async function handleSubmit(e) {
    e.preventDefault();
    setLoading(true);
    setError(null);

    const user = supabase.auth.user();
    if (!user) {
      setError("You must be logged in as a recruiter.");
      setLoading(false);
      return;
    }

    // Fetch profile id from profiles table (assuming 'user.id' is auth user ID)
    const { data: profile, error: profileError } = await supabase
      .from("profiles")
      .select("id")
      .eq("auth_uid", user.id) // Adjust this column to your auth link
      .single();

    if (profileError) {
      setError("Failed to find your profile.");
      setLoading(false);
      return;
    }

    // Insert new job post
    const { error: insertError } = await supabase.from("jobs").insert([
      {
        recruiter_id: profile.id,
        title,
        description,
      },
    ]);

    if (insertError) {
      setError(insertError.message);
      setLoading(false);
      return;
    }

    setLoading(false);
    router.push("/recruiter/jobs"); // Redirect to job list or dashboard
  }

  return (
    <div>
      <h1>Create a New Job Posting</h1>
      <form onSubmit={handleSubmit}>
        <label>Job Title:</label>
        <input
          type="text"
          value={title}
          onChange={(e) => setTitle(e.target.value)}
          required
        />
        <br />
        <label>Job Description:</label>
        <textarea
          value={description}
          onChange={(e) => setDescription(e.target.value)}
          required
          rows={6}
        />
        <br />
        <button type="submit" disabled={loading}>
          {loading ? "Saving..." : "Save Job"}
        </button>
      </form>
      {error && <p style={{ color: "red" }}>{error}</p>}
    </div>
  );
}
