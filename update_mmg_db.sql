-- SQL script to update mmg_db database based on index.php form fields
-- Run this script to add the missing columns to the attendedstudent table

-- Add seminar_id column if it doesn't exist
ALTER TABLE `attendedstudent` 
ADD COLUMN `seminar_id` int DEFAULT NULL,
ADD FOREIGN KEY (`seminar_id`) REFERENCES `seminar_schedules`(`id`) ON DELETE SET NULL;
