<?php
/**
 * Table Filter Persistence
 * 
 * This partial adds URL query string support for table filters.
 * It restores filters from URL on page load and updates URL when filters change,
 * allowing users to navigate away and back without losing filter state.
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
        var url_params = new URLSearchParams(window.location.search);
        var additional_params = <?= json_encode($additional_params) ?>;
        var filter_select_id = '<?= esc($filter_select_id) ?>';
        
        // Restore start_date and end_date from URL
        if (url_params.has('start_date')) {
            start_date = url_params.get('start_date');
        }
        if (url_params.has('end_date')) {
            end_date = url_params.get('end_date');
        }
        
        // Restore additional params from URL
        var restored_params = {};
        additional_params.forEach(function(param) {
            var values = url_params.getAll(param + '[]');
            if (values.length > 0) {
                restored_params[param] = values;
            } else if (url_params.has(param)) {
                restored_params[param] = url_params.get(param);
            }
        });
        
        // Restore filters[] from URL
        var url_filters = url_params.getAll('filters[]');
        
        // Define update_url function first
        function update_url() {
            var params = new URLSearchParams();
            
            // Add dates
            if (typeof start_date !== 'undefined') {
                params.set('start_date', start_date);
            }
            if (typeof end_date !== 'undefined') {
                params.set('end_date', end_date);
            }
            
            // Add filters
            var filters = $('#' + filter_select_id).val();
            if (filters) {
                filters.forEach(function(filter) {
                    params.append('filters[]', filter);
                });
            }
            
            // Add additional params
            additional_params.forEach(function(param) {
                var element = $('#' + param);
                if (element.length) {
                    var value = element.val();
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
            var new_url = window.location.pathname;
            var params_str = params.toString();
            if (params_str) {
                new_url += '?' + params_str;
            }
            window.history.replaceState({}, '', new_url);
        }
        
        // Update URL when filter dropdown changes
        $('#' + filter_select_id).on('hidden.bs.select', function(e) {
            update_url();
            table_support.refresh();
        });
        
        // Update URL when stock location changes (if exists)
        if ($('#stock_location').length) {
            $("#stock_location").change(function() {
                update_url();
                table_support.refresh();
            });
        }
        
        // Update URL when daterangepicker changes
        $("#daterangepicker").on('apply.daterangepicker', function(ev, picker) {
            update_url();
            table_support.refresh();
        });
        
        // Initialize filters from URL after all components are loaded
        setTimeout(function() {
            // Restore daterangepicker dates
            if (url_params.has('start_date') || url_params.has('end_date')) {
                var daterangepicker = $('#daterangepicker').data('daterangepicker');
                if (daterangepicker) {
                    if (url_params.has('start_date')) {
                        daterangepicker.setStartDate(moment(start_date));
                    }
                    if (url_params.has('end_date')) {
                        daterangepicker.setEndDate(moment(end_date));
                    }
                }
            }
            
            // Restore filter multiselect values
            if (url_filters.length > 0) {
                $('#' + filter_select_id).selectpicker('val', url_filters);
            }
            
            // Restore additional params
            additional_params.forEach(function(param) {
                if (restored_params[param] !== undefined) {
                    var element;
                    if (Array.isArray(restored_params[param])) {
                        element = $('#' + param).val(restored_params[param]);
                    } else {
                        element = $('#' + param).val(restored_params[param]);
                    }
                    if (element && element.data('selectpicker')) {
                        element.selectpicker('refresh');
                    }
                }
            });
            
            // Refresh table with restored filters
            table_support.refresh();
        }, 100);
    });
</script>