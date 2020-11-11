ALTER TABLE `ospos_item_kits`
ADD COLUMN `item_kit_number` VARCHAR(255) DEFAULT NULL AFTER `item_kit_id`,
ADD KEY `item_kit_number` (`item_kit_number`);