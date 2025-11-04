 (cd "$(git rev-parse --show-toplevel)" && git apply --3way <<'EOF' 
diff --git a/app/Language/en/Items.php b/app/Language/en/Items.php
index 254fb6235d70fef9cb3260753af50d95da36b40f..ab5f84d61dd8739f33a67d2a9e27f95030d40cf3 100644
--- a/app/Language/en/Items.php
+++ b/app/Language/en/Items.php
@@ -80,42 +80,44 @@ return [
     "pack_name"                          => "Pack Name",
     "qty_per_pack"                       => "Quantity per pack",
     "quantity"                           => "Quantity",
     "quantity_number"                    => "Quantity must be a number.",
     "quantity_required"                  => "Quantity is a required field.",
     "receiving_quantity"                 => "Receiving Quantity",
     "remove_image"                       => "Remove Image",
     "reorder_level"                      => "Reorder Level",
     "reorder_level_number"               => "Reorder Level must be a number.",
     "reorder_level_required"             => "Reorder Level is a required field.",
     "retrive_item_info"                  => "Retrieve Item Info",
     "sales_tax_1"                        => "Sales Tax",
     "sales_tax_2"                        => "Sales Tax 2",
     "search_attributes"                  => "Search Attributes",
     "select_image"                       => "Select Image",
     "serialized_items"                   => "Serialized Items",
     "standard"                           => "Standard",
     "stock"                              => "Stock",
     "stock_location"                     => "Stock location",
     "stock_type"                         => "Stock Type",
     "successful_adding"                  => "You have successfully added item",
     "successful_bulk_edit"               => "You have successfully updated the selected item(s)",
     "successful_deleted"                 => "You have successfully deleted",
     "successful_updating"                => "You have successfully updated item",
     "supplier"                           => "Supplier",
+    "is_consignment"                    => "Consignment Item",
+    "consignment_rate"                  => "Consignment Rate (%)",
     "tax_1"                              => "Tax 1",
     "tax_2"                              => "Tax 2",
     "tax_3"                              => "",
     "tax_category"                       => "Tax Category",
     "tax_percent"                        => "",
     "tax_percent_number"                 => "Tax Percent must be a numeric value",
     "tax_percent_required"               => "Tax Percent is a required field.",
     "tax_percents"                       => "Tax Percent(s)",
     "temp"                               => "Temporary",
     "type"                               => "Item Type",
     "unit_price"                         => "Retail Price",
     "unit_price_number"                  => "Unit price must be a number.",
     "unit_price_required"                => "Retail Price is a required field.",
     "upc_database"                       => "Barcode Database",
     "update"                             => "Update Item",
     "use_inventory_menu"                 => "Use Inventory Menu",
 ];
 
EOF
)
