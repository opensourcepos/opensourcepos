UPDATE `ospos_suppliers`
  SET `tax_id` = ''
  WHERE `tax_id` IS NULL;

ALTER TABLE `ospos_suppliers`
  CHANGE COLUMN `tax_id` `tax_id` varchar(32) NOT NULL DEFAULT '';
