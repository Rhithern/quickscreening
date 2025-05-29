import { useState } from 'react';

export default function InterviewSubmission({ user, supabase }) {
  // You can enhance this to allow selecting job/interview question, recording video, etc.

  const [videoFile, setVideoFile] = useState(null);
  const [uploading, setUploading] = useState(false);

  const handleFileChange = (e) => {
    setVideoFile(e.target.files[0]);
  };

  const handleUpload = async () => {
    if (!videoFile) return alert('Please select a video file');

    setUploading(true);

    const fileExt = videoFile.name.split('.').pop();
    const fileName = `${user.id}_${Date.now()}.${fileExt}`;
    const filePath = `candidate_videos/${fileName}`;

    let { error: uploadError } = await supabase.storage
      .from('videos')
      .upload(filePath, videoFile);

    if (uploadError) {
      alert('Upload error: ' + uploadError.message);
      setUploading(false);
      return;
    }

    alert('Video uploaded successfully!');
    setVideoFile(null);
    setUploading(false);
  };

  return (
    <section style={{ marginBottom: 30 }}>
      <h2>Submit Interview Video</h2>
      <input type="file" accept="video/*" onChange={handleFileChange} />
      <br />
      <button onClick={handleUpload} disabled={uploading}>
        {uploading ? 'Uploading...' : 'Upload Video'}
      </button>
    </section>
  );
}
