-- Drop FK ospos_attribute_links_ibfk_1 if it exists
SET @fk := (
  SELECT CONSTRAINT_NAME
  FROM information_schema.REFERENTIAL_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME = 'ospos_attribute_links'
    AND CONSTRAINT_NAME = 'ospos_attribute_links_ibfk_1'
);

SET @sql := IF(@fk IS NOT NULL,
  'ALTER TABLE `ospos_attribute_links` DROP FOREIGN KEY `ospos_attribute_links_ibfk_1`',
  'DO 0'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;


-- Drop FK ospos_attribute_links_ibfk_2 if it exists
SET @fk := (
  SELECT CONSTRAINT_NAME
  FROM information_schema.REFERENTIAL_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME = 'ospos_attribute_links'
    AND CONSTRAINT_NAME = 'ospos_attribute_links_ibfk_2'
);

SET @sql := IF(@fk IS NOT NULL,
  'ALTER TABLE `ospos_attribute_links` DROP FOREIGN KEY `ospos_attribute_links_ibfk_2`',
  'DO 0'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

# Prevents duplicate attribute links with the same definition_id and item_id.
# This accounts for dropdown rows (null item_id) and rows associated with sales or receivings.
ALTER TABLE `ospos_attribute_links` DROP COLUMN IF EXISTS `generated_unique_column`;

ALTER TABLE `ospos_attribute_links`
    ADD COLUMN `generated_unique_column` VARCHAR(255) GENERATED ALWAYS AS (
        CASE
            WHEN `sale_id` IS NULL AND `receiving_id` IS NULL AND `item_id` IS NOT NULL THEN CONCAT(`definition_id`, '-', `item_id`)
            ELSE NULL
        END
        ) STORED,
    ADD UNIQUE INDEX `attribute_links_uq3` (`generated_unique_column`);

ALTER TABLE `ospos_attribute_links` ADD CONSTRAINT `ospos_attribute_links_ibfk_1` FOREIGN KEY (`definition_id`) REFERENCES `ospos_attribute_definitions` (`definition_id`) ON DELETE RESTRICT;
ALTER TABLE `ospos_attribute_links` ADD CONSTRAINT `ospos_attribute_links_ibfk_2` FOREIGN KEY (`attribute_id`) REFERENCES `ospos_attribute_values` (`attribute_id`) ON DELETE RESTRICT;
