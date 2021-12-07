		</div>
	</div>

	<div id="footer">
		<div class="jumbotron push-spaces">
			<strong><?php echo lang('Common.copyrights', date('Y')) ?> ·
			<a href="https://opensourcepos.org" target="_blank"><?php echo lang('Common.website') ?></a>  ·
  			<?php echo esc($this->appconfig->get('application_version')) ?> - <?php echo esc(substr($this->appconfig->get('commit_sha1')), 0, 6) ?></strong>.
		</div>
	</div>
</body>
</html>
