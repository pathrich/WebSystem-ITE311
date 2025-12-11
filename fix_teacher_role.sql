-- Fix Teacher Role in Database
-- Run this SQL directly in your database if needed

-- Update teacher user by email
UPDATE `users` SET `role` = 'teacher' WHERE `email` = 'teacher@example.com';

-- Update teacher user by name
UPDATE `users` SET `role` = 'teacher' WHERE `name` = 'Teacher User' AND `role` != 'teacher';

-- Update any user with 'teacher' in email
UPDATE `users` SET `role` = 'teacher' WHERE `email` LIKE '%teacher%' AND `role` != 'teacher';

-- Verify the update
SELECT `id`, `name`, `email`, `role`, `status` FROM `users` WHERE `email` LIKE '%teacher%' OR `name` LIKE '%Teacher%';

