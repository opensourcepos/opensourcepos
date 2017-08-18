ALTER TABLE `ospos_suppliers`
   ADD COLUMN `agency_name` VARCHAR(255) NOT NULL;

INSERT INTO `ospos_app_config` (`key`, `value`) VALUES
   ('dateformat', 'm/d/Y'),
   ('timeformat', 'H:i:s'),
   ('barcode_generate_if_empty', '0');

ALTER TABLE `ospos_sales_suspended`
    DROP KEY `invoice_number`;

ALTER TABLE `ospos_items` 
  CHANGE COLUMN `item_pic` `pic_id` int(10) DEFAULT NULL;

-- Clear out emptied comments (0 inserted in comment if empty #192)
ALTER TABLE ospos_sales
MODIFY COLUMN comment text DEFAULT NULL;

ALTER TABLE ospos_receivings
MODIFY COLUMN comment text DEFAULT NULL;

ALTER TABLE ospos_sales_suspended
MODIFY COLUMN comment text DEFAULT NULL;

UPDATE `ospos_sales` SET comment = NULL WHERE comment = '0';
UPDATE `ospos_receivings` SET comment = NULL WHERE comment = '0';
UPDATE `ospos_sales_suspended` SET comment = NULL WHERE comment = '0';
