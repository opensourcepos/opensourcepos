<?php
/**
* Change the  variables 'uid', 'pwd', 'phone' and 'msg' according to your sms-api on line no. 47 & 48 and in the form-field names below.
* Change the '$res = sendsms' part (on line no. 48) according to your 'sms-api.php'
* Put your 'sms-api.php' file in views/messages folder and change the file name below (on line no. 9) accordingly.
**/
?>
<?php $this->load->view("partial/header"); ?>
<?php include_once('sms-api.php'); ?>

	        <!---------------------------------- BOOTSTRAP HORIZONTAL FORM ------------------------------------------>
       <div class="jumbotron"style="max-width: 60%; margin:0 auto">
	<form   id="message_submit_form" name="message_submit_form" action="" method="POST">
	  <fieldset>
	    <legend style="text-align: center;">SEND SMS </legend>
	    <div class="form-group">
	      
		<!-------------------------   POST BUT  HIDE USER-ID & PASSWORD FOR 'SMS-API'  -------------------------->
		<input type="hidden" class="form-control" name="uid" value="<?php echo $this->config->item('msg_uid');?>">
		<input type="hidden" class="form-control" name="pwd" value="<?php echo $this->config->item('msg_pwd');?>">
		<!------------------------------------------------------------------------------------------------------->
	    <div class="form-group">
	      <label for="inputPhone" class="col-lg-2 control-label">Mobile:</label>
	      <div class="col-lg-10">
	         <input class="form-control", type="text", name="phone", placeholder="Put Mobile No Here !"></input>
	      </div>
	    </div></br></br>
	    <div class="form-group">
	      <label for="textArea" class="col-lg-2 control-label">Message:</label>
	      <div class="col-lg-10">
		<textarea class="form-control" rows="3" id="textArea" name="msg" placeholder="Put Your Message Here !"></textarea>
		<span class="help-block" style="text-align:center;">( Maximum 140 characters )</span>
	      </div>
	    </div>
	    <div>
	      <div class="col-lg-10 col-lg-offset-2">
	    <button type="submit" name="submit" class="btn btn-primary btn-md pull-right"  value="submit">Submit</button>
	      </div>
	    </div>
	  </fieldset>
	</form>
       </div>

	<!---------------------------------------------- END OF FORM ------------------------------------------------------>	
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
	
<?php $this->load->view("partial/footer"); ?>	
