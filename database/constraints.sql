--
-- Constraints for dumped tables
--

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `people` (`person_id`),
  ADD CONSTRAINT `customers_ibfk_2` FOREIGN KEY (`package_id`) REFERENCES `customers_packages` (`package_id`);

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `people` (`person_id`);

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`trans_items`) REFERENCES `items` (`item_id`),
  ADD CONSTRAINT `inventory_ibfk_2` FOREIGN KEY (`trans_user`) REFERENCES `employees` (`person_id`),
  ADD CONSTRAINT `inventory_ibfk_3` FOREIGN KEY (`trans_location`) REFERENCES `stock_locations` (`location_id`);

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`person_id`);

--
-- Constraints for table `items_taxes`
--
ALTER TABLE `items_taxes`
  ADD CONSTRAINT `items_taxes_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`) ON DELETE CASCADE;

--
-- Constraints for table `item_kit_items`
--
ALTER TABLE `item_kit_items`
  ADD CONSTRAINT `item_kit_items_ibfk_1` FOREIGN KEY (`item_kit_id`) REFERENCES `item_kits` (`item_kit_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `item_kit_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`)  ON DELETE CASCADE;

--
-- Constraints for table `permissions`
--
ALTER TABLE `permissions`
  ADD CONSTRAINT `permissions_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `modules` (`module_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permissions_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `stock_locations` (`location_id`) ON DELETE CASCADE;

--
-- Constraints for table `grants`
--
ALTER TABLE `grants`
  ADD CONSTRAINT `grants_ibfk_1` foreign key (`permission_id`) references `permissions` (`permission_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grants_ibfk_2` foreign key (`person_id`) references `employees` (`person_id`) ON DELETE CASCADE;

--
-- Constraints for table `receivings`
--
ALTER TABLE `receivings`
  ADD CONSTRAINT `receivings_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`person_id`),
  ADD CONSTRAINT `receivings_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`person_id`);

--
-- Constraints for table `receivings_items`
--
ALTER TABLE `receivings_items`
  ADD CONSTRAINT `receivings_items_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`),
  ADD CONSTRAINT `receivings_items_ibfk_2` FOREIGN KEY (`receiving_id`) REFERENCES `receivings` (`receiving_id`);

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`person_id`),
  ADD CONSTRAINT `sales_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`person_id`),
  ADD CONSTRAINT `sales_ibfk_3` FOREIGN KEY (`dinner_table_id`) REFERENCES `dinner_tables` (`dinner_table_id`);

--
-- Constraints for table `sales_items`
--
ALTER TABLE `sales_items`
  ADD CONSTRAINT `sales_items_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`),
  ADD CONSTRAINT `sales_items_ibfk_2` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`sale_id`),
  ADD CONSTRAINT `sales_items_ibfk_3` FOREIGN KEY (`item_location`) REFERENCES `stock_locations` (`location_id`);

--
-- Constraints for table `sales_items_taxes`
--
ALTER TABLE `sales_items_taxes`
  ADD CONSTRAINT `sales_items_taxes_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales_items` (`sale_id`),
  ADD CONSTRAINT `sales_items_taxes_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`);

--
-- Constraints for table `sales_payments`
--
ALTER TABLE `sales_payments`
  ADD CONSTRAINT `sales_payments_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`sale_id`);

--
-- Constraints for table `item_quantities`
--
ALTER TABLE `item_quantities`
  ADD CONSTRAINT `item_quantities_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`),
  ADD CONSTRAINT `item_quantities_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `stock_locations` (`location_id`);

--
-- Constraints for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD CONSTRAINT `suppliers_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `people` (`person_id`);
  
--
-- Constraints for table `giftcards`
--
ALTER TABLE `giftcards`
  ADD CONSTRAINT `giftcards_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `people` (`person_id`);

--
-- Constraints for table `customers_points`
--
ALTER TABLE `customers_points`
 ADD CONSTRAINT `customers_points_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `customers` (`person_id`),
 ADD CONSTRAINT `customers_points_ibfk_2` FOREIGN KEY (`package_id`) REFERENCES `customers_packages` (`package_id`),
 ADD CONSTRAINT `customers_points_ibfk_3` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`sale_id`);

--
-- Constraints for table `sales_reward_points`
--
ALTER TABLE `sales_reward_points`
 ADD CONSTRAINT `sales_reward_points_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`sale_id`);
