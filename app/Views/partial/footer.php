			</div>
		</div>

		<div id="footer">
			<div class="jumbotron push-spaces">
				<strong><?php echo lang('common_copyrights', date('Y')); ?> ·
					<a href="https://opensourcepos.org" target="_blank"><?php echo lang('common_website'); ?></a>  ·
					<?php echo esc(config('OSPOS')->application_version) ?> - <a target="_blank" href="https://github.com/opensourcepos/opensourcepos/commit/<?php echo esc(config('OSPOS')->commit_sha1) ?>"><?php echo esc(substr(config('OSPOS')->commit_sha1, 0, 6)); ?></a></strong>.
			</div>
		</div>
	</body>
</html>