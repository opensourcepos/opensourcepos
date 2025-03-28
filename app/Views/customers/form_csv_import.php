<ul id="error_message_box" class="error_message_box"></ul>

<?= form_open_multipart('customers/importCsvFile/', ['id' => 'csv_form', 'class' => 'form-horizontal']) ?>
    <fieldset id="item_basic_info">
        <div class="form-group form-group-sm">
            <div class="col-xs-12">
                <a href="<?= esc('customers/csv') ?>"><?= lang('Common.download_import_template') ?></a>
            </div>
        </div>

        <div class="form-group form-group-sm">
            <div class='col-xs-12'>
                <div class="fileinput fileinput-new input-group" data-provides="fileinput">
                    <div class="form-control" data-trigger="fileinput"><i class="glyphicon glyphicon-file fileinput-exists"></i><span class="fileinput-filename"></span></div>
                    <span class="input-group-addon input-sm btn btn-default btn-file"><span class="fileinput-new"><?= lang('Common.import_select_file') ?></span><span class="fileinput-exists"><?= lang('Common.import_change_file') ?></span><input type="file" id="file_path" name="file_path" accept=".csv"></span>
                    <a href="#" class="input-group-addon input-sm btn btn-default fileinput-exists" data-dismiss="fileinput"><?= lang('Common.import_remove_file') ?></a>
                </div>
            </div>
        </div>
    </fieldset>
<?= form_close() ?>

<script type="application/javascript">
//validation and submit handling
$(document).ready(function()
{
    $('#csv_form').validate($.extend({
        submitHandler: function(form) {
            $(form).ajaxSubmit({
                success: function(response)
                {
                    dialog_support.hide();
                    table_support.handle_submit('<?= esc('customers') ?>', response);
                },
                dataType: 'json'
            });
        },

        errorLabelContainer: '#error_message_box',

        rules:
        {
            file_path: 'required'
           },

        messages:
        {
               file_path: "<?= lang('Common.import_full_path') ?>"
        }
    }, form_support.error));
});
</script>
