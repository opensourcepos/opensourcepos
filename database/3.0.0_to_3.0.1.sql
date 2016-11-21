-- Disable ONLY_FULL_GROUP_BY required by MySQL 5.7
SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));