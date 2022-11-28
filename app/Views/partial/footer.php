			</div>
		</div>

		<div id="footer">
			<div class="jumbotron push-spaces">
				<strong><?php echo lang('Common.copyrights', ['current_year' => date('Y')]) ?> ·
				<a href="https://opensourcepos.org" target="_blank"><?php echo lang('Common.website') ?></a>  ·
				<?php echo esc(config('OSPOS')->settings['application_version']) ?> - <a target="_blank" href="https://github.com/opensourcepos/opensourcepos/commit/<?php echo esc(config('OSPOS')->settings['commit_sha1']) ?>"><?php echo esc(substr(config('OSPOS')->settings['commit_sha1'], 0, 6)); ?></a></strong>.
			</div>
		</div>
	</body>
</html>
