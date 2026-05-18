-- Add missing columns to users table
ALTER TABLE users ADD COLUMN status TEXT DEFAULT 'ACTIVE';
ALTER TABLE users ADD COLUMN avatar_path TEXT;
ALTER TABLE users ADD COLUMN last_login_at DATETIME;
