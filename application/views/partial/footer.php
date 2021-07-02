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
	<script type="text/javascript" src="dist/bootstrap/js/bootstrap.bundle.min.js"></script>
	<script type="text/javascript" src="js/lang_lines.js"></script>
	<script type="text/javascript" src="js/other.js"></script>
	<script type="text/javascript" src="js/bs-tooltips.js"></script>
	<script type="text/javascript" src="js/bs-modal_switch_content.js"></script>
	<script type="text/javascript" src="js/ospos-change_password.js"></script>
</body>

</html>