-- Add support for Work Orders

INSERT INTO `ospos_app_config` (`key`, `value`) VALUES
('work_order_enable', '0'),
('work_order_format', 'W%y{WSEQ:6}'),
('last_used_work_order_number', '0');

ALTER TABLE `ospos_sales`
  ADD COLUMN `work_order_number` varchar(32) DEFAULT NULL,
  ADD COLUMN `sale_type` tinyint(2) NOT NULL DEFAULT 0;

-- sale_type (0=pos, 1=invoice, 2=work order, 3=quote)

update `ospos_sales`
  set `sale_type` = '3' where quote_number IS NOT NULL;

update `ospos_sales`
  set `sale_type` = '2', `work_order_number` = `quote_number`
  where quote_number IS NOT NULL;

update `ospos_sales`
  set `sale_type` = '1' where invoice_number IS NOT NULL;
