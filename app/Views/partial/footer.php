		</div>
	</div>

	<div id="footer">
		<div class="jumbotron push-spaces">
			<strong><?php echo lang('Common.copyrights', date('Y')); ?> · 
			<a href="https://opensourcepos.org" target="_blank"><?php echo lang('Common.website'); ?></a>  · 
  			<?php echo $this->config->get('application_version'); ?> - <?php echo substr($this->config->get('commit_sha1'), 0, 6); ?></strong>.
		</div>
	</div>
</body>
</html>
