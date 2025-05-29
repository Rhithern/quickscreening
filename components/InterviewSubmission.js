import React, { useState } from 'react';
import { useUser } from '@supabase/auth-helpers-react';
import { uploadVideoToStorage, saveInterviewSubmission } from '../lib/interviewHelpers';

export default function InterviewSubmission({ question }) {
  const user = useUser();
  const [videoFile, setVideoFile] = useState(null);
  const [loading, setLoading] = useState(false);
  const [message, setMessage] = useState('');

  const handleFileChange = (e) => {
    setVideoFile(e.target.files[0]);
  };

  const handleSubmit = async () => {
    if (!videoFile) {
      setMessage('Please record or upload your answer video.');
      return;
    }
    setLoading(true);
    setMessage('');

    try {
      const answerVideoUrl = await uploadVideoToStorage(videoFile, user.id);

      await saveInterviewSubmission({
        userId: user.id,
        questionText: question.text,
        questionVideoUrl: question.videoUrl || null,
        answerVideoUrl,
      });

      setMessage('Submission saved successfully!');
      setVideoFile(null);
    } catch (err) {
      setMessage('Error uploading submission: ' + err.message);
    } finally {
      setLoading(false);
    }
  };

  if (!user) return <p>Please log in to submit your interview answer.</p>;

  return (
    <div>
      <h3>Interview Question</h3>
      <p>{question.text}</p>

      {question.videoUrl && (
        <video src={question.videoUrl} controls width="400" />
      )}

      <div>
        <label>
          Upload your answer video:
          <input type="file" accept="video/*" onChange={handleFileChange} />
        </label>
      </div>

      <button onClick={handleSubmit} disabled={loading}>
        {loading ? 'Submitting...' : 'Submit Answer'}
      </button>

      {message && <p>{message}</p>}
    </div>
  );
}
