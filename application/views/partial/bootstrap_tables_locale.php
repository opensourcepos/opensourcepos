(function ($) {
    'use strict';

    $.fn.bootstrapTable.locales['<?php echo $this->config->item('language'); ?>'] = {
    formatLoadingMessage: function () {
        return '<?php echo $this->lang->line('tables_loading');?>';
    },
    formatRecordsPerPage: function (pageNumber) {
        return '<?php echo $this->lang->line('tables_rows_per_page'); ?>'.replace('{0}', pageNumber);
    },
    formatShowingRows: function (pageFrom, pageTo, totalRows) {
        return '<?php echo $this->lang->line('tables_page_from_to'); ?>'.replace('{0}', pageFrom).replace('{1}', pageTo).replace('{2}', totalRows);
    },
    formatSearch: function () {
        return '<?php echo $this->lang->line('common_search'); ?>';
    },
    formatNoMatches: function () {
        return '<?php echo $this->lang->line($controller_name . '_no_' . $controller_name .'_to_display'); ?>';
    },
    formatPaginationSwitch: function () {
        return '<?php echo $this->lang->line('tables_hide_show_pagination'); ?>';
    },
    formatRefresh: function () {
        return '<?php echo $this->lang->line('tables_refresh'); ?>';
    },
    formatToggle: function () {
        return '<?php echo $this->lang->line('tables_toggle'); ?>';
    },
    formatColumns: function () {
        return '<?php echo $this->lang->line('tables_columns'); ?>';
    },
    formatAllRows: function () {
        return '<?php echo $this->lang->line('tables_all'); ?>';
    }
    };

    $.extend($.fn.bootstrapTable.defaults, $.fn.bootstrapTable.locales['<?php echo $this->config->item('language');?>']);

})(jQuery);