<?php $this->load->view("partial/header"); ?>

<?php
if (isset($error))
{
	echo "<div class='alert alert-dismissible alert-danger'>".$error."</div>";
}

if (!empty($warning))
{
	echo "<div class='alert alert-dismissible alert-warning'>".$warning."</div>";
}

if (isset($success))
{
	echo "<div class='alert alert-dismissible alert-success'>".$success."</div>";
}
?>
	      
<div class="jumbotron" style="max-width: 57%; margin:0 auto">
	<?php echo form_open("messages/send/", array('id'=>'send_sms_form', 'enctype'=>'multipart/form-data', 'method'=>'post', 'class'=>'form-horizontal')); ?>
		<fieldset>
			<legend style="text-align: center;">SEND SMS</legend>
			<div class="form-group">
				<div class="form-group">
					<label for="inputPhone" class="col-lg-2 control-label">Mobile:</label>
					<div class="col-lg-10">
						<input class="form-control", type="text", name="phone", placeholder="Put Mobile No(s) Here !"></input>
						<span class="help-block" style="text-align:center;">( In case of multiple recipients, enter mobile numbers separated with comma )</span>
					</div>
				</div></br>

				<div class="form-group">
					<label for="textArea" class="col-lg-2 control-label">Message:</label>
					<div class="col-lg-10">
						<textarea class="form-control" rows="3" id="textArea" name="msg" placeholder="Put Your Message Here !"></textarea>
					</div>
				</div>

				<div class="col-lg-10 col-lg-offset-2">
					<button type="submit" name="submit" class="btn btn-primary btn-md pull-right"  value="submit">Submit</button>
				</div>
			</div>
		</fieldset>
	<?php echo form_close(); ?>
</div>

<?php $this->load->view("partial/footer"); ?>
