<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
	dialog_support.init("a.modal-dlg");
</script>

<ul class="nav nav-tabs" data-tabs="tabs">
	<li class="active" role="presentation">
		<a data-toggle="tab" href="#tax_codes_tab" title="<?php echo $this->lang->line('taxes_tax_codes_configuration'); ?>"><?php echo $this->lang->line('taxes_tax_codes'); ?></a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#tax_jurisdictions_tab" title="<?php echo $this->lang->line('taxes_tax_jurisdictions_configuration'); ?>"><?php echo $this->lang->line('taxes_tax_jurisdictions'); ?></a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#tax_categories_tab" title="<?php echo $this->lang->line('taxes_tax_categories_configuration'); ?>"><?php echo $this->lang->line('taxes_tax_categories'); ?></a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#tax_rates_tab" title="<?php echo $this->lang->line('taxes_tax_rate_configuration'); ?>"><?php echo $this->lang->line('taxes_tax_rates'); ?></a>
	</li>
</ul>

<div class="tab-content">
	<div class="tab-pane fade in active" id="tax_codes_tab">
		<?php $this->load->view("taxes/tax_codes"); ?>
	</div>
	<div class="tab-pane" id="tax_jurisdictions_tab">
		<?php $this->load->view("taxes/tax_jurisdictions"); ?>
	</div>
	<div class="tab-pane" id="tax_categories_tab">
		<?php $this->load->view("taxes/tax_categories"); ?>
	</div>
	<div class="tab-pane" id="tax_rates_tab">
		<?php $this->load->view("taxes/tax_rates"); ?>
	</div>
</div>

<?php $this->load->view("partial/footer"); ?>
