import { createClient } from '@supabase/supabase-js';

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY
);

export async function uploadVideoToStorage(file, userId) {
  if (!file || !userId) throw new Error('File and userId are required');

  const fileExt = file.name.split('.').pop();
  const fileName = `${userId}/${Date.now()}.${fileExt}`;

  const { data, error } = await supabase.storage
    .from('interview-answers')
    .upload(fileName, file, {
      cacheControl: '3600',
      upsert: false,
      contentType: file.type,
    });

  if (error) throw error;

  const { publicURL, error: urlError } = supabase.storage
    .from('interview-answers')
    .getPublicUrl(fileName);

  if (urlError) throw urlError;

  return publicURL;
}

export async function saveInterviewSubmission({
  userId,
  questionText,
  questionVideoUrl = null,
  answerVideoUrl,
}) {
  if (!userId || !questionText || !answerVideoUrl) throw new Error('Missing required fields');

  const { data, error } = await supabase
    .from('interview_submissions')
    .insert([
      {
        user_id: userId,
        question_text: questionText,
        question_video_url: questionVideoUrl,
        answer_video_url: answerVideoUrl,
        submitted_at: new Date().toISOString(),
      },
    ])
    .select()
    .single();

  if (error) throw error;

  return data;
}
