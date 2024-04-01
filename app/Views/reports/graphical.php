<?php
/**
 * @var string $title
 * @var string $subtitle
 * @var string $chart_type
 * @var array $summary_data_1
 */
?>
<?= view('partial/header') ?>

<script type="application/javascript">
	dialog_support.init("a.modal-dlg");
</script>

<div id="page_title"><?= esc($title) ?></div>

<div id="page_subtitle"><?= esc($subtitle) ?></div>

<div class="ct-chart ct-golden-section" id="chart1"></div>

<?= view($chart_type) ?>

<div id="chart_report_summary">
	<?php
	foreach($summary_data_1 as $name => $value)
	{
	?>
		<div class="summary_row"><?= lang("Reports.$name"). ': ' . to_currency($value) ?></div>
	<?php
	}
	?>
</div>

<?= view('partial/footer') ?>
