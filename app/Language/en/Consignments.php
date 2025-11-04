 (cd "$(git rev-parse --show-toplevel)" && git apply --3way <<'EOF' 
diff --git a/app/Language/en/Consignments.php b/app/Language/en/Consignments.php
new file mode 100644
index 0000000000000000000000000000000000000000..a133f46339747802ed9f1c280157c220518a331d
--- /dev/null
+++ b/app/Language/en/Consignments.php
@@ -0,0 +1,27 @@
+<?php
+
+return [
+    "manage_title"          => "Consignment Transactions",
+    "mark_paid"             => "Mark Paid",
+    "confirm_mark_paid"     => "Mark selected consignments as paid?",
+    "consignment_id"        => "ID",
+    "sold_at"               => "Sold At",
+    "sale_reference"        => "Sale Reference",
+    "item"                  => "Item",
+    "supplier"              => "Consignor",
+    "quantity"              => "Quantity",
+    "sale_amount"           => "Sale Amount",
+    "payout_rate"           => "Payout Rate",
+    "payout_amount"         => "Payout Amount",
+    "status"                => "Status",
+    "payout_date"           => "Payout Date",
+    "notes"                 => "Notes",
+    "pending"               => "Pending",
+    "paid"                  => "Paid",
+    "canceled"              => "Canceled",
+    "successful_updating"   => "Consignment updated successfully.",
+    "error_updating"        => "Unable to update consignment.",
+    "successful_mark_paid"  => "Consignments marked as paid.",
+    "error_mark_paid"       => "Unable to mark consignments as paid.",
+    "payout_date_required"  => "Payout date is required when marking a consignment as paid."
+];
 
EOF
)
