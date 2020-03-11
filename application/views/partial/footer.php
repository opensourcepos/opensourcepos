		</div>
	</div>

	<div id="footer">
		<div class="jumbotron push-spaces">
			<strong><?php echo $this->lang->line('common_you_are_using_ospos'); ?>
  			<?php echo $this->config->item('application_version'); ?> - <?php echo substr($this->config->item('commit_sha1'), 0, 6); ?></strong>.
			<?php echo $this->lang->line('common_please_visit_my'); ?>
			<a href="https://github.com/jekkos/opensourcepos" target="_blank"><?php echo $this->lang->line('common_website'); ?></a>
			<?php echo $this->lang->line('common_learn_about_project'); ?>
		</div>
	</div>
</body>
</html>
