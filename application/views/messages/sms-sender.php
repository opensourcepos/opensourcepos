	
	<?php echo form_open('messages/send', array('id'=>'send_sms_form', 'enctype'=>'multipart/form-data', 'method'=>'post', 'class'=>'form-horizontal')); ?>
	  <fieldset><fieldset>
	    <legend></legend>
	    <div class="form-group">
	      <label for="inputName" class="col-lg-2 control-label">First Name:</label>
	      <div class="col-lg-10">
		<?php echo form_input(array( 'class'=>'form-control', 'type'=>'text','name'=>'first_name', 'readonly'=>'true', 'value'=>$person_info->first_name));?>
	    	</div>
		</div>
		<div class="form-group">
	      <label for="inputName" class="col-lg-2 control-label">Last Name:</label>
	      <div class="col-lg-10">
	      <?php echo form_input(array('class'=>'form-control', 'type'=>'text','name'=>'last_name', 'readonly'=>'true', 'value'=>$person_info->last_name));?>
	      </div>
		</div> 
		<div class="form-group">
	      <label for="inputPhone" class="col-lg-2 control-label">Mobile:</label>
	      <div class="col-lg-10">
	      <?php echo form_input(array('class'=>'form-control', 'type'=>'text','name'=>'phone', 'readonly'=>'true', 'value'=>$person_info->phone_number));?>
	      </div>
	    </div>
	    <div class="form-group">
	      <label for="textArea" class="col-lg-2 control-label">Message:</label>
	      <div class="col-lg-10">
		<textarea class="form-control" rows="3"  name="msg"><?php echo $this->config->item('msg_msg'); ?></textarea>
	      </div>
  	  </fieldset>


