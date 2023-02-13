<?php
/**
 * @var int $sale_id_num
 * @var bool $print_after_sale
 */

use App\Models\Employee;

?>
<?php echo view('partial/header') ?>

<?php
if (isset($error_message))
{
	echo "<div class='alert alert-dismissible alert-danger'>$error_message</div>";
	exit;
}
?>

<?php if(!empty($customer_email)): ?>
	<script type="text/javascript">
	$(document).ready(function()
	{
		var send_email = function()
		{
			$.get('<?php echo site_url() . esc("/sales/send_receipt/$sale_id_num", 'url') ?>',
				function(response)
				{
					$.notify( { message: response.message }, { type: response.success ? 'success' : 'danger'} )
				}, 'json'
			);
		};

		$("#show_email_button").click(send_email);

		<?php if(!empty($email_receipt)): ?>
			send_email();
		<?php endif; ?>
	});
	</script>
<?php endif; ?>

<?php echo view('partial/print_receipt', ['print_after_sale' => $print_after_sale, 'selected_printer' => 'receipt_printer']) ?>

<div class="print_hide" id="control_buttons" style="text-align:right">
	<a href="javascript:printdoc();"><div class="btn btn-info btn-sm" id="show_print_button"><?php echo '<span class="glyphicon glyphicon-print">&nbsp</span>' . lang('Common.print') ?></div></a>
	<?php if(!empty($customer_email)): ?>
		<a href="javascript:void(0);"><div class="btn btn-info btn-sm" id="show_email_button"><?php echo '<span class="glyphicon glyphicon-envelope">&nbsp</span>' . lang('Sales.send_receipt') ?></div></a>
	<?php endif; ?>
	<?php echo anchor('sales', '<span class="glyphicon glyphicon-shopping-cart">&nbsp</span>' . lang('Sales.register'), ['class' => 'btn btn-info btn-sm', 'id' => 'show_sales_button']) ?>
	<?php
		$employee = model(Employee::class);
		if($employee->has_grant('reports_sales', session('person_id'))): ?>
		<?php echo anchor('sales/manage', '<span class=\'glyphicon glyphicon-list-alt\'>&nbsp</span>' . lang('Sales.takings'), ['class' => 'btn btn-info btn-sm', 'id' => 'show_takings_button']) ?>
	<?php endif; ?>
</div>

<?php echo view('sales/' . $config['receipt_template']) ?>

<?php echo view('partial/footer') ?>
