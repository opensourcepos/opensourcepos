-- creating new column of TIMESTAMP type
ALTER TABLE `ospos_sessions`
    ADD COLUMN `temp_timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP();

-- Use FROM_UNIXTIME() to convert from the INT timestamp to a proper datetime type
-- assigning value from old INT column to it, in hope that it will be recognized as timestamp
UPDATE `ospos_sessions` SET `temp_timestamp` = FROM_UNIXTIME(`timestamp`);

-- dropping the old INT column
ALTER TABLE `ospos_sessions` DROP COLUMN `timestamp`;

-- changing the name of the column
ALTER TABLE `ospos_sessions` CHANGE `temp_timestamp` `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP();