CREATE TABLE IF NOT EXISTS `ospos_tag_definitions` (
 `definition_id` INT(10) NOT NULL AUTO_INCREMENT,
 `definition_name` VARCHAR(255) NOT NULL,
 `definition_type` VARCHAR(45) NOT NULL,
 `definition_unit` VARCHAR(16) NULL,
 `definition_flags` TINYINT(4) NOT NULL,
 `definition_fk` INT(10) NULL,
 `deleted` TINYINT(1) NOT NULL DEFAULT 0,
 PRIMARY KEY (`definition_id`),
 KEY `definition_fk` (`definition_fk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;


CREATE TABLE IF NOT EXISTS `ospos_tag_values` (
 `tag_id` INT NOT NULL AUTO_INCREMENT,
 `tag_value` VARCHAR(255) UNIQUE NULL,
 `tag_date` DATETIME NULL,
 `tag_decimal` DECIMAL(7,3) NULL,
 PRIMARY KEY (`tag_id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;


CREATE TABLE IF NOT EXISTS `ospos_tag_links` (
 `tag_id` INT NULL,
 `definition_id` INT NOT NULL,
 `person_id` INT NULL,
 KEY `tag_id` (`tag_id`),
 KEY `definition_id` (`definition_id`),
 KEY `person_id` (`person_id`),
 UNIQUE `tag_links_uq1` (`tag_id`, `definition_id`, `person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;


ALTER TABLE `ospos_tag_definitions`
 ADD CONSTRAINT `fk_ospos_tag_definitions_ibfk_1` FOREIGN KEY (`definition_fk`) REFERENCES `ospos_tag_definitions` (`definition_id`);


ALTER TABLE `ospos_tag_links`
 ADD CONSTRAINT `ospos_tag_links_ibfk_1` FOREIGN KEY (`definition_id`) REFERENCES `ospos_tag_definitions` (`definition_id`) ON DELETE CASCADE,
 ADD CONSTRAINT `ospos_tag_links_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `ospos_tag_values` (`tag_id`) ON DELETE CASCADE,
 ADD CONSTRAINT `ospos_tag_links_ibfk_3` FOREIGN KEY (`person_id`)  REFERENCES `ospos_people` (`person_id`);

INSERT INTO `ospos_modules` (`name_lang_key`, `desc_lang_key`, `sort`, `module_id`) VALUES
 ('module_tags', 'module_tags_desc', 108, 'tags');

INSERT INTO `ospos_permissions` (`permission_id`, `module_id`) VALUES
 ('tags', 'tags');

INSERT INTO `ospos_grants` (`permission_id`, `person_id`, `menu_group`) VALUES
 ('tags', 1, 'office');