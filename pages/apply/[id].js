import { useEffect, useState, useRef } from 'react';
import { useRouter } from 'next/router';
import { createClient } from '@supabase/supabase-js';
import { useUser } from '@supabase/auth-helpers-react';

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY
);

export default function ApplyPage() {
  const router = useRouter();
  const { id } = router.query;
  const user = useUser();

  const [job, setJob] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  // State to hold video blobs & URLs per question index
  const [videos, setVideos] = useState({});
  // State to track if recording for which question index
  const [recordingIndex, setRecordingIndex] = useState(null);
  const [recording, setRecording] = useState(false);
  const mediaRecorderRef = useRef(null);
  const videoChunksRef = useRef([]);

  useEffect(() => {
    if (!id) return;
    if (!user) {
      router.push('/login');
      return;
    }

    async function fetchJob() {
      setLoading(true);
      setError(null);
      try {
        const { data, error } = await supabase
          .from('jobs')
          .select('*')
          .eq('id', id)
          .single();

        if (error) throw error;
        setJob(data);
      } catch (err) {
        setError('Failed to load job details');
        console.error(err);
      } finally {
        setLoading(false);
      }
    }

    fetchJob();
  }, [id, user, router]);

  // Start recording for a question
  async function startRecording(index) {
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      alert('Your browser does not support video recording.');
      return;
    }
    try {
      const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
      videoChunksRef.current = [];
      const mediaRecorder = new MediaRecorder(stream);
      mediaRecorderRef.current = mediaRecorder;

      mediaRecorder.ondataavailable = (event) => {
        if (event.data.size > 0) {
          videoChunksRef.current.push(event.data);
        }
      };

      mediaRecorder.onstop = () => {
        const blob = new Blob(videoChunksRef.current, { type: 'video/webm' });
        const url = URL.createObjectURL(blob);
        setVideos((prev) => ({
          ...prev,
          [index]: { blob, url }
        }));
        // Stop all tracks
        stream.getTracks().forEach((track) => track.stop());
      };

      mediaRecorder.start();
      setRecordingIndex(index);
      setRecording(true);
    } catch (err) {
      alert('Could not start video recording: ' + err.message);
    }
  }

  // Stop recording for the current question
  function stopRecording() {
    if (mediaRecorderRef.current && recording) {
      mediaRecorderRef.current.stop();
      setRecording(false);
      setRecordingIndex(null);
    }
  }

  // Upload video answers to Supabase Storage and insert into videos table
  async function handleSubmit(e) {
    e.preventDefault();
    if (!user || !job) return;

    // Prevent submission if any question is unanswered
    if (job.questions && job.questions.length > 0) {
      for (let i = 0; i < job.questions.length; i++) {
        if (!videos[i]) {
          alert(`Please record an answer for question ${i + 1}`);
          return;
        }
      }
    }

    try {
      // Get candidate profile id
      const { data: profile, error: profileError } = await supabase
        .from('profiles')
        .select('id')
        .eq('user_id', user.id)
        .single();

      if (profileError) throw profileError;

      // Upload each video to Supabase Storage and insert record in 'videos' table
      for (let i = 0; i < job.questions.length; i++) {
        const videoData = videos[i];
        if (!videoData) continue;

        // Create unique file path: candidateID/jobID/questionIndex.webm
        const filePath = `candidate-${profile.id}/job-${job.id}/question-${i}.webm`;

        // Upload video blob to storage bucket 'quickscreening'
        const { error: uploadError } = await supabase.storage
          .from('quickscreening')
          .upload(filePath, videoData.blob, { upsert: true });

        if (uploadError) throw uploadError;

        // Get public URL for the uploaded video
        const { data: publicUrlData } = supabase.storage
          .from('quickscreening')
          .getPublicUrl(filePath);

        // Insert submission record
        const { error: insertError } = await supabase
          .from('videos')
          .insert([
            {
              user_id: profile.id,
              job_id: job.id,
              question_index: i,
              video_url: publicUrlData.publicUrl,
              submitted_at: new Date().toISOString(),
            },
          ]);

        if (insertError) throw insertError;
      }

      alert('Your video answers have been submitted successfully!');
      router.push('/candidate-dashboard');

    } catch (err) {
      console.error('Submission error:', err);
      alert('Failed to submit your answers: ' + err.message);
    }
  }

  if (loading) return <p>Loading...</p>;
  if (error) return <p style={{ color: 'red' }}>{error}</p>;
  if (!job) return <p>Job not found.</p>;

  return (
    <div style={{ maxWidth: 700, margin: 'auto', padding: 20 }}>
      <h1>Apply: {job.title}</h1>
      <p>{job.description}</p>

      {job.questions && job.questions.length > 0 ? (
        <form onSubmit={handleSubmit}>
          {job.questions.map((q, i) => (
            <div key={i} style={{ marginBottom: 30 }}>
              <h3>Question {i + 1}:</h3>
              <p>{q}</p>

              {videos[i]?.url ? (
                <>
                  <video
                    src={videos[i].url}
                    controls
                    style={{ width: '100%', maxHeight: 300 }}
                  />
                  <br />
                  <button
                    type="button"
                    onClick={() => setVideos((prev) => {
                      const copy = { ...prev };
                      delete copy[i];
                      return copy;
                    })}
                    style={{ marginTop: 8 }}
                  >
                    Re-record Answer
                  </button>
                </>
              ) : recording && recordingIndex === i ? (
                <>
                  <p>Recording... 🎥</p>
                  <button type="button" onClick={stopRecording}>Stop Recording</button>
                </>
              ) : (
                <button type="button" onClick={() => startRecording(i)}>
                  Record Answer
                </button>
              )}
            </div>
          ))}

          <button type="submit" style={{ fontWeight: 'bold', fontSize: 18 }}>
            Submit Answers
          </button>
        </form>
      ) : (
        <p>No interview questions provided for this job.</p>
      )}
    </div>
  );
}
