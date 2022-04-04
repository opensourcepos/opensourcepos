			</div>
		</div>

		<div id="footer">
			<div class="jumbotron push-spaces">
				<strong><?php echo lang('Common.copyrights', ['current_year' => date('Y')]) ?> ·
				<a href="https://opensourcepos.org" target="_blank"><?php echo lang('Common.website') ?></a>  ·
				<?php echo esc(config('OSPOS')->application_version) ?> - <?php echo esc(substr(config('OSPOS')->commit_sha1, 0, 6)) ?></strong>.
			</div>
		</div>
	</body>
</html>
