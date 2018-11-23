<div id="page_title"><?php echo $this->lang->line('config_serial_configuration'); ?></div>
<?php
echo form_open('config/save_units/',array('id'=>'serial_config_form'));
?>
    <div id="config_wrapper">
        <fieldset id="config_info">
            <div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>
            <ul id="serial_error_message_box" class="error_message_box"></ul>
            <legend><?php echo $this->lang->line("config_unit_info"); ?></legend>

			<div class="field_row clearfix">
				<?php echo form_label($this->lang->line('config_serialport_server_url').':', 'config_serialport_server_url',array('class'=>'wide')); ?>
				<div class='form_field'>
					<?php echo form_input(array(
						'name'=>'serialport_server_url',
						'id'=>'serialport_server_url',
						'value'=>'ws://localhost:8989/ws',
						'type'=>'text')); ?>
					<input type="button" id="config_serialport_connect" value="<?php echo $this->lang->line('config_serialport_connect'); ?>" />
				</div>

			</div>

			<div class="field_row hidden">
				<?php echo form_label($this->lang->line('config_serialport_picker').':', 'config_serialport_picker',array('class'=>'wide')); ?>
				<div class='form_field'>
					<?php echo form_dropdown('input_device_picker', array(), ' ','id="input_device_picker"');?>
					<?php echo form_dropdown('baud_rate_picker', array(), ' ','id="baud_rate_picker"');?>
					<?php
						foreach($item_units as $unit_id => $unit_data )
						{
							if (!empty($unit_data['unit_name']))
							{
								?>
								<input type="checkbox" id="input_device_<?php echo $unit_data['unit_id']; ?>"
									   class="input_device_check"/>
								<?php echo $unit_data['unit_name'];
							}
						}
						?>
				</div>
			</div>

            <?php 
            echo form_submit(array(
                'name'=>'submit',
                'id'=>'submit',
                'value'=>$this->lang->line('common_submit'),
                'class'=>'submit_button float_right')
            );
            ?>
        </fieldset>
    </div>
<?php
echo form_close();
?>


<script type='text/javascript'>

//validation and submit handling
$(document).ready(function()
{
	var unit_count = <?php echo sizeof($item_units); ?>;

	$("#config_serialport_connect").prop("disabled", !$("#serialport_server_url").val().length);
	$("#serialport_server_url").on('keyup', function() {
		$("#config_serialport_connect").prop("disabled", !this.value.length);
	});

	if (window['localStorage'])
	{

		var url = serial_config.load_url();
		$("#config_serialport_connect").click(function()
		{
			url = serial_config.save_url();
			try {
				var websocket = new WebSocket(url);

				websocket.onopen = function(data) {
					websocket.send("list");
					websocket.send("baudrates");
				};

				var callbacks = 0;
				websocket.onmessage = function(event)
				{
					try {
						if (event.data.match(/.*(BaudRate|SerialPorts).*/g))
						{
							var data = JSON.parse(event.data);
							callbacks++;
							if (data.BaudRate)
							{
								serial_config.parse_baud_rates(data.BaudRate);
							}
							if (data.SerialPorts)
							{
								serial_config.parse_ports(data.SerialPorts);

								$(".input_device_check").click(serial_config.save_serial_settings);
								$("#baud_rate_picker").change(serial_config.save_serial_settings);
								$("#input_device_picker").change(serial_config.load_serial_settings);
								serial_config.load_configured_device();
							}
							callbacks > 1 && websocket.close();
						}
					} catch(e) {
						console.log(e);
					}
				};
			} catch(e) {
				alert(e);
			}

		});

	}

	$('#serial_config_form').validate({
		submitHandler:function(form)
		{
			$(form).ajaxSubmit({
			success:function(response)
			{
				if(response.success)
				{
					set_feedback(response.message,'success_message',false);		
				}
				else
				{
					set_feedback(response.message,'error_message',true);		
				}
			},
			dataType:'json'
		});

		},
		errorLabelContainer: "#serial_error_message_box",
 		wrapper: "li",
		rules: 
		{
   		},
		messages: 
		{
		}
	});
});
</script>
