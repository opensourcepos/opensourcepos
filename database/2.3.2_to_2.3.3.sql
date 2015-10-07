ALTER TABLE `ospos_suppliers`
   ADD COLUMN `agency_name` VARCHAR(255) NOT NULL;

INSERT INTO `ospos_app_config` (`key`, `value`) VALUES
   ('dateformat', 'm-d-Y'),
   ('timeformat', 'H:i:s');

ALTER TABLE `ospos_sales_suspended`
    DROP KEY `invoice_number`;