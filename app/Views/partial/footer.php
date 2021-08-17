		</div>
	</div>

	<div id="footer">
		<div class="jumbotron push-spaces">
			<strong><?php echo lang('Common.copyrights', date('Y')); ?> · 
			<a href="https://opensourcepos.org" target="_blank"><?php echo lang('Common.website'); ?></a>  · 
  			<?php echo $this->config->item('application_version'); ?> - <?php echo substr($this->config->item('commit_sha1'), 0, 6); ?></strong>.
		</div>
	</div>
</body>
</html>
