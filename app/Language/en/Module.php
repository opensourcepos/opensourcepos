 (cd "$(git rev-parse --show-toplevel)" && git apply --3way <<'EOF' 
diff --git a/app/Language/en/Module.php b/app/Language/en/Module.php
index d8e024bde409c0f7a3884fedb45676c6fdae9be3..a9b219ff2abc7ca40d4080fc6d58382994d1b85d 100644
--- a/app/Language/en/Module.php
+++ b/app/Language/en/Module.php
@@ -1,37 +1,39 @@
 <?php
 
 return [
     "admin_cashups"              => "",
     "admin_cashups_desc"         => "",
     "attributes"                 => "Attributes",
     "attributes_desc"            => "Add, Update, Delete, and Search attributes.",
     "both"                       => "Both",
     "cashups"                    => "Cashups",
     "cashups_desc"               => "Add, Update, Delete, and Search Cashups.",
     "config"                     => "Configuration",
     "config_desc"                => "Change OSPOS's Configuration.",
+    "consignments"               => "Consignments",
+    "consignments_desc"          => "Track consigned sales and payouts.",
     "customers"                  => "Customers",
     "customers_desc"             => "Add, Update, Delete, and Search Customers.",
     "employees"                  => "Employees",
     "employees_desc"             => "Add, Update, Delete, and Search Employees.",
     "expenses"                   => "Expenses",
     "expenses_categories"        => "Expenses Categories",
     "expenses_categories_desc"   => "Add, Update, and Delete Expenses Categories.",
     "expenses_desc"              => "Add, Update, Delete, and Search Expenses.",
     "giftcards"                  => "Gift Cards",
     "giftcards_desc"             => "Add, Update, Delete and Search Gift Cards.",
     "home"                       => "Home",
     "home_desc"                  => "List home menu modules.",
     "item_kits"                  => "Item Kits",
     "item_kits_desc"             => "Add, Update, Delete and Search Item Kits.",
     "items"                      => "Items",
     "items_desc"                 => "Add, Update, Delete, and Search Items.",
     "messages"                   => "Messages",
     "messages_desc"              => "Send Messages to Customers, Suppliers and Employees.",
     "migrate"                    => "Migrate",
     "migrate_desc"               => "Update the OSPOS Database.",
     "office"                     => "Office",
     "office_desc"                => "List office menu modules.",
     "receivings"                 => "Receivings",
     "receivings_desc"            => "Process Purchase Orders.",
     "reports"                    => "Reports",
 
EOF
)
