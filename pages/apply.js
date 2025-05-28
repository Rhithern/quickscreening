import { useRouter } from 'next/router';
import { useEffect, useRef, useState } from 'react';
import { createClient } from '@supabase/supabase-js';
import { useUser } from '@supabase/auth-helpers-react';

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY
);

export default function Apply() {
  const router = useRouter();
  const user = useUser();
  const { id: jobId } = router.query;

  const [job, setJob] = useState(null);
  const [loading, setLoading] = useState(true);
  const [recordings, setRecordings] = useState({});
  const [uploading, setUploading] = useState(false);

  const mediaRecorderRefs = useRef({});
  const streamRef = useRef(null);
  const chunksRef = useRef({});

  useEffect(() => {
    if (!user) router.push('/login');
  }, [user]);

  useEffect(() => {
    if (jobId) fetchJob();
  }, [jobId]);

  async function fetchJob() {
    const { data, error } = await supabase
      .from('jobs')
      .select('*')
      .eq('id', jobId)
      .single();

    if (error) {
      console.error(error);
    } else {
      setJob(data);
    }

    setLoading(false);
  }

  async function startRecording(index) {
    const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
    streamRef.current = stream;

    const recorder = new MediaRecorder(stream);
    chunksRef.current[index] = [];

    recorder.ondataavailable = (e) => {
      chunksRef.current[index].push(e.data);
    };

    recorder.onstop = () => {
      const blob = new Blob(chunksRef.current[index], { type: 'video/webm' });
      const url = URL.createObjectURL(blob);
      setRecordings((prev) => ({ ...prev, [index]: { blob, url } }));
      stream.getTracks().forEach((track) => track.stop());
    };

    mediaRecorderRefs.current[index] = recorder;
    recorder.start();
  }

  function stopRecording(index) {
    mediaRecorderRefs.current[index]?.stop();
  }

  async function handleSubmit() {
    if (!job || !user) return;

    setUploading(true);

    const { data: profile } = await supabase
      .from('profiles')
      .select('id')
      .eq('user_id', user.id)
      .single();

    const uploads = Object.entries(recordings).map(async ([index, { blob }]) => {
      const filePath = `videos/${user.id}-${jobId}-${index}-${Date.now()}.webm`;

      const { error: uploadError } = await supabase.storage
        .from('quickscreening')
        .upload(filePath, blob, {
          contentType: 'video/webm',
        });

      if (uploadError) throw uploadError;

      const { data: publicUrlData } = supabase.storage
        .from('quickscreening')
        .getPublicUrl(filePath);

      await supabase.from('videos').insert([
        {
          user_id: profile.id,
          job_id: job.id,
          url: publicUrlData.publicUrl,
        },
      ]);
    });

    try {
      await Promise.all(uploads);
      alert('Application submitted!');
      router.push('/candidate-dashboard');
    } catch (e) {
      alert('Error uploading videos');
      console.error(e);
    } finally {
      setUploading(false);
    }
  }

  if (!user || loading) return <p>Loading...</p>;
  if (!job) return <p>Job not found</p>;

  return (
    <div style={{ maxWidth: 700, margin: 'auto', padding: 20 }}>
      <h1>Apply for: {job.title}</h1>
      <p>{job.description}</p>

      {Array.isArray(job.questions) && job.questions.map((question, index) => (
        <div key={index} style={{ marginBottom: 30 }}>
          <h4>Question {index + 1}</h4>
          <p>{question}</p>

          <div>
            {!recordings[index] ? (
              <>
                <button onClick={() => startRecording(index)}>Start Recording</button>
                <button onClick={() => stopRecording(index)} style={{ marginLeft: 10 }}>
                  Stop Recording
                </button>
              </>
            ) : (
              <>
                <video src={recordings[index].url} controls width="100%" />
              </>
            )}
          </div>
        </div>
      ))}

      <button onClick={handleSubmit} disabled={uploading}>
        {uploading ? 'Submitting...' : 'Submit Application'}
      </button>
    </div>
  );
}
