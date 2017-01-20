<script type="text/javascript">
function printdoc()
{
	// install firefox addon in order to use this plugin
	if (window.jsPrintSetup)
	{
		// set top margins in millimeters
		jsPrintSetup.setOption('marginTop', '<?php echo $this->config->item('print_top_margin'); ?>');
		jsPrintSetup.setOption('marginLeft', '<?php echo $this->config->item('print_left_margin'); ?>');
		jsPrintSetup.setOption('marginBottom', '<?php echo $this->config->item('print_bottom_margin'); ?>');
		jsPrintSetup.setOption('marginRight', '<?php echo $this->config->item('print_right_margin'); ?>');

		<?php if (!$this->config->item('print_header'))
		{
		?>
			// set page header
			jsPrintSetup.setOption('headerStrLeft', '');
			jsPrintSetup.setOption('headerStrCenter', '');
			jsPrintSetup.setOption('headerStrRight', '');
		<?php
		}
		if (!$this->config->item('print_footer'))
		{
		?>
			// set empty page footer
			jsPrintSetup.setOption('footerStrLeft', '');
			jsPrintSetup.setOption('footerStrCenter', '');
			jsPrintSetup.setOption('footerStrRight', '');
		<?php
		}
		?>

		var printers = jsPrintSetup.getPrintersList().split(',');
		// get right printer here..
		for(var index in printers) {
			var default_ticket_printer = window.localStorage && localStorage['<?php echo $selected_printer; ?>'];
			var selected_printer = printers[index];
			if (selected_printer == default_ticket_printer) {
				// select epson label printer
				jsPrintSetup.setPrinter(selected_printer);
				// clears user preferences always silent print value
				// to enable using 'printSilent' option
				jsPrintSetup.clearSilentPrint();
				<?php if (!$this->config->item('print_silently'))
				{
				?>
					// Suppress print dialog (for this context only)
					jsPrintSetup.setOption('printSilent', 1);
				<?php
				}
				?>
				// Do Print
				// When print is submitted it is executed asynchronous and
				// script flow continues after print independently of completetion of print process!
				jsPrintSetup.print();
			}
		}
	}
	else
	{
		window.print();
	}
}

<?php 
if($print_after_sale)
{
?>
	$(window).load(function() 
	{
	   // executes when complete page is fully loaded, including all frames, objects and images
	   printdoc();
	}); 
<?php
}
?>
</script>