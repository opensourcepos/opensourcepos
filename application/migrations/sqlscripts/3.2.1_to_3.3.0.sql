--
-- Add support for Multi-Package Items
--

INSERT INTO `ospos_app_config` (`key`, `value`) VALUES
('multi_pack_enabled', '0');

ALTER TABLE `ospos_items`
  ADD COLUMN `qty_per_pack` decimal(15,3) NOT NULL DEFAULT 1,
  ADD COLUMN `pack_name` varchar(8) DEFAULT 'Each',
  ADD COLUMN `low_sell_item_id` int(10) DEFAULT 0;

UPDATE `ospos_items`
  SET `low_sell_item_id` = `item_id`
  WHERE `low_sell_item_id` = 0;
