<?php
/**
 * @var string $title
 * @var string $subtitle
 * @var array $overall_summary_data
 * @var array $details_data
 * @var array $headers
 * @var array $summary_data
 * @var array $config
 */
?>
<?= view('partial/header') ?>

<div id="page_title"><?= esc($title) ?></div>

<div id="page_subtitle"><?= esc($subtitle) ?></div>

<div id="table_holder">
	<table id="table"></table>
</div>

<div id="report_summary">
	<?php
		foreach($overall_summary_data as $name => $value)
		{
		?>
			<div class="summary_row"><?= lang("Reports.$name") . ': ' . to_currency($value) ?></div>
		<?php
		}
	?>
</div>

<script type="text/javascript">
	$(document).ready(function()
	{
	 	<?= view('partial/bootstrap_tables_locale') ?>

		var details_data = <?= json_encode(esc($details_data, 'js')) ?>;
		<?php
		if($config['customer_reward_enable'] && !empty($details_data_rewards))
		{
		?>
			var details_data_rewards = <?= json_encode(esc($details_data_rewards, 'js')) ?>;
		<?php
		}
		?>
		var init_dialog = function() {
			<?php
			if(isset($editable))
			{
			?>
				table_support.submit_handler('<?= esc(site_url("reports/get_detailed_$editable" . '_row'), 'url') ?>');
				dialog_support.init("a.modal-dlg");
			<?php
			}
			?>
		};

		$('#table')
			.addClass("table-striped")
			.addClass("table-bordered")
			.bootstrapTable({
				columns: <?= transform_headers(esc($headers['summary'], 'js'), true) ?>,
				stickyHeader: true,
				stickyHeaderOffsetLeft: $('#table').offset().left + 'px',
				stickyHeaderOffsetRight: $('#table').offset().right + 'px',
				pageSize: <?= $config['lines_per_page'] ?>,
				pagination: true,
				sortable: true,
				showColumns: true,
				uniqueId: 'id',
				showExport: true,
				exportDataType: 'all',
				exportTypes: ['json', 'xml', 'csv', 'txt', 'sql', 'excel', 'xlsx', 'pdf'],
				data: <?= json_encode(esc($summary_data, 'js')) ?>,
				iconSize: 'sm',
				paginationVAlign: 'bottom',
				detailView: true,
				escape: false,
				search: true,
				onPageChange: init_dialog,
				onPostBody: function() {
					dialog_support.init("a.modal-dlg");
				},
				onExpandRow: function (index, row, $detail) {
					$detail.html('<table></table>').find("table").bootstrapTable({
						columns: <?= transform_headers_readonly(esc($headers['details'], 'js')) ?>,
						data: details_data[(!isNaN(row.id) && row.id) || $(row[0] || row.id).text().replace(/(POS|RECV)\s*/g, '')]
					});

					<?php
					if($config['customer_reward_enable'] && !empty($details_data_rewards))
					{
					?>
						$detail.append('<table></table>').find("table").bootstrapTable({
							columns: <?= transform_headers_readonly(esc($headers['details_rewards'], 'js')) ?>,
							data: details_data_rewards[(!isNaN(row.id) && row.id) || $(row[0] || row.id).text().replace(/(POS|RECV)\s*/g, '')]
						});
					<?php
					}
					?>
				}
		});

		init_dialog();
	});
</script>

<?= view('partial/footer') ?>
