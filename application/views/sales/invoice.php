<?php $this->load->view("partial/header"); ?>
<?php
if (isset($error_message))
{
	echo '<h1 style="text-align: center;">'.$error_message.'</h1>';
	exit;
}
?>

<?php $this->load->view('partial/print_receipt', array('print_after_sale', $print_after_sale, 
		'selected_printer' => 'invoice_printer')); ?>

<div id="page-wrap">
	<div id="header"><?php echo $this->lang->line('sales_invoice'); ?></div>
	<div id="block1">
		 <div id="customer-title"><?php if(isset($customer))
		{
		?>
			<textarea id="customer" rows="5" cols="6"><?=$customer_info ?></textarea>
		<?php
		}
		?></div>
		
        <div id="logo">
	        <?php if ($this->Appconfig->get('company_logo') == '') 
	        { 
	        ?>
	        <div id="company_name"><?php echo $this->config->item('company'); ?></div>
			<?php 
			}
			else 
			{ 
			?>
			<img id="image" src="<?php echo base_url('uploads/' . $this->Appconfig->get('company_logo')); ?>" alt="company_logo" />			
			<?php
			}
			?>
        </div>
	</div>
	
	<div class="clearfix"></div>
	<div id="block2">
       	<textarea id="company-title" rows="5" cols="20"><?=$company_info?></textarea>
        <table id="meta">
            <tr>
                <td class="meta-head"><?php echo $this->lang->line('sales_invoice_number');?> </td>
                <td><textarea rows="5" cols="6"><?php echo $invoice_number; ?></textarea></td>
            </tr>
            <tr>
                <td class="meta-head"><?php echo $this->lang->line('common_date'); ?></td>
                <td><textarea rows="5" cols="6"><?php echo $transaction_date; ?></textarea></td>
            </tr>
            
            <tr>
                <td class="meta-head"><?php echo $this->lang->line('sales_amount_due'); ?></td>
                <td><textarea rows="5" cols="6"><?php echo to_currency($total); ?></textarea></td>
            </tr>
			
        </table>
	</div>

	<table id="items">
	  <tr>
	      <th><?php echo $this->lang->line('sales_item_number'); ?></th>
	      <th><?php echo $this->lang->line('sales_item_name'); ?></th>
	      <th><?php echo $this->lang->line('sales_quantity'); ?></th>
	      <th><?php echo $this->lang->line('sales_price'); ?></th>
	      <th><?php echo $this->lang->line('sales_discount'); ?></th>
	      <th><?php echo $this->lang->line('sales_total'); ?></th>
	  </tr>
	  
	<?php
		foreach($cart as $line=>$item)
		{
		?>
			<tr class="item-row">
				<td><?php echo $item['item_number']; ?></td>
				<td class="item-name"><textarea rows="5" cols="6" class='long_name'><?php echo $item['is_serialized'] || $item['allow_alt_description'] && !empty($item['description']) ? base64_decode($item['description']) : base64_decode($item['name']); ?></textarea></td>
				<td style='text-align:center;'><textarea rows="5" cols="6"><?php echo $item['quantity']; ?></textarea></td>
				<td><textarea rows="5" cols="6"><?php echo to_currency($item['price']); ?></textarea></td>
				<td style='text-align:center;'><textarea rows="5" cols="6"><?php echo $item['discount'] .'%'; ?></textarea></td>
				<td style='border-right: solid 1px; text-align:right;'><textarea rows="5" cols="6"><?php echo to_currency($item['discounted_total']); ?></textarea></td>
			</tr>
		<?php
		}
		?>
		<tr>
			<td class="blank" colspan="6" align="center"><?php echo '&nbsp;'; ?></td>
		</tr>
		     
	  <tr>
	      <td colspan="3" class="blank-bottom"> </td>
	      <td colspan="2" class="total-line"><textarea rows="5" cols="6"><?php echo $this->lang->line('sales_sub_total'); ?></textarea></td>
	      <td class="total-value"><textarea rows="5" cols="6" id="subtotal"><?php echo to_currency($tax_exclusive_subtotal); ?></textarea></td>
	  </tr>
	  <?php foreach($taxes as $name=>$value) { ?>
	  <tr>
	      <td colspan="3" class="blank"> </td>
	      <td colspan="2" class="total-line"><textarea rows="5" cols="6"><?php echo $name; ?>:</textarea></td>
	      <td class="total-value"><textarea rows="5" cols="6" id="taxes"><?php echo to_currency($value); ?></textarea></td>
	  </tr>
      <?php }; ?>
	  <tr>
	      <td colspan="3" class="blank"> </td>
	      <td colspan="2" class="total-line"><textarea rows="5" cols="6"><?php echo $this->lang->line('sales_total'); ?></textarea></td>
	      <td class="total-value"><textarea rows="5" cols="6" id="total"><?php echo to_currency($total); ?></textarea></td>
	  </tr>
	</table>
	
	<div id="terms">
		<div id="sale_return_policy">
		 	<h5>
			 	<textarea rows="5" cols="6"><?php echo nl2br($this->config->item('payment_message')); ?></textarea>
			  	<textarea rows="5" cols="6"><?php echo $this->lang->line('sales_comments'). ': ' . (empty($comments) ? $this->config->item('invoice_default_comments') : $comments); ?></textarea>
		  	</h5>
			<?php echo nl2br($this->config->item('return_policy')); ?>
		</div>
		<div id='barcode'>
			<img src='data:image/png;base64,<?php echo $barcode; ?>' /><br>
			<?php echo $sale_id; ?>
		</div>
	</div>
</div>

<script type="text/javascript">
$(window).load(function()
{
	// install firefox addon in order to use this plugin
	if (window.jsPrintSetup) 
	{
		<?php if (!$this->Appconfig->get('print_header'))
		{
		?>
		// set page header
		jsPrintSetup.setOption('headerStrLeft', '');
		jsPrintSetup.setOption('headerStrCenter', '');
		jsPrintSetup.setOption('headerStrRight', '');
		<?php 
		}
		if (!$this->Appconfig->get('print_footer'))
		{
		?>
		// set empty page footer
		jsPrintSetup.setOption('footerStrLeft', '');
		jsPrintSetup.setOption('footerStrCenter', '');
		jsPrintSetup.setOption('footerStrRight', '');
		<?php 
		} 
		?>
	}
});
</script>

<?php $this->load->view("partial/footer"); ?>
