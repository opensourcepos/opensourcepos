<?php
/**
 * Table Filter Persistence
 *
 * This partially updates the URL when filters change, allowing users to
 * share/bookmark filtered views and maintain state on back navigation.
 *
 * Filter restoration from URL is handled server-side in the controller.
 *
 * @param array $options Additional filter options
 *   - 'additional_params': Array of additional parameter names to track (e.g., ['stock_location'])
 *   - 'filter_select_id': Filter multiselect element ID (default: 'filters')
 */
$options = $options ?? [];
$additional_params = $options['additional_params'] ?? [];
$filter_select_id = $options['filter_select_id'] ?? 'filters';
?>

<script type="text/javascript">
    $(document).ready(function() {
        const additional_params = <?= json_encode($additional_params) ?>;
        const filter_select_id = '<?= esc($filter_select_id) ?>';

        function get_date_range() {
            let sDate = null;
            let eDate = null;

            if (typeof table_support !== 'undefined' && typeof table_support.query_params === 'function') {
                const qp = table_support.query_params();
                if (qp && qp.start_date) {
                    sDate = qp.start_date;
                }
                if (qp && qp.end_date) {
                    eDate = qp.end_date;
                }
            }

            if (!sDate && typeof start_date !== 'undefined') {
                sDate = start_date;
            }
            if (!eDate && typeof end_date !== 'undefined') {
                eDate = end_date;
            }

            if ((!sDate || !eDate) && $('#daterangepicker').length && $('#daterangepicker').data('daterangepicker')) {
                const picker = $('#daterangepicker').data('daterangepicker');
                if (picker.startDate && picker.endDate) {
                    const format = picker.timePicker ? 'YYYY-MM-DD HH:mm:ss' : 'YYYY-MM-DD';
                    if (!sDate) {
                        sDate = picker.startDate.format(format);
                    }
                    if (!eDate) {
                        eDate = picker.endDate.format(format);
                    }
                }
            }

            if (!sDate || !eDate) {
                const urlParams = new URLSearchParams(window.location.search);
                if (!sDate && urlParams.has('start_date')) {
                    sDate = urlParams.get('start_date');
                }
                if (!eDate && urlParams.has('end_date')) {
                    eDate = urlParams.get('end_date');
                }
            }

            return { start_date: sDate, end_date: eDate };
        }

        function update_url() {
            const params = new URLSearchParams();

            // Add dates
            const dates = get_date_range();
            if (dates.start_date) {
                params.set('start_date', dates.start_date);
            }
            if (dates.end_date) {
                params.set('end_date', dates.end_date);
            }

            // Add filters
            const filters = $('#' + filter_select_id).val();
            if (filters) {
                filters.forEach(function(filter) {
                    params.append('filters[]', filter);
                });
            }

            // Add additional params
            additional_params.forEach(function(param) {
                const element = $('#' + param);
                if (element.length) {
                    const value = element.val();
                    if (Array.isArray(value) && value.length > 0) {
                        value.forEach(function(v) {
                            params.append(param + '[]', v);
                        });
                    } else if (value) {
                        params.set(param, value);
                    }
                }
            });

            // Update URL without page reload
            let new_url = window.location.pathname;
            const params_str = params.toString();
            if (params_str) {
                new_url += '?' + params_str;
            }
            window.history.replaceState({}, '', new_url);
        }

        // Update URL when filter dropdown changes
        $('#' + filter_select_id).on('hidden.bs.select', function(e) {
            update_url();
        });

        // Update URL when stock location changes (if exists)
        if ($('#stock_location').length) {
            $("#stock_location").change(function() {
                update_url();
            });
        }

        // Update URL when daterangepicker changes
        $("#daterangepicker").on('apply.daterangepicker', function(ev, picker) {
            update_url();
        });
    });
</script>
