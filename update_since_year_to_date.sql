-- SQL script to change since_year column to since_date (DATE type)
-- Run this script in phpMyAdmin SQL tab

-- For reg_online table
ALTER TABLE `reg_online` 
CHANGE COLUMN `since_year` `since_date` DATE NULL DEFAULT NULL;

-- Verify the change
DESCRIBE `reg_online`;

