-- Add support for Work Orders

INSERT INTO `ospos_app_config` (`key`, `value`) VALUES
('work_order_enable', '0'),
('work_order_format', 'W%y{WSEQ:6}'),
('last_used_work_order_number', '0');

ALTER TABLE `ospos_sales`
  ADD COLUMN `work_order_number` varchar(32) DEFAULT NULL,
  ADD COLUMN `sale_type` tinyint(2) NOT NULL DEFAULT 0;

-- sale_type (0=pos, 1=invoice, 2=work order, 3=quote, 4=return)

UPDATE `ospos_sales`
  SET `sale_type` = 0;

UPDATE ospos_sales t1
  SET sale_type = 4
WHERE EXISTS (SELECT t2.sale_id FROM ospos_sales_items t2 WHERE t1.sale_id = t2.sale_id AND t2.quantity_purchased < 0);

UPDATE `ospos_sales`
  SET `sale_type` = 3
WHERE `quote_number` IS NOT NULL;

-- The following is needed only if quotes were being treated as work orders.
-- UPDATE `ospos_sales`
--   SET `sale_type` = 2, `work_order_number` = `quote_number`
-- WHERE quote_number IS NOT NULL;

-- Identify invoices
UPDATE `ospos_sales`
  SET `sale_type` = 1
WHERE `invoice_number` IS NOT NULL;
