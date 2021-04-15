CREATE TABLE IF NOT EXISTS `ospos_person_attribute_definitions` (
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


CREATE TABLE IF NOT EXISTS `ospos_person_attribute_values` (
 `person_attribute_id` INT NOT NULL AUTO_INCREMENT,
 `person_attribute_value` VARCHAR(255) UNIQUE NULL,
 `person_attribute_date` DATETIME NULL,
 `person_attribute_decimal` DECIMAL(7,3) NULL,
 PRIMARY KEY (`person_attribute_id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;


CREATE TABLE IF NOT EXISTS `ospos_person_attribute_links` (
 `person_attribute_id` INT NULL,
 `definition_id` INT NOT NULL,
 `person_id` INT NULL,
 KEY `person_attribute_id` (`person_attribute_id`),
 KEY `definition_id` (`definition_id`),
 KEY `person_id` (`person_id`),
 UNIQUE `person_attribute_links_uq1` (`person_attribute_id`, `definition_id`, `person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;


ALTER TABLE `ospos_person_attribute_definitions`
 ADD CONSTRAINT `fk_ospos_person_attribute_definitions_ibfk_1` FOREIGN KEY (`definition_fk`) REFERENCES `ospos_person_attribute_definitions` (`definition_id`);


ALTER TABLE `ospos_person_attribute_links`
 ADD CONSTRAINT `ospos_person_attribute_links_ibfk_1` FOREIGN KEY (`definition_id`) REFERENCES `ospos_person_attribute_definitions` (`definition_id`) ON DELETE CASCADE,
 ADD CONSTRAINT `ospos_person_attribute_links_ibfk_2` FOREIGN KEY (`person_attribute_id`) REFERENCES `ospos_person_attribute_values` (`person_attribute_id`) ON DELETE CASCADE,
 ADD CONSTRAINT `ospos_person_attribute_links_ibfk_3` FOREIGN KEY (`person_id`)  REFERENCES `ospos_people` (`person_id`);

INSERT INTO `ospos_modules` (`name_lang_key`, `desc_lang_key`, `sort`, `module_id`) VALUES
 ('module_person_attributes', 'module_person_attributes_desc', 108, 'person_attributes');

INSERT INTO `ospos_permissions` (`permission_id`, `module_id`) VALUES
 ('person_attributes', 'person_attributes');

INSERT INTO `ospos_grants` (`permission_id`, `person_id`, `menu_group`) VALUES
 ('person_attributes', 1, 'office');