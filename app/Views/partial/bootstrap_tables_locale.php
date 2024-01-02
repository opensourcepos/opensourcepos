<?php
/**
 * @var string $controller_name
 */
?>
(function ($) {
	'use strict';

	$.fn.bootstrapTable.locales['<?= current_language_code() ?>'] = {
		formatLoadingMessage: function () {
			return "<?= lang('Bootstrap_tables.loading') ?>";
		},
		formatRecordsPerPage: function (pageNumber) {
			return "<?= lang('Bootstrap_tables.rows_per_page') ?>".replace('{0}', pageNumber);
		},
		formatShowingRows: function (pageFrom, pageTo, totalRows) {
			return "<?= lang('Bootstrap_tables.page_from_to') ?>".replace('{0}', pageFrom).replace('{1}', pageTo).replace('{2}', totalRows);
		},
		formatSearch: function () {
			return "<?= lang('Common.search') ?>";
		},
		formatNoMatches: function () {
			return "<?= lang(preg_match('(customers|suppliers|employees)', $controller_name)
				? 'Common.no_persons_to_display'
				: ucfirst($controller_name) . '.no_' . $controller_name . '_to_display')
			?>";
		},
		formatPaginationSwitch: function () {
			return "<?= lang('Bootstrap_tables.hide_show_pagination') ?>";
		},
		formatRefresh: function () {
			return "<?= lang('Bootstrap_tables.refresh') ?>";
		},
		formatToggle: function () {
			return "<?= lang('Bootstrap_tables.toggle') ?>";
		},
		formatColumns: function () {
			return "<?= lang('Bootstrap_tables.columns') ?>";
		},
		formatAllRows: function () {
			return "<?= lang('Bootstrap_tables.all') ?>";
		},
		formatConfirmAction: function(action) {
			if (action == "delete")
			{
				return "<?= lang(ucfirst($editable ?? $controller_name). '.confirm_delete') ?>";
			}
			else
			{
				return "<?= lang(ucfirst($editable ?? $controller_name). '.confirm_restore') ?>";
			}
        }
	};

	$.extend($.fn.bootstrapTable.defaults, $.fn.bootstrapTable.locales["<?= current_language_code() ?>"]);

})(jQuery);
