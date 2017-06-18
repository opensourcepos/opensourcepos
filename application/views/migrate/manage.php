<?php $this->load->view("partial/header"); ?>

<?php echo form_open("migrate/perform_migration/", array('id'=>'start_migration_form','method'=>'post', 'class'=>'form-horizontal')); ?>
<fieldset>

    <div class="form-group form-group-sm">

        <div class='col-xs-12'>
            <strong><?php echo $this->lang->line('migrate_info'); ?></strong>
        </div>
    </div>

    <div class="form-group form-group-sm">
        <div class='col-xs-12'>
        	<strong><?php echo $this->lang->line('migrate_backup'); ?></strong>
        </div>
    </div>


	<?php echo form_submit(array(
		'name'=>'submit_form',
		'id'=>'submit_form',
		'value'=>$this->lang->line('migrate_start'),
		'class'=>'btn btn-primary btn-sm pull-right'));?>
</fieldset>
<?php echo form_close(); ?>

<?php $this->load->view("partial/footer"); ?>

<script type="text/javascript">
    //validation and submit handling
    $(document).ready(function()
    {
        $('#start_migration_form').validate({
            submitHandler: function(form) {
                $(form).ajaxSubmit({
                    success: function(response)	{
                        $.notify(response.message, { type: response.success ? 'success' : 'danger'} );
                    },
                    dataType: 'json'
                });
            }
        });
    });
</script>
