ALTER TABLE `ospos_migrations`
	MODIFY COLUMN `version` VARCHAR(255) NOT NULL,
	ADD `id` bigint(20) UNSIGNED NOT NULL,
	ADD `class` varchar(255) NOT NULL,
	ADD `group` varchar(255) NOT NULL,
	ADD `namespace` varchar(255) NOT NULL,
	ADD `time` int(11) NOT NULL,
	ADD `batch` int(11) UNSIGNED NOT NULL;

ALTER TABLE `ospos_migrations`
	ADD PRIMARY KEY (`id`);

ALTER TABLE `ospos_migrations`
	MODIFY COLUMN `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

UPDATE `ospos_migrations`
SET
	`namespace` = 'App',
	`time` = 0,
	`batch` = 0
WHERE `ospos_migrations`.`id` = 1
