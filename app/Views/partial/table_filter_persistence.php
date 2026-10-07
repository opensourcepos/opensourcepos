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

        function update_url() {
            const params = new URLSearchParams();

            // Add dates
            if (typeof start_date !== 'undefined') {
                params.set('start_date', start_date);
            }
            if (typeof end_date !== 'undefined') {
                params.set('end_date', end_date);
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
            const new_url = window.location.pathname;
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
