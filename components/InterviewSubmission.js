import React, { useState, useRef, useEffect } from 'react';
import { useUser } from '@supabase/auth-helpers-react';
import { createClient } from '@supabase/supabase-js';

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY
);

export default function InterviewSubmission({ question }) {
  const user = useUser();
  const [recording, setRecording] = useState(false);
  const [mediaBlob, setMediaBlob] = useState(null);
  const [uploading, setUploading] = useState(false);
  const [uploadError, setUploadError] = useState(null);
  const [submitted, setSubmitted] = useState(false);

  const mediaRecorderRef = useRef(null);
  const mediaStreamRef = useRef(null);
  const recordedChunksRef = useRef([]);

  // Video refs for preview
  const videoPreviewRef = useRef(null);
  const videoRecordRef = useRef(null);

  // Start recording
  const startRecording = async () => {
    setUploadError(null);
    setSubmitted(false);
    recordedChunksRef.current = [];

    try {
      const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
      mediaStreamRef.current = stream;

      videoRecordRef.current.srcObject = stream;
      videoRecordRef.current.play();

      const mediaRecorder = new MediaRecorder(stream, { mimeType: 'video/webm' });
      mediaRecorderRef.current = mediaRecorder;

      mediaRecorder.ondataavailable = (event) => {
        if (event.data.size > 0) {
          recordedChunksRef.current.push(event.data);
        }
      };

      mediaRecorder.onstop = () => {
        const blob = new Blob(recordedChunksRef.current, { type: 'video/webm' });
        setMediaBlob(blob);

        // Stop all tracks to release camera/mic
        stream.getTracks().forEach(track => track.stop());
      };

      mediaRecorder.start();
      setRecording(true);
    } catch (err) {
      console.error('Error accessing camera/microphone:', err);
      setUploadError('Could not access camera or microphone.');
    }
  };

  // Stop recording
  const stopRecording = () => {
    mediaRecorderRef.current?.stop();
    setRecording(false);
  };

  // Upload recording to Supabase Storage
  const uploadRecording = async () => {
    if (!mediaBlob || !user) return;

    setUploading(true);
    setUploadError(null);

    try {
      // Create unique file name
      const fileName = `interview-answers/${user.id}-${Date.now()}.webm`;

      // Upload to Supabase Storage 'interview-answers' bucket (create this in Supabase dashboard)
      const { data, error } = await supabase.storage
        .from('interview-answers')
        .upload(fileName, mediaBlob, {
          contentType: 'video/webm',
        });

      if (error) throw error;

      // Get public URL or signed URL of uploaded file
      const { publicURL, error: urlError } = supabase.storage
        .from('interview-answers')
        .getPublicUrl(fileName);

      if (urlError) throw urlError;

      // Save submission record to Supabase table 'interview_submissions'
      const { error: dbError } = await supabase
        .from('interview_submissions')
        .insert({
          user_id: user.id,
          question_text: question.text || null,
          question_video_url: question.videoUrl || null,
          answer_video_url: publicURL,
          submitted_at: new Date().toISOString(),
        });

      if (dbError) throw dbError;

      setSubmitted(true);
      setMediaBlob(null);
    } catch (err) {
      console.error('Upload failed:', err);
      setUploadError('Failed to upload or save your answer. Please try again.');
    } finally {
      setUploading(false);
    }
  };

  return (
    <div style={{ maxWidth: 600, margin: 'auto' }}>
      <h3>Interview Question</h3>

      {question.text && <p>{question.text}</p>}

      {question.videoUrl && (
        <video
          src={question.videoUrl}
          controls
          style={{ maxWidth: '100%', marginBottom: 20 }}
        />
      )}

      {/* Live video recording */}
      <div>
        {!recording ? (
          <button onClick={startRecording} disabled={uploading}>
            Start Recording Answer
          </button>
        ) : (
          <button onClick={stopRecording}>
            Stop Recording
          </button>
        )}
      </div>

      {/* Live video stream while recording */}
      {recording && (
        <video
          ref={videoRecordRef}
          style={{ width: '100%', marginTop: 10 }}
          autoPlay
          muted
        />
      )}

      {/* Playback recorded video */}
      {mediaBlob && !recording && (
        <>
          <h4>Preview your answer:</h4>
          <video
            ref={videoPreviewRef}
            src={URL.createObjectURL(mediaBlob)}
            controls
            style={{ width: '100%', marginBottom: 10 }}
          />
          <button onClick={uploadRecording} disabled={uploading}>
            {uploading ? 'Uploading...' : 'Submit Answer'}
          </button>
          <button onClick={() => setMediaBlob(null)} disabled={uploading} style={{ marginLeft: 10 }}>
            Record Again
          </button>
        </>
      )}

      {submitted && <p style={{ color: 'green' }}>Your answer has been submitted successfully!</p>}

      {uploadError && <p style={{ color: 'red' }}>{uploadError}</p>}
    </div>
  );
}
