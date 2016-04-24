<?php
/**
* Change the  variables 'uid', 'pwd', 'phone' and 'msg' according to your sms-api on line no. 55 & 56 and in the form-field names below.
* Change the '$res = sendsms' part (on line no. 56) according to your 'sms-api.php'
* Put your 'sms-api.php' file in views/messages folder and change the file name below (on line no. 8) accordingly.
**/
?>
<?php include_once('sms-api.php'); ?>
	        <!------------------------------------------- BOOTSTRAP HORIZONTAL FORM --------------------------------------------->
	

	<?php echo form_open('messages/', array('id'=>'send_sms_form', 'enctype'=>'multipart/form-data', 'class'=>'form-horizontal')); ?>
	  <fieldset><fieldset>
	    <legend></legend>
	    <div class="form-group">
	      <label for="inputName" class="col-lg-2 control-label">First Name:</label>
	      <div class="col-lg-10">
		<?php echo form_input(array( 'class'=>'form-control', 'type'=>'text','name'=>'first_name', 'value'=>$person_info->first_name));?>
	    	</div>
		</div>
		<div class="form-group">
	      <label for="inputName" class="col-lg-2 control-label">Last Name:</label>
	      <div class="col-lg-10">
	      <?php echo form_input(array('class'=>'form-control', 'type'=>'text','name'=>'last_name', 'value'=>$person_info->last_name));?>
	      </div>
		</div> 
		
		  <!--------  HIDE 'USER-ID' & 'PASSWORD FOR' 'SMS-API'   ------------------->
		<input type="hidden" class="form-control" name="uid" value="<?php echo $this->config->item('msg_uid');?>">
		<input type="hidden" class="form-control" name="pwd" value="<?php echo $this->config->item('msg_pwd');?>">
		  <!------------------------------------------------------------------------->
		
	    <div class="form-group">
	      <label for="inputPhone" class="col-lg-2 control-label">Mobile:</label>
	      <div class="col-lg-10">
	      <?php echo form_input(array('class'=>'form-control', 'type'=>'text','name'=>'phone', 'value'=>$person_info->phone_number));?>
	      </div>
	    </div>
	    <div class="form-group">
	      <label for="textArea" class="col-lg-2 control-label">Message:</label>
	      <div class="col-lg-10">
		<textarea class="form-control" rows="3"  name="msg"><?php echo $this->config->item('msg_msg'); ?></textarea>
		<span class="help-block"style="text-align: center;">(Maximum 140 characters)</span>
	      </div>
  	  </fieldset>
	</form>



			<!---------------------------- END OF FORM ------------------------->


<?php

	if (isset($_POST['uid']) && isset($_POST['pwd']) && isset($_POST['phone']) && isset($_POST['msg'])) {
	    $res = sendsms($_POST['uid'], $_POST['pwd'], $_POST['phone'], $_POST['msg']);
	    if (is_array($res))
		echo $res[0]['result'] ? 'Message Sent successfully' : 'Message Not Sent';
	    else
		echo $res;
	    exit;
	}

       ?>		
	
	
