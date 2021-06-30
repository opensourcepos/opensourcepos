	</main>

	<footer class="flex-shrink-0 text-muted small fw-bold bg-light py-3">
		<div class="container-lg d-flex flex-wrap justify-content-center align-items-center">
			<div>
				<span><?php echo $this->lang->line('common_copyrights', date('Y')); ?></span>
			</div>
			<div>
				<span class="d-none d-xl-block">&nbsp;·&nbsp;<a href="https://opensourcepos.org" class="text-muted" target="_blank" rel="noopener"><?php echo $this->lang->line('common_website'); ?></a>&nbsp;·&nbsp;</span>
				<span class="d-xl-none">&nbsp;·&nbsp;<?php echo $this->lang->line('common_website'); ?>&nbsp;·&nbsp;</span>
			</div>
			<div>
				<span><?php echo $this->config->item('application_version'); ?>&nbsp;-&nbsp;<?php echo substr($this->config->item('commit_sha1'), 0, 6); ?></span>
			</div>
		</div>
	</footer>
</body>

</html>
