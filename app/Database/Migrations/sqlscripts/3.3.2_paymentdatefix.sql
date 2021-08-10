UPDATE `ospos_sales_payments`
  JOIN `ospos_sales` ON `ospos_sales`.`sale_id`=`ospos_sales_payments`.`sale_id`
  SET `ospos_sales_payments`.`payment_time`=`ospos_sales`.`sale_time`;
