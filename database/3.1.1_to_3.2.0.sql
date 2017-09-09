-- Add columns to save per-user language selection
ALTER TABLE `ospos_employees` 
	ADD COLUMN `language` VARCHAR(48) DEFAULT NULL,
	ADD COLUMN `language_code` VARCHAR(48) DEFAULT NULL;
