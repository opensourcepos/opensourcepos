<?= form_open_multipart('items/importCsvFile/', ['id' => 'csv_form']) ?>

    <ul id="error_message_box" class="alert alert-warning d-none"></ul>

    <a type="button" class="btn btn-secondary mb-3" href="<?= esc('items/generateCsvFile', 'attr') ?>"><?= lang('Common.download_import_template') ?></a>

    <div class="fileinput fileinput-new input-group mb-3" data-provides="fileinput">
        <span class="input-group-text"><i class="bi bi-filetype-csv"></i></span>
        <div class="form-control" data-trigger="fileinput">
            <span class="fileinput-filename"></span>
        </div>
        <span class="input-group-text fileinput-exists" data-dismiss="fileinput" style="cursor: pointer;"><?= lang('Common.import_remove_file') ?></span>
        <span class="input-group-text btn-file">
            <span class="fileinput-new"><?= lang('Common.import_select_file') ?></span>
            <span class="fileinput-exists"><?= lang('Common.import_change_file') ?></span>
            <input type="file" id="file_path" name="file_path" accept=".csv">
        </span>
    </div>

<?= form_close() ?>

<script type="text/javascript">
    // Validation and submit handling
    $(document).ready(function() {
        $('#csv_form').validate($.extend({
            submitHandler: function(form) {
                $(form).ajaxSubmit({
                    success: function(response) {
                        dialog_support.hide();
                        table_support.handle_submit('<?= esc('items') ?>', response);
                    },
                    dataType: 'json'
                });
            },

            errorLabelContainer: '#error_message_box',

            rules: {
                file_path: 'required'
            },

            messages: {
                file_path: "<?= lang('Common.import_full_path') ?>"
            }
        }, form_support.error));
    });
</script>
