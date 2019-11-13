<?php $this->load->view("partial/header"); ?>

    <div id="register_wrapper">
        <?php $tabindex = 0; ?>
        <!-- Sale Items List -->

         <table class="sales_table_100" id="register">

            <thead>
            <tr>
                <th style="width: 15%;"><?php echo $this->lang->line('sales_item_number'); ?></th>
                <th style="width: 35%;"><?php echo $this->lang->line('sales_item_name'); ?></th>
                <th style="width: 10%;"><?php echo $this->lang->line('sales_price'); ?></th>
                <th style="width: 10%;"><?php echo $this->lang->line('sales_quantity'); ?></th>
                <th style="width: 10%;"><?php echo $this->lang->line('sales_discount'); ?></th>
                <th style="width: 10%;"><?php echo $this->lang->line('sales_total'); ?></th>
            </tr>
            </thead>

            <tbody id="cart_contents">
            <?php
            if(count($cart) == 0)
            {
                ?>
                <tr>
                    <td colspan='8'>
                        <div class='alert alert-dismissible alert-info'><?php echo $this->lang->line('sales_no_items_in_cart'); ?></div>
                    </td>
                </tr>
                <?php
            }
            else
            {
                foreach(array_reverse($cart, TRUE) as $line=>$item)
                {
                    ?>
                    <tr>
                        <td><?php echo $item['item_number']; ?></td>
                        <td style="align: center;">
                            <?php echo $item['name']; ?><br /> <?php if($item['stock_type'] == '0'): echo '[' . to_quantity_decimals($item['in_stock']) . ' in ' . $item['stock_name'] . ']'; endif; ?>
                            <?php echo form_hidden('location', $item['item_location']); ?>
                        </td>

                        <?php
                        if($items_module_allowed)
                        {
                            ?>
                            <td><?php echo form_input(array('name'=>'price', 'class'=>'form-control input-sm', 'value'=>to_currency_no_money($item['price']), 'tabindex'=>++$tabindex, 'onClick'=>'this.select();', 'readOnly' => 'true'));?></td>
                            <?php
                        }
                        else
                        {
                            ?>
                            <td>
                                <?php echo to_currency($item['price']); ?>
                                <?php echo form_hidden('price', to_currency_no_money($item['price'])); ?>
                            </td>
                            <?php
                        }
                        ?>

                        <td>
                            <?php
                            if($item['is_serialized']==1)
                            {
                                echo to_quantity_decimals($item['quantity']);
                                echo form_hidden('quantity', $item['quantity']);
                            }
                            else
                            {
                                echo form_input(array('name'=>'quantity', 'class'=>'form-control input-sm', 'value'=>to_quantity_decimals($item['quantity']), 'tabindex'=>++$tabindex, 'onClick'=>'this.select();', 'readOnly' => 'true'));
                            }
                            ?>
                        </td>

                        <td><?php echo form_input(array('name'=>'discount', 'class'=>'form-control input-sm', 'value'=>to_decimals($item['discount'], 0), 'tabindex'=>++$tabindex, 'onClick'=>'this.select();', 'readOnly' => 'true'));?></td>
                        <td>
                            <?php
                            if($item['item_type'] == ITEM_AMOUNT_ENTRY)
                            {
                                echo form_input(array('name'=>'discounted_total', 'class'=>'form-control input-sm', 'value'=>to_currency_no_money($item['discounted_total']), 'tabindex'=>++$tabindex, 'onClick'=>'this.select();', 'readOnly' => 'true'));
                            }
                            else
                            {
                                echo to_currency($item['discounted_total']);
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <?php
                        if($item['allow_alt_description']==1)
                        {
                            ?>
                            <td style="color: #2F4F4F;"><?php echo $this->lang->line('sales_description_abbrv');?></td>
                            <?php
                        }
                        ?>

                        <td colspan='2' style="text-align: left;">
                            <?php
                            if($item['allow_alt_description']==1)
                            {
                                echo form_input(array('name'=>'description', 'class'=>'form-control input-sm', 'value'=>$item['description'], 'onClick'=>'this.select();'));
                            }
                            else
                            {
                                if($item['description']!='')
                                {
                                    echo $item['description'];
                                    echo form_hidden('description', $item['description']);
                                }
                                else
                                {
                                    echo $this->lang->line('sales_no_description');
                                    echo form_hidden('description','');
                                }
                            }
                            ?>
                        </td>
                        <td>&nbsp;</td>
                        <td style="color: #2F4F4F;">
                            <?php
                            if($item['is_serialized']==1)
                            {
                                echo $this->lang->line('sales_serial');
                            }
                            ?>
                        </td>
                        <td colspan='4' style="text-align: left;">
                            <?php
                            if($item['is_serialized']==1)
                            {
                                echo form_input(array('name'=>'serialnumber', 'class'=>'form-control input-sm', 'value'=>$item['serialnumber'], 'onClick'=>'this.select();'));
                            }
                            else
                            {
                                echo form_hidden('serialnumber', '');
                            }
                            ?>
                        </td>
                    </tr>
                    <?php echo form_close(); ?>
                    <?php
                }
            }
            ?>
            </tbody>
        </table>
    </div>

    <!-- Overall Sale -->

    <div id="overall_sale" class="panel panel-default">
        <div class="panel-body">
            <?php
            if(isset($customer)) {
                ?>
                <table class="sales_table_100">
                    <tr>
                        <th style='width: 55%;'><?php echo $this->lang->line("sales_customer"); ?></th>
                        <th style="width: 45%; text-align: right;"><?php echo anchor('customers/view/' . $customer_id, $customer, array('class' => 'modal-dlg', 'data-btn-submit' => $this->lang->line('common_submit'), 'title' => $this->lang->line('customers_update'))); ?></th>
                    </tr>
                    <?php
                    if (!empty($customer_email)) {
                        ?>
                        <tr>
                            <th style='width: 55%;'><?php echo $this->lang->line("sales_customer_email"); ?></th>
                            <th style="width: 45%; text-align: right;"><?php echo $customer_email; ?></th>
                        </tr>
                        <?php
                    }
                    ?>
                    <?php
                    if (!empty($customer_address)) {
                        ?>
                        <tr>
                            <th style='width: 55%;'><?php echo $this->lang->line("sales_customer_address"); ?></th>
                            <th style="width: 45%; text-align: right;"><?php echo $customer_address; ?></th>
                        </tr>
                        <?php
                    }
                    ?>
                    <?php
                    if (!empty($customer_location)) {
                        ?>
                        <tr>
                            <th style='width: 55%;'><?php echo $this->lang->line("sales_customer_location"); ?></th>
                            <th style="width: 45%; text-align: right;"><?php echo $customer_location; ?></th>
                        </tr>
                        <?php
                    }
                    ?>
                    <tr>
                        <th style='width: 55%;'><?php echo $this->lang->line("sales_customer_discount"); ?></th>
                        <th style="width: 45%; text-align: right;"><?php echo $customer_discount_percent . ' %'; ?></th>
                    </tr>
                    <?php if ($this->config->item('customer_reward_enable') == TRUE): ?>
                        <?php
                        if (!empty($customer_rewards)) {
                            ?>
                            <tr>
                                <th style='width: 55%;'><?php echo $this->lang->line("rewards_package"); ?></th>
                                <th style="width: 45%; text-align: right;"><?php echo $customer_rewards['package_name']; ?></th>
                            </tr>
                            <tr>
                                <th style='width: 55%;'><?php echo $this->lang->line("customers_available_points"); ?></th>
                                <th style="width: 45%; text-align: right;"><?php echo $customer_rewards['points']; ?></th>
                            </tr>
                            <?php
                        }
                        ?>
                    <?php endif; ?>
                    <tr>
                        <th style='width: 55%;'><?php echo $this->lang->line("sales_customer_total"); ?></th>
                        <th style="width: 45%; text-align: right;"><?php echo to_currency($customer_total); ?></th>
                    </tr>
                    <?php
                    if (!empty($mailchimp_info)) {
                        ?>
                        <tr>
                            <th style='width: 55%;'><?php echo $this->lang->line("sales_customer_mailchimp_status"); ?></th>
                            <th style="width: 45%; text-align: right;"><?php echo $mailchimp_info['status']; ?></th>
                        </tr>
                        <?php
                    }
                    ?>
                </table>

                <?php
            }
            ?>
            <?php echo form_close(); ?>

            <table class="sales_table_100" id="sale_totals">
                <tr>
                    <th style="width: 55%;"><?php echo $this->lang->line('sales_quantity_of_items',$item_count); ?></th>
                    <th style="width: 45%; text-align: right;"><?php echo $total_units; ?></th>
                </tr>
                <tr>
                    <th style="width: 55%;"><?php echo $this->lang->line('sales_sub_total'); ?></th>
                    <th style="width: 45%; text-align: right;"><?php echo to_currency($subtotal); ?></th>
                </tr>

                <?php
                foreach($taxes as $tax_group_index=>$sales_tax)
                {
                    ?>
                    <tr>
                        <th style='width: 55%;'><?php echo $sales_tax['tax_group']; ?></th>
                        <th style="width: 45%; text-align: right;"><?php echo to_currency_tax($sales_tax['sale_tax_amount']); ?></th>
                    </tr>
                    <?php
                }
                ?>

                <tr>
                    <th style='width: 55%;'><?php echo $this->lang->line('sales_total'); ?></th>
                    <th style="width: 45%; text-align: right;"><span id="sale_total"><?php echo to_currency($total); ?></span></th>
                </tr>
            </table>

            <?php
            // Only show this part if there are Items already in the sale.
            if(count($cart) > 0)
            {
                ?>
                <table class="sales_table_100" id="payment_totals">
                    <tr>
                        <th style="width: 55%;"><?php echo $this->lang->line('sales_payments_total');?></th>
                        <th style="width: 45%; text-align: right;"><?php echo to_currency($payments_total); ?></th>
                    </tr>
                    <tr>
                        <th style="width: 55%;"><?php echo $this->lang->line('sales_amount_due');?></th>
                        <th style="width: 45%; text-align: right;"><span id="sale_amount_due"><?php echo to_currency($amount_due); ?></span></th>
                    </tr>
                </table>




                <?php
            }
            ?>
        </div>
    </div>

    <script type="text/javascript">

        $(document).ready(function()
        {

            setInterval('location.reload()', 1200);

            $("#item").autocomplete(
                {
                    source: '<?php echo site_url($controller_name."/item_search"); ?>',
                    minChars: 0,
                    autoFocus: false,
                    delay: 500,
                    select: function (a, ui) {
                        $(this).val(ui.item.value);
                        $("#add_item_form").submit();
                        return false;
                    }
                });

            $('#item').focus();

            $('#item').keypress(function (e) {
                if(e.which == 13) {
                    $('#add_item_form').submit();
                    return false;
                }
            });

            $('#item').blur(function()
            {
                $(this).val("<?php echo $this->lang->line('sales_start_typing_item_name'); ?>");
            });

            var clear_fields = function()
            {
                if($(this).val().match("<?php echo $this->lang->line('sales_start_typing_item_name') . '|' . $this->lang->line('sales_start_typing_customer_name'); ?>"))
                {
                    $(this).val('');
                }
            };

            $("#customer").autocomplete(
                {
                    source: '<?php echo site_url("customers/suggest"); ?>',
                    minChars: 0,
                    delay: 10,
                    select: function (a, ui) {
                        $(this).val(ui.item.value);
                        $("#select_customer_form").submit();
                    }
                });

            $('#item, #customer').click(clear_fields).dblclick(function(event)
            {
                $(this).autocomplete("search");
            });

            $('#customer').blur(function()
            {
                $(this).val("<?php echo $this->lang->line('sales_start_typing_customer_name'); ?>");
            });

            $(".giftcard-input").autocomplete(
                {
                    source: '<?php echo site_url("giftcards/suggest"); ?>',
                    minChars: 0,
                    delay: 10,
                    select: function (a, ui) {
                        $(this).val(ui.item.value);
                        $("#add_payment_form").submit();
                    }
                });

            $('#comment').keyup(function()
            {
                $.post('<?php echo site_url($controller_name."/set_comment");?>', {comment: $('#comment').val()});
            });

            <?php
            if($this->config->item('invoice_enable') == TRUE)
            {
            ?>
            $('#sales_invoice_number').keyup(function()
            {
                $.post('<?php echo site_url($controller_name."/set_invoice_number");?>', {sales_invoice_number: $('#sales_invoice_number').val()});
            });

            var enable_invoice_number = function()
            {
                var enabled = $("#sales_invoice_enable").is(":checked");
                $("#sales_invoice_number").prop("disabled", !enabled).parents('tr').show();
                return enabled;
            }

            enable_invoice_number();

            $("#sales_invoice_enable").change(function()
            {
                var enabled = enable_invoice_number();
                $.post('<?php echo site_url($controller_name."/set_invoice_number_enabled");?>', {sales_invoice_number_enabled: enabled});
            });
            <?php
            }
            ?>

            $("#sales_print_after_sale").change(function()
            {
                $.post('<?php echo site_url($controller_name."/set_print_after_sale");?>', {sales_print_after_sale: $(this).is(":checked")});
            });

            $("#price_work_orders").change(function()
            {
                $.post('<?php echo site_url($controller_name."/set_price_work_orders");?>', {price_work_orders: $(this).is(":checked")});
            });

            $('#email_receipt').change(function()
            {
                $.post('<?php echo site_url($controller_name."/set_email_receipt");?>', {email_receipt: $(this).is(":checked")});
            });

            $("#finish_sale_button").click(function()
            {
                $('#buttons_form').attr('action', '<?php echo site_url($controller_name."/complete"); ?>');
                $('#buttons_form').submit();
            });

            $("#finish_invoice_quote_button").click(function()
            {
                $('#buttons_form').attr('action', '<?php echo site_url($controller_name."/complete"); ?>');
                $('#buttons_form').submit();
            });

            $("#suspend_sale_button").click(function()
            {
                $('#buttons_form').attr('action', '<?php echo site_url($controller_name."/suspend"); ?>');
                $('#buttons_form').submit();
            });

            $("#cancel_sale_button").click(function()
            {
                if(confirm('<?php echo $this->lang->line("sales_confirm_cancel_sale"); ?>'))
                {
                    $('#buttons_form').attr('action', '<?php echo site_url($controller_name."/cancel"); ?>');
                    $('#buttons_form').submit();
                }
            });

            $("#add_payment_button").click(function()
            {
                $('#add_payment_form').submit();
            });

            $("#payment_types").change(check_payment_type).ready(check_payment_type);

            $("#cart_contents input").keypress(function(event)
            {
                if(event.which == 13)
                {
                    $(this).parents("tr").prevAll("form:first").submit();
                }
            });

            $("#amount_tendered").keypress(function(event)
            {
                if(event.which == 13)
                {
                    $('#add_payment_form').submit();
                }
            });

            $("#finish_sale_button").keypress(function(event)
            {
                if(event.which == 13)
                {
                    $('#finish_sale_form').submit();
                }
            });

            dialog_support.init("a.modal-dlg, button.modal-dlg");

            table_support.handle_submit = function(resource, response, stay_open)
            {
                $.notify(response.message, { type: response.success ? 'success' : 'danger'} );

                if(response.success)
                {
                    if(resource.match(/customers$/))
                    {
                        $("#customer").val(response.id);
                        $("#select_customer_form").submit();
                    }
                    else
                    {
                        var $stock_location = $("select[name='stock_location']").val();
                        $("#item_location").val($stock_location);
                        $("#item").val(response.id);
                        if(stay_open)
                        {
                            $("#add_item_form").ajaxSubmit();
                        }
                        else
                        {
                            $("#add_item_form").submit();
                        }
                    }
                }
            }

            $('[name="price"],[name="quantity"],[name="discount"],[name="description"],[name="serialnumber"],[name="discounted_total"]').change(function() {
                $(this).parents("tr").prevAll("form:first").submit()
            });
        });

        function check_payment_type()
        {
            var cash_rounding = <?php echo json_encode($cash_rounding); ?>;

            if($("#payment_types").val() == "<?php echo $this->lang->line('sales_giftcard'); ?>")
            {
                $("#sale_total").html("<?php echo to_currency($total); ?>");
                $("#sale_amount_due").html("<?php echo to_currency($amount_due); ?>");
                $("#amount_tendered_label").html("<?php echo $this->lang->line('sales_giftcard_number'); ?>");
                $("#amount_tendered:enabled").val('').focus();
                $(".giftcard-input").attr('disabled', false);
                $(".non-giftcard-input").attr('disabled', true);
                $(".giftcard-input:enabled").val('').focus();
            }
            else if($("#payment_types").val() == "<?php echo $this->lang->line('sales_cash'); ?>" && cash_rounding)
            {
                $("#sale_total").html("<?php echo to_currency($cash_total); ?>");
                $("#sale_amount_due").html("<?php echo to_currency($cash_amount_due); ?>");
                $("#amount_tendered_label").html("<?php echo $this->lang->line('sales_amount_tendered'); ?>");
                $("#amount_tendered:enabled").val('<?php echo to_currency_no_money($cash_amount_due); ?>');
                $(".giftcard-input").attr('disabled', true);
                $(".non-giftcard-input").attr('disabled', false);
            }
            else
            {
                $("#sale_total").html("<?php echo to_currency($non_cash_total); ?>");
                $("#sale_amount_due").html("<?php echo to_currency($non_cash_amount_due); ?>");
                $("#amount_tendered_label").html("<?php echo $this->lang->line('sales_amount_tendered'); ?>");
                $("#amount_tendered:enabled").val('<?php echo to_currency_no_money($non_cash_amount_due); ?>');
                $(".giftcard-input").attr('disabled', true);
                $(".non-giftcard-input").attr('disabled', false);
            }
        }
    </script>