INSERT INTO `ospos_app_config` (`key`, `value`) VALUES 
('barcode_content', 'id'),
('barcode_first_row', 'category'),
('barcode_second_row', 'item_code'),
('barcode_third_row', 'cost_price'),
('barcode_num_in_row', '2'),
('barcode_font', 'Arial'),
('barcode_font_size', '10'),
('barcode_height', '50'),
('barcode_quality', '100'),
('barcode_type', 'Code39'),
('barcode_width', '250'),
('company_logo', ''),
('barcode_page_width', '100'),      
('barcode_page_cellspacing', '20'),
('receipt_show_taxes', '0'),
('use_invoice_template', '1'),
('invoice_default_comments', 'This is a default comment'),
('invoice_email_message', 'Dear $CU, In attachment the receipt for sale $CO'),
('print_silently', '1'),
('print_header', '0'),
('print_footer', '0'),
('print_top_margin', '0'),
('print_left_margin', '0'),
('print_bottom_margin', '0'),
('print_right_margin', '0'),
('default_sales_discount', '0'),
('lines_per_page', '25'),
('show_total_discount', '25');

INSERT IGNORE INTO `ospos_permissions` (permission_id, module_id, location_id) 
(SELECT CONCAT('sales_', location_name), 'sales', location_id FROM ospos_stock_locations);

INSERT IGNORE INTO `ospos_permissions` (permission_id, module_id, location_id)
(SELECT CONCAT('receivings_', location_name), 'receivings', location_id FROM ospos_stock_locations);

-- add item_pic column to items table
ALTER TABLE `ospos_items` 
   ADD COLUMN `item_pic` int(10) DEFAULT NULL;

ALTER TABLE `ospos_people` 
   ADD COLUMN `gender` int(1) DEFAULT NULL;
   
-- drop redundant payment_type column in sales, add index to sale_time to speed up sorting
ALTER TABLE `ospos_sales`
    DROP COLUMN `payment_type`,
    ADD INDEX `sale_time` (`sale_time`);
   
ALTER TABLE `ospos_customers`
    ADD COLUMN `company_name` varchar(255) DEFAULT NULL;

ALTER TABLE `ospos_giftcards`
    MODIFY `person_id` int(10) DEFAULT NULL;

ALTER TABLE `ospos_receivings_items`
    ADD COLUMN `receiving_quantity` int(11) NOT NULL DEFAULT '1';
