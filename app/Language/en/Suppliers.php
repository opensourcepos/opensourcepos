 (cd "$(git rev-parse --show-toplevel)" && git apply --3way <<'EOF' 
diff --git a/app/Language/en/Suppliers.php b/app/Language/en/Suppliers.php
index fa21844df168eb29fd54ffed63953e3230f1a098..c141582325a5fe608e3798897a225ea2ee0a2d88 100644
--- a/app/Language/en/Suppliers.php
+++ b/app/Language/en/Suppliers.php
@@ -1,25 +1,27 @@
 <?php
 
 return [
     "account_number"        => "Account Number",
     "agency_name"           => "Agency Name",
     "cannot_be_deleted"     => "Could not delete selected Supplier(s). One or more have Sales.",
     "category"              => "Category",
     "company_name"          => "Company Name",
     "company_name_required" => "Company Name is a required field.",
     "confirm_delete"        => "Are you sure you want to delete the selected Supplier(s)?",
     "confirm_restore"       => "Are you sure you want to restore selected Supplier(s)?",
     "cost"                  => "Cost Supplier",
     "error_adding_updating" => "Supplier update or add failed.",
     "goods"                 => "Goods Supplier",
+    "is_consignor"          => "Consignor",
+    "default_consignment_rate" => "Default Consignment Rate (%)",
     "new"                   => "New Supplier",
     "none_selected"         => "You have not selected Supplier(s) to delete.",
     "one_or_multiple"       => "Supplier(s)",
     "successful_adding"     => "You have successfully added Supplier",
     "successful_deleted"    => "You have successfully deleted",
     "successful_updating"   => "You have successfully updated Supplier",
     "supplier"              => "Supplier",
     "supplier_id"           => "Id",
     "tax_id"                => "Tax Id",
     "update"                => "Update Supplier",
 ];
 
EOF
)
