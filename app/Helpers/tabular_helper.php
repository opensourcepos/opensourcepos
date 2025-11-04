 (cd "$(git rev-parse --show-toplevel)" && git apply --3way <<'EOF' 
diff --git a/app/Helpers/tabular_helper.php b/app/Helpers/tabular_helper.php
index 82f6dedd94ed6fc50daf1d43f959d3fabd842e4d..95f3e7acae4c07bdce2acd0512a18619eefcac20 100644
--- a/app/Helpers/tabular_helper.php
+++ b/app/Helpers/tabular_helper.php
@@ -375,50 +375,52 @@ function get_supplier_data_row(object $supplier): array
                     'title'           => lang('Messages.sms_send')
                 ]
             ),
         'edit'             => anchor(
             "$controller/view/$supplier->person_id",
             '<span class="glyphicon glyphicon-edit"></span>',
             [
                 'class'           => "modal-dlg",
                 'data-btn-submit' => lang('Common.submit'),
                 'title'           => lang(ucfirst($controller) . ".update")
             ]
         )
     ];
 }
 
 function item_headers(): array
 {
     return [
         ['items.item_id' => lang('Common.id')],
         ['item_number'   => lang('Items.item_number')],
         ['name'          => lang('Items.name')],
         ['category'      => lang('Items.category')],
         ['company_name'  => lang('Suppliers.company_name')],
         ['cost_price'    => lang('Items.cost_price')],
         ['unit_price'    => lang('Items.unit_price')],
+        ['is_consignment' => lang('Items.is_consignment')],
+        ['consignment_rate' => lang('Items.consignment_rate')],
         ['quantity'      => lang('Items.quantity')]
     ];
 }
 
 /**
  * Get the header for the items tabular view
  */
 function get_items_manage_table_headers(): string
 {
     $attribute = model(Attribute::class);
     $config = config(OSPOS::class)->settings;
     $definition_names = $attribute->get_definitions_by_flags($attribute::SHOW_IN_ITEMS);    // TODO: this should be made into a constant in constants.php
 
     $headers = item_headers();
 
     if ($config['use_destination_based_tax']) {
         $headers[] = ['tax_percents' => lang('Items.tax_category'), 'sortable' => false];
     } else {
         $headers[] = ['tax_percents' => lang('Items.tax_percents'), 'sortable' => false];
     }
 
     $headers[] = ['item_pic' => lang('Items.image'), 'sortable' => false];
 
     foreach ($definition_names as $definition_id => $definition_name) {
         $headers[] = [$definition_id => $definition_name, 'sortable' => false];
@@ -466,50 +468,54 @@ function get_item_data_row(object $item): array
         $ext = pathinfo($item->pic_filename, PATHINFO_EXTENSION);
 
         $images = $ext == ''
             ? glob("./uploads/item_pics/$item->pic_filename.*")
             : glob("./uploads/item_pics/$item->pic_filename");
 
         if (sizeof($images) > 0) {
             $image .= '<a class="rollover" href="' . base_url($images[0]) . '"><img alt="Image thumbnail" src="' . site_url('items/PicThumb/' . pathinfo($images[0], PATHINFO_BASENAME)) . '"></a>';
         }
     }
 
     if ($config['multi_pack_enabled']) {
         $item->name .= NAME_SEPARATOR . $item->pack_name;
     }
 
     $definition_names = $attribute->get_definitions_by_flags($attribute::SHOW_IN_ITEMS);
 
     $columns = [
         'items.item_id' => $item->item_id,
         'item_number'   => $item->item_number,
         'name'          => $item->name,
         'category'      => $item->category,
         'company_name'  => $item->company_name,    // TODO: This isn't in the items table. Should this be here?
         'cost_price'    => to_currency($item->cost_price),
         'unit_price'    => to_currency($item->unit_price),
+        'is_consignment' => !empty($item->is_consignment) ? lang('Common.yes') : lang('Common.no'),
+        'consignment_rate' => $item->consignment_rate !== null
+            ? to_tax_decimals($item->consignment_rate) . '%'
+            : '-',
         'quantity'      => to_quantity_decimals($item->quantity),
         'tax_percents'  => !$tax_percents ? '-' : $tax_percents,
         'item_pic'      => $image
     ];
 
     $icons = [
         'inventory' => anchor(
             "$controller/inventory/$item->item_id",
             '<span class="glyphicon glyphicon-pushpin"></span>',
             [
                 'class'           => 'modal-dlg',
                 'data-btn-submit' => lang('Common.submit'),
                 'title'           => lang(ucfirst($controller) . ".count")
             ]
         ),
         'stock'     => anchor(
             "$controller/countDetails/$item->item_id",
             '<span class="glyphicon glyphicon-list-alt"></span>',
             [
                 'class' => 'modal-dlg',
                 'title' => lang(ucfirst($controller) . ".details_count")
             ]
         ),
         'edit'      => anchor(
             "$controller/view/$item->item_id",
@@ -891,36 +897,105 @@ function get_cash_up_data_row(object $cash_up): array
         'cashup_id'            => $cash_up->cashup_id,
         'open_date'            => to_datetime(strtotime($cash_up->open_date)),
         'open_employee_id'     => $cash_up->open_first_name . ' ' . $cash_up->open_last_name,
         'open_amount_cash'     => to_currency($cash_up->open_amount_cash),
         'transfer_amount_cash' => to_currency($cash_up->transfer_amount_cash),
         'close_date'           => to_datetime(strtotime($cash_up->close_date)),
         'close_employee_id'    => $cash_up->close_first_name . ' ' . $cash_up->close_last_name,
         'closed_amount_cash'   => to_currency($cash_up->closed_amount_cash),
         'note'                 => $cash_up->note ? '<span class="glyphicon glyphicon-ok"></span>' : '<span class="glyphicon glyphicon-remove"></span>',
         'closed_amount_due'    => to_currency($cash_up->closed_amount_due),
         'closed_amount_card'   => to_currency($cash_up->closed_amount_card),
         'closed_amount_check'  => to_currency($cash_up->closed_amount_check),
         'closed_amount_total'  => to_currency($cash_up->closed_amount_total),
         'edit'                 => anchor(
             "$controller/view/$cash_up->cashup_id",
             '<span class="glyphicon glyphicon-edit"></span>',
             [
                 'class'           => 'modal-dlg',
                 'data-btn-submit' => lang('Common.submit'),
                 'title'           => lang(ucfirst($controller) . ".update")
             ]
         )
     ];
 }
 
+function consignment_headers(): array
+{
+    return [
+        ['consignment_transactions.consignment_id' => lang('Consignments.consignment_id')],
+        ['sold_at'            => lang('Consignments.sold_at')],
+        ['sale_reference'     => lang('Consignments.sale_reference')],
+        ['item_name'          => lang('Consignments.item')],
+        ['company_name'       => lang('Consignments.supplier')],
+        ['quantity'           => lang('Consignments.quantity')],
+        ['sale_amount'        => lang('Consignments.sale_amount')],
+        ['payout_rate'        => lang('Consignments.payout_rate')],
+        ['payout_amount'      => lang('Consignments.payout_amount')],
+        ['status_label'       => lang('Consignments.status')],
+        ['payout_date'        => lang('Consignments.payout_date')]
+    ];
+}
+
+function get_consignments_manage_table_headers(): string
+{
+    $headers = consignment_headers();
+    $headers[] = ['notes' => lang('Consignments.notes'), 'sortable' => false];
+    $headers[] = ['edit' => '', 'escape' => false];
+
+    return transform_headers($headers);
+}
+
+function get_consignment_data_row(object $consignment): array
+{
+    $controller = get_controller();
+    $config = config(OSPOS::class)->settings;
+
+    $sold_at = !empty($config['date_or_time_format'])
+        ? to_datetime(strtotime($consignment->sold_at))
+        : to_date(strtotime($consignment->sold_at));
+
+    $status = match ($consignment->status) {
+        App\Models\Consignment::STATUS_PAID     => lang('Consignments.paid'),
+        App\Models\Consignment::STATUS_CANCELED => lang('Consignments.canceled'),
+        default                                  => lang('Consignments.pending')
+    };
+
+    $columns = [
+        'consignment_transactions.consignment_id' => $consignment->consignment_id,
+        'sold_at'            => $sold_at,
+        'sale_reference'     => 'POS ' . $consignment->sale_id,
+        'item_name'          => $consignment->item_name,
+        'company_name'       => $consignment->company_name,
+        'quantity'           => to_quantity_decimals($consignment->quantity),
+        'sale_amount'        => to_currency($consignment->sale_amount),
+        'payout_rate'        => to_tax_decimals($consignment->payout_rate) . '%',
+        'payout_amount'      => to_currency($consignment->payout_amount),
+        'status_label'       => $status,
+        'payout_date'        => $consignment->payout_date ? to_datetime(strtotime($consignment->payout_date)) : '-',
+        'notes'              => $consignment->notes ?? ''
+    ];
+
+    $columns['edit'] = anchor(
+        "$controller/view/$consignment->consignment_id",
+        '<span class="glyphicon glyphicon-edit"></span>',
+        [
+            'class'           => 'modal-dlg',
+            'data-btn-submit' => lang('Common.submit'),
+            'title'           => lang('Consignments.update')
+        ]
+    );
+
+    return $columns;
+}
+
 /**
  * Returns the right-most part of the controller name
  * @return string
  */
 function get_controller(): string
 {
     $router = service('router');
     $controller_name = strtolower($router->controllerName());
     $controller_name_parts = explode('\\', $controller_name);
     return end($controller_name_parts);
 }
 
EOF
)
