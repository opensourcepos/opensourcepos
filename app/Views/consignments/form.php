 (cd "$(git rev-parse --show-toplevel)" && git apply --3way <<'EOF' 
diff --git a/app/Views/consignments/form.php b/app/Views/consignments/form.php
new file mode 100644
index 0000000000000000000000000000000000000000..f9825fe196bc3cb00e6e3bbbd6f928ebe0cffb0e
--- /dev/null
+++ b/app/Views/consignments/form.php
@@ -0,0 +1,122 @@
+<?php
+/**
+ * @var object $consignment_info
+ * @var array $status_options
+ * @var string $controller_name
+ */
+?>
+
+<div id="required_fields_message"><?= lang('Common.fields_required_message') ?></div>
+<ul id="error_message_box" class="error_message_box"></ul>
+
+<?= form_open("$controller_name/save/{$consignment_info->consignment_id}", ['id' => 'consignment_form', 'class' => 'form-horizontal']) ?>
+    <fieldset id="consignment_basic_info">
+        <div class="form-group form-group-sm">
+            <?= form_label(lang('Consignments.sale_reference'), 'sale_reference', ['class' => 'control-label col-xs-3']) ?>
+            <div class="col-xs-8">
+                <p class="form-control-static">POS <?= esc($consignment_info->sale_id) ?></p>
+            </div>
+        </div>
+
+        <div class="form-group form-group-sm">
+            <?= form_label(lang('Consignments.sold_at'), 'sold_at', ['class' => 'control-label col-xs-3']) ?>
+            <div class="col-xs-8">
+                <p class="form-control-static"><?= to_datetime(strtotime($consignment_info->sold_at)) ?></p>
+            </div>
+        </div>
+
+        <div class="form-group form-group-sm">
+            <?= form_label(lang('Consignments.item'), 'item_name', ['class' => 'control-label col-xs-3']) ?>
+            <div class="col-xs-8">
+                <p class="form-control-static"><?= esc($consignment_info->item_name) ?></p>
+            </div>
+        </div>
+
+        <div class="form-group form-group-sm">
+            <?= form_label(lang('Consignments.supplier'), 'supplier', ['class' => 'control-label col-xs-3']) ?>
+            <div class="col-xs-8">
+                <p class="form-control-static"><?= esc($consignment_info->company_name) ?></p>
+            </div>
+        </div>
+
+        <div class="form-group form-group-sm">
+            <?= form_label(lang('Consignments.quantity'), 'quantity', ['class' => 'control-label col-xs-3']) ?>
+            <div class="col-xs-8">
+                <p class="form-control-static"><?= to_quantity_decimals($consignment_info->quantity) ?></p>
+            </div>
+        </div>
+
+        <div class="form-group form-group-sm">
+            <?= form_label(lang('Consignments.sale_amount'), 'sale_amount', ['class' => 'control-label col-xs-3']) ?>
+            <div class="col-xs-8">
+                <p class="form-control-static"><?= to_currency($consignment_info->sale_amount) ?></p>
+            </div>
+        </div>
+
+        <div class="form-group form-group-sm">
+            <?= form_label(lang('Consignments.payout_amount'), 'payout_amount', ['class' => 'control-label col-xs-3']) ?>
+            <div class="col-xs-8">
+                <p class="form-control-static"><?= to_currency($consignment_info->payout_amount) ?> (<?= to_tax_decimals($consignment_info->payout_rate) ?>%)</p>
+            </div>
+        </div>
+
+        <div class="form-group form-group-sm">
+            <?= form_label(lang('Consignments.status'), 'status', ['class' => 'required control-label col-xs-3']) ?>
+            <div class="col-xs-8">
+                <?= form_dropdown('status', $status_options, $consignment_info->status, ['class' => 'form-control', 'id' => 'status']) ?>
+            </div>
+        </div>
+
+        <div class="form-group form-group-sm">
+            <?= form_label(lang('Consignments.payout_date'), 'payout_date', ['class' => 'control-label col-xs-3']) ?>
+            <div class="col-xs-8">
+                <?= form_input([
+                    'type'  => 'datetime-local',
+                    'name'  => 'payout_date',
+                    'id'    => 'payout_date',
+                    'class' => 'form-control input-sm',
+                    'value' => $consignment_info->payout_date ? date('Y-m-d\TH:i', strtotime($consignment_info->payout_date)) : ''
+                ]) ?>
+            </div>
+        </div>
+
+        <div class="form-group form-group-sm">
+            <?= form_label(lang('Consignments.notes'), 'notes', ['class' => 'control-label col-xs-3']) ?>
+            <div class="col-xs-8">
+                <?= form_textarea([
+                    'name'  => 'notes',
+                    'id'    => 'notes',
+                    'class' => 'form-control input-sm',
+                    'rows'  => 3,
+                    'value' => $consignment_info->notes
+                ]) ?>
+            </div>
+        </div>
+    </fieldset>
+<?= form_close() ?>
+
+<script type="text/javascript">
+    $(document).ready(function() {
+        $('#consignment_form').validate($.extend({
+            submitHandler: function(form) {
+                $(form).ajaxSubmit({
+                    success: function(response) {
+                        dialog_support.hide();
+                        table_support.handle_submit('<?= esc($controller_name) ?>', response);
+                    },
+                    dataType: 'json'
+                });
+            },
+            rules: {
+                payout_date: {
+                    required: function() {
+                        return $('#status').val() === '<?= \App\Models\Consignment::STATUS_PAID ?>';
+                    }
+                }
+            },
+            messages: {
+                payout_date: "<?= lang('Consignments.payout_date_required') ?>"
+            }
+        }, form_support.error));
+    });
+</script>
 
EOF
)
