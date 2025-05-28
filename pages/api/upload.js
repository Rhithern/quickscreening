import { createClient } from '@supabase/supabase-js';

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY
);

export const config = {
  api: {
    bodyParser: false,
  },
};

export default async function handler(req, res) {
  const chunks = [];
  for await (const chunk of req) {
    chunks.push(chunk);
  }
  const buffer = Buffer.concat(chunks);

  const fileName = `recordings/interview-${Date.now()}.webm`;

  const { error } = await supabase.storage
    .from('videos')
    .upload(fileName, buffer, {
      contentType: 'video/webm',
    });

  if (error) return res.status(500).json({ error: error.message });

  res.status(200).json({ message: 'Upload successful', fileName });
}
