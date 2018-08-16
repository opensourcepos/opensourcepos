
CREATE TABLE IF NOT EXISTS `ospos_attribute_definitions` (
 `definition_id` INT(10) NOT NULL AUTO_INCREMENT,
 `definition_name` VARCHAR(255) NOT NULL,
 `definition_type` VARCHAR(45) NOT NULL,
 `definition_flags` TINYINT(4) NOT NULL,
 `definition_fk` INT(10) NULL,
 `deleted` TINYINT(1) NOT NULL DEFAULT 0,
 PRIMARY KEY (`definition_id`),
 KEY `definition_fk` (`definition_fk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `ospos_attribute_values` (
 `attribute_id` INT NOT NULL AUTO_INCREMENT,
 `attribute_value` VARCHAR(45) NULL,
 `attribute_datetime` DATETIME NULL,
 PRIMARY KEY (`attribute_id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `ospos_attribute_links` (
 `attribute_id` INT NULL,
 `definition_id` INT NOT NULL,
 `item_id` INT NULL,
 `sale_id` INT NULL,
 `receiving_id` INT NULL,
 KEY `attribute_id` (`attribute_id`),
 KEY `definition_id` (`definition_id`),
 KEY `item_id` (`item_id`),
 KEY `sale_id` (`sale_id`),
 KEY `receiving_id` (`receiving_id`),
 UNIQUE `attribute_links_uq1` (`attribute_id`, `definition_id`, `item_id`, `sale_id`, `receiving_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `ospos_attribute_definitions`
 ADD CONSTRAINT `fk_ospos_attribute_definitions_ibfk_1` FOREIGN KEY (`definition_fk`) REFERENCES `ospos_attribute_definitions` (`definition_id`);


ALTER TABLE `ospos_attribute_links`
 ADD CONSTRAINT `ospos_attribute_links_ibfk_1` FOREIGN KEY (`definition_id`) REFERENCES `ospos_attribute_definitions` (`definition_id`) ON DELETE CASCADE,
 ADD CONSTRAINT `ospos_attribute_links_ibfk_2` FOREIGN KEY (`attribute_id`) REFERENCES `ospos_attribute_values` (`attribute_id`) ON DELETE CASCADE,
 ADD CONSTRAINT `ospos_attribute_links_ibfk_3` FOREIGN KEY (`item_id`)  REFERENCES `ospos_items` (`item_id`),
 ADD CONSTRAINT `ospos_attribute_links_ibfk_4` FOREIGN KEY (`receiving_id`) REFERENCES `ospos_receivings` (`receiving_id`),
 ADD CONSTRAINT `ospos_attribute_links_ibfk_5` FOREIGN KEY (`sale_id`) REFERENCES `ospos_sales` (`sale_id`);


UPDATE `ospos_modules` SET `sort` = 120 WHERE `name_lang_key` = 'module_config';

INSERT INTO `ospos_modules` (`name_lang_key`, `desc_lang_key`, `sort`, `module_id`) VALUES
 ('module_attributes', 'module_attributes_desc', 110, 'attributes');

INSERT INTO `ospos_permissions` (`permission_id`, `module_id`) VALUES
 ('attributes', 'attributes');

INSERT INTO `ospos_grants` (`permission_id`, `person_id`, `menu_group`) VALUES
 ('attributes', 1, 'office');

-- migrate custom fields to text attributes
-- NOTE: items with custom attributes won't keep their selected category!!
INSERT INTO `ospos_attribute_definitions` (definition_name, definition_type) SELECT `value`, 'TEXT' FROM ospos_app_config WHERE `key` = 'custom1_name';
INSERT INTO `ospos_attribute_definitions` (definition_name, definition_type) SELECT `value`, 'TEXT' FROM ospos_app_config WHERE `key` = 'custom2_name';
INSERT INTO `ospos_attribute_definitions` (definition_name, definition_type) SELECT `value`, 'TEXT' FROM ospos_app_config WHERE `key` = 'custom3_name';
INSERT INTO `ospos_attribute_definitions` (definition_name, definition_type) SELECT `value`, 'TEXT' FROM ospos_app_config WHERE `key` = 'custom4_name';
INSERT INTO `ospos_attribute_definitions` (definition_name, definition_type) SELECT `value`, 'TEXT' FROM ospos_app_config WHERE `key` = 'custom5_name';
INSERT INTO `ospos_attribute_definitions` (definition_name, definition_type) SELECT `value`, 'TEXT' FROM ospos_app_config WHERE `key` = 'custom6_name';
INSERT INTO `ospos_attribute_definitions` (definition_name, definition_type) SELECT `value`, 'TEXT' FROM ospos_app_config WHERE `key` = 'custom7_name';
INSERT INTO `ospos_attribute_definitions` (definition_name, definition_type) SELECT `value`, 'TEXT' FROM ospos_app_config WHERE `key` = 'custom8_name';
INSERT INTO `ospos_attribute_definitions` (definition_name, definition_type) SELECT `value`, 'TEXT' FROM ospos_app_config WHERE `key` = 'custom9_name';
INSERT INTO `ospos_attribute_definitions` (definition_name, definition_type) SELECT `value`, 'TEXT' FROM ospos_app_config WHERE `key` = 'custom10_name';

INSERT INTO ospos_attribute_links (definition_id, item_id) SELECT definition_id, item_id FROM ospos_attribute_definitions, ospos_app_config, ospos_items
 WHERE ospos_app_config.`key` = 'custom1_name' AND ospos_app_config.`value` = ospos_attribute_definitions.definition_name AND custom1 IS NOT NULL;
INSERT INTO ospos_attribute_links (definition_id, item_id) SELECT definition_id, item_id FROM ospos_attribute_definitions, ospos_app_config, ospos_items
 WHERE ospos_app_config.`key` = 'custom2_name' AND ospos_app_config.`value` = ospos_attribute_definitions.definition_name AND custom2 IS NOT NULL;
INSERT INTO ospos_attribute_links (definition_id, item_id) SELECT definition_id, item_id FROM ospos_attribute_definitions, ospos_app_config, ospos_items
 WHERE ospos_app_config.`key` = 'custom3_name' AND ospos_app_config.`value` = ospos_attribute_definitions.definition_name AND custom3 IS NOT NULL;
INSERT INTO ospos_attribute_links (definition_id, item_id) SELECT definition_id, item_id FROM ospos_attribute_definitions, ospos_app_config, ospos_items
 WHERE ospos_app_config.`key` = 'custom4_name' AND ospos_app_config.`value` = ospos_attribute_definitions.definition_name AND custom4 IS NOT NULL;
INSERT INTO ospos_attribute_links (definition_id, item_id) SELECT definition_id, item_id FROM ospos_attribute_definitions, ospos_app_config, ospos_items
 WHERE ospos_app_config.`key` = 'custom5_name' AND ospos_app_config.`value` = ospos_attribute_definitions.definition_name AND custom5 IS NOT NULL;
INSERT INTO ospos_attribute_links (definition_id, item_id) SELECT definition_id, item_id FROM ospos_attribute_definitions, ospos_app_config, ospos_items
 WHERE ospos_app_config.`key` = 'custom6_name' AND ospos_app_config.`value` = ospos_attribute_definitions.definition_name AND custom6 IS NOT NULL;
INSERT INTO ospos_attribute_links (definition_id, item_id) SELECT definition_id, item_id FROM ospos_attribute_definitions, ospos_app_config, ospos_items
 WHERE ospos_app_config.`key` = 'custom7_name' AND ospos_app_config.`value` = ospos_attribute_definitions.definition_name AND custom7 IS NOT NULL;
INSERT INTO ospos_attribute_links (definition_id, item_id) SELECT definition_id, item_id FROM ospos_attribute_definitions, ospos_app_config, ospos_items
 WHERE ospos_app_config.`key` = 'custom8_name' AND ospos_app_config.`value` = ospos_attribute_definitions.definition_name AND custom8 IS NOT NULL;
INSERT INTO ospos_attribute_links (definition_id, item_id) SELECT definition_id, item_id FROM ospos_attribute_definitions, ospos_app_config, ospos_items
 WHERE ospos_app_config.`key` = 'custom9_name' AND ospos_app_config.`value` = ospos_attribute_definitions.definition_name AND custom9 IS NOT NULL;
INSERT INTO ospos_attribute_links (definition_id, item_id) SELECT definition_id, item_id FROM ospos_attribute_definitions, ospos_app_config, ospos_items
 WHERE ospos_app_config.`key` = 'custom10_name' AND ospos_app_config.`value` = ospos_attribute_definitions.definition_name AND custom10 IS NOT NULL;

INSERT INTO ospos_attribute_values (attribute_value) SELECT DISTINCT custom1 FROM ospos_items;
INSERT INTO ospos_attribute_values (attribute_value) SELECT DISTINCT custom2 FROM ospos_items;
INSERT INTO ospos_attribute_values (attribute_value) SELECT DISTINCT custom3 FROM ospos_items;
INSERT INTO ospos_attribute_values (attribute_value) SELECT DISTINCT custom4 FROM ospos_items;
INSERT INTO ospos_attribute_values (attribute_value) SELECT DISTINCT custom5 FROM ospos_items;
INSERT INTO ospos_attribute_values (attribute_value) SELECT DISTINCT custom6 FROM ospos_items;
INSERT INTO ospos_attribute_values (attribute_value) SELECT DISTINCT custom7 FROM ospos_items;
INSERT INTO ospos_attribute_values (attribute_value) SELECT DISTINCT custom8 FROM ospos_items;
INSERT INTO ospos_attribute_values (attribute_value) SELECT DISTINCT custom9 FROM ospos_items;
INSERT INTO ospos_attribute_values (attribute_value) SELECT DISTINCT custom10 FROM ospos_items;

UPDATE ospos_attribute_links
 INNER JOIN ospos_items ON ospos_attribute_links.item_id = ospos_items.item_id
 INNER JOIN ospos_attribute_values ON attribute_value = custom1
 SET ospos_attribute_links.attribute_id = ospos_attribute_values.attribute_id
 WHERE definition_id IN (SELECT definition_id FROM ospos_attribute_definitions
 WHERE definition_name = (SELECT `value` FROM ospos_app_config WHERE `key` = 'custom1_name'));

UPDATE ospos_attribute_links
 INNER JOIN ospos_items ON ospos_attribute_links.item_id = ospos_items.item_id
 INNER JOIN ospos_attribute_values ON attribute_value = custom2
 SET ospos_attribute_links.attribute_id = ospos_attribute_values.attribute_id
 WHERE definition_id IN (SELECT definition_id FROM ospos_attribute_definitions
 WHERE definition_name = (SELECT `value` FROM ospos_app_config WHERE `key` = 'custom2_name'));

UPDATE ospos_attribute_links
 INNER JOIN ospos_items ON ospos_attribute_links.item_id = ospos_items.item_id
 INNER JOIN ospos_attribute_values ON attribute_value = custom3
 SET ospos_attribute_links.attribute_id = ospos_attribute_values.attribute_id
 WHERE definition_id IN (SELECT definition_id FROM ospos_attribute_definitions
 WHERE definition_name = (SELECT `value` FROM ospos_app_config WHERE `key` = 'custom3_name'));

UPDATE ospos_attribute_links
 INNER JOIN ospos_items ON ospos_attribute_links.item_id = ospos_items.item_id
 INNER JOIN ospos_attribute_values ON attribute_value = custom4
 SET ospos_attribute_links.attribute_id = ospos_attribute_values.attribute_id
 WHERE definition_id IN (SELECT definition_id FROM ospos_attribute_definitions
 WHERE definition_name = (SELECT `value` FROM ospos_app_config WHERE `key` = 'custom4_name'));

UPDATE ospos_attribute_links
 INNER JOIN ospos_items ON ospos_attribute_links.item_id = ospos_items.item_id
 INNER JOIN ospos_attribute_values ON attribute_value = custom5
 SET ospos_attribute_links.attribute_id = ospos_attribute_values.attribute_id
 WHERE definition_id IN (SELECT definition_id FROM ospos_attribute_definitions
 WHERE definition_name = (SELECT `value` FROM ospos_app_config WHERE `key` = 'custom5_name'));

UPDATE ospos_attribute_links
 INNER JOIN ospos_items ON ospos_attribute_links.item_id = ospos_items.item_id
 INNER JOIN ospos_attribute_values ON attribute_value = custom6
 SET ospos_attribute_links.attribute_id = ospos_attribute_values.attribute_id
 WHERE definition_id IN (SELECT definition_id FROM ospos_attribute_definitions
 WHERE definition_name = (SELECT `value` FROM ospos_app_config WHERE `key` = 'custom6_name'));

UPDATE ospos_attribute_links
 INNER JOIN ospos_items ON ospos_attribute_links.item_id = ospos_items.item_id
 INNER JOIN ospos_attribute_values ON attribute_value = custom7
 SET ospos_attribute_links.attribute_id = ospos_attribute_values.attribute_id
 WHERE definition_id IN (SELECT definition_id FROM ospos_attribute_definitions
 WHERE definition_name = (SELECT `value` FROM ospos_app_config WHERE `key` = 'custom7_name'));

UPDATE ospos_attribute_links
 INNER JOIN ospos_items ON ospos_attribute_links.item_id = ospos_items.item_id
 INNER JOIN ospos_attribute_values ON attribute_value = custom8
 SET ospos_attribute_links.attribute_id = ospos_attribute_values.attribute_id
 WHERE definition_id IN (SELECT definition_id FROM ospos_attribute_definitions
 WHERE definition_name = (SELECT `value` FROM ospos_app_config WHERE `key` = 'custom8_name'));

UPDATE ospos_attribute_links
 INNER JOIN ospos_items ON ospos_attribute_links.item_id = ospos_items.item_id
 INNER JOIN ospos_attribute_values ON attribute_value = custom9
 SET ospos_attribute_links.attribute_id = ospos_attribute_values.attribute_id
 WHERE definition_id IN (SELECT definition_id FROM ospos_attribute_definitions
 WHERE definition_name = (SELECT `value` FROM ospos_app_config WHERE `key` = 'custom9_name'));

UPDATE ospos_attribute_links
 INNER JOIN ospos_items ON ospos_attribute_links.item_id = ospos_items.item_id
 INNER JOIN ospos_attribute_values ON attribute_value = custom10
 SET ospos_attribute_links.attribute_id = ospos_attribute_values.attribute_id
 WHERE definition_id IN (SELECT definition_id FROM ospos_attribute_definitions
 WHERE definition_name = (SELECT `value` FROM ospos_app_config WHERE `key` = 'custom10_name'));

ALTER TABLE `ospos_items`
 DROP COLUMN `custom1`,
 DROP COLUMN `custom2`,
 DROP COLUMN `custom3`,
 DROP COLUMN `custom4`,
 DROP COLUMN `custom5`,
 DROP COLUMN `custom6`,
 DROP COLUMN `custom7`,
 DROP COLUMN `custom8`,
 DROP COLUMN `custom9`,
 DROP COLUMN `custom10`;