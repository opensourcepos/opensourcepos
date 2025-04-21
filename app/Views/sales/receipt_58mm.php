<?php
/**
 * @var string $transaction_time
 * @var int $sale_id
 * @var string $employee
 * @var float $discount
 * @var array $cart
 * @var float $subtotal
 * @var array $taxes
 * @var float $total
 * @var array $payments
 * @var float $amount_change
 * @var string $barcode
 * @var array $config
 */
?>
<div id="receipt_wrapper_58" class="receipt_wrapper_58">
    <div class="receipt">
        <div class="center_58">
        	<?php
            if($config['company_logo'] != '')
            {
            ?>
                <div id="company_name">
                    <img id="image" src="<?= base_url('uploads/' . esc($config['company_logo'], 'url')) ?>" alt="company_logo" />
                </div>
            <?php
            }
            ?>
            <strong><?= esc($config['company']) ?></strong><br>
            <?= nl2br(esc($config['address'])) ?><br>
            <?= lang('Sales.receipt') ?><br>
            <?= esc($transaction_time) ?><br>
        </div>
        <br/>
        <div id="receipt_general_info">
            <?php
            if(isset($customer))
            {
            ?>
                <div id="customer"><?= lang('Customers.customer') . esc(": $customer") ?></div>
            <?php
            }
            ?>
    
            <div id="sale_id"><?= lang('Sales.id') . esc(": $sale_id") ?></div>
    
            <?php
            if(!empty($invoice_number))
            {
            ?>
                <div id="invoice_number"><?= lang('Sales.invoice_number') . ": $invoice_number" ?></div>
            <?php
            }
            ?>
    
            <div id="employee"><?= lang('Employees.employee') . esc(": $employee") ?></div>
        </div>
		<br/>
        <table class="items_58">
            <?php foreach($cart as $item): ?>
            
            <tr>
                <td colspan="2"><?= $item['name']; ?></td>
            </tr>
            <tr>
                <td>&nbsp;&nbsp;<?= to_quantity_decimals($item['quantity']); ?> x <?= to_currency($item['price']); ?></td>
                <td style="text-align: right;"><?= to_currency($item[($config['receipt_show_total_discount'] ? 'total' : 'discounted_total')]) ?></td>
            </tr>
            <?php
            if($config['receipt_show_serialnumber'] && !empty($item['serialnumber']))
            {
            ?>
            	<tr>
                	<td>&nbsp;&nbsp;<?= esc($item['serialnumber']) ?></td>
               	</tr>
            <?php
            }
            ?>
            <?php
            if($item['discount'] > 0)
            {
            ?>
                <tr>
                    <?php
                    if($item['discount_type'] == FIXED)
                    {
                    ?>
                        <td class="discount">&nbsp;&nbsp;<?= to_currency($item['discount']) . " " . lang('Sales.discount') ?></td>
                    <?php
                    }
                    elseif($item['discount_type'] == PERCENT)
                    {
                    ?>
                        <td class="discount">&nbsp;&nbsp;<?= to_decimals($item['discount']) . " " . lang('Sales.discount_included') ?></td>
                    <?php
                    }
                    ?>
                    <td class="total-value"><?= to_currency($item['discounted_total']) ?></td>
                </tr>
            <?php
            }
            ?>
            
            <?php endforeach; ?>
        </table>

        <div class="totals_58">
        	<?php
        	if($config['receipt_show_total_discount'] && $discount > 0)
            {
            ?>
            	<div><?= lang('Sales.sub_total') ?>&nbsp;<?= to_currency($subtotal) ?></div>
            	<div><?= lang('Sales.discount') ?>: <?= to_currency($discount * -1) ?></div>
                
            <?php
            }
            ?>
       </div>
       <div class="totals_58"> 	
           	<?php
            if($config['receipt_show_taxes'])
            {
            ?>
                <div><?= lang('Sales.sub_total') ?> <?= to_currency($subtotal) ?></div>
                <?php
                foreach($taxes as $tax_group_index => $tax)
                {
                ?>
                	<div><?= (float)$tax['tax_rate'] . '% ' . $tax['tax_group'] ?>: <?= to_currency_tax($tax['sale_tax_amount']) ?></div>
                <?php
                }
                ?>
            <?php
            }
            ?>
       
       
            <div><?= lang('Sales.total') ?>: <?= to_currency($total); ?></div>
           	<?php
            $only_sale_check = false;
            $show_giftcard_remainder = false;
            foreach($payments as $payment_id => $payment)
            {
                $only_sale_check |= $payment['payment_type'] == lang('Sales.check');
                $splitpayment = explode(':', $payment['payment_type']);
                $show_giftcard_remainder |= $splitpayment[0] == lang('Sales.giftcard');
            ?>
            	<div><?= $splitpayment[0] ?>: <?= to_currency( $payment['payment_amount'] * -1 ) ?></div>
            <?php
            }
            ?>
            
            <?php
            if(isset($cur_giftcard_value) && $show_giftcard_remainder)
            {
            ?>
            	<div><?= lang('Sales.giftcard_balance') ?>: <?= to_currency($cur_giftcard_value) ?></div>
            <?php
            }
            ?>
            
            <div><?= lang('Sales.amount_due') ?>:: <?= to_currency($amount_change); ?></div>
        </div>

        <div class="center footer">
           
        </div>
    </div>
 </div>
