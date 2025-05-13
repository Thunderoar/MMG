-- SQL script to enable soft delete on attendedstudent table

ALTER TABLE `attendedstudent`
  ADD COLUMN `is_deleted` tinyint(1) NOT NULL DEFAULT 0 AFTER `is_dealt`;
