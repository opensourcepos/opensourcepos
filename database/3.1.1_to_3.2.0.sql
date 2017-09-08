-- Add columns to save per-user language selection
ALTER TABLE `ospos_employees` 
	ADD COLUMN `language` VARCHAR(500) DEFAULT NULL,
	ADD COLUMN `language_code` VARCHAR(500) DEFAULT NULL;
