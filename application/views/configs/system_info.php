<div id="config_wrapper">
	<?php echo $this->lang->line('config_server_notice'); ?>
	<table class="table text-left" >
		<tbody>
		<tr>
		  <th><?php echo $this->lang->line('config_ospos_info'); ?></th>
			<td><?php echo $this->config->item('application_version'); ?> - <?php echo substr($this->config->item('commit_sha1'), 0, 6); ?><br></td>
		</tr>
		<tr>
			<th>Language Code</th>
			<td><?php echo current_language_code();	?></td>
		</tr>
		<tr>
		  <th>Extensions & Modules</th>
			<td>
			<?php 
				echo "GD: ", extension_loaded('gd') ? 'Enabled &#x2713' : 'MISSING &#x2717', '<br>';
				echo "BC Math: ", extension_loaded('bcmath') ? 'Enabled &#x2713' : 'MISSING &#x2717', '<br>';
				echo "INTL: ", extension_loaded('intl') ? 'Enabled &#x2713' : 'MISSING &#x2717', '<br>';
				echo "OpenSSL: ", extension_loaded('openssl') ? 'Enabled &#x2713' : 'MISSING &#x2717', '<br>';
				echo "MBString: ", extension_loaded('mbstring') ? 'Enabled &#x2713' : 'MISSING &#x2717', '<br>';
				echo "Curl: ", extension_loaded('curl') ? 'Enabled &#x2713' : 'MISSING &#x2717', '<br>';		
			?>	
			</td>
		</tr>
		<tr>
		  <th>User Settings</th>
			<td>Browser: 
			<?php
				function get_browser_name($user_agent)
				{
					if (strpos($user_agent, 'Opera') || strpos($user_agent, 'OPR/')) return 'Opera';
					elseif (strpos($user_agent, 'Edge')) return 'Edge';
					elseif (strpos($user_agent, 'Chrome')) return 'Chrome';
					elseif (strpos($user_agent, 'Safari')) return 'Safari';
					elseif (strpos($user_agent, 'Firefox')) return 'Firefox';
					elseif (strpos($user_agent, 'MSIE') || strpos($user_agent, 'Trident/7')) return 'Internet Explorer';
					return 'Other';
				}
				echo get_browser_name($_SERVER['HTTP_USER_AGENT']);
			?><br>

				Server Software: <?php echo $_SERVER['SERVER_SOFTWARE']; ?><br>
				PHP Version: <?php echo PHP_VERSION; ?> <br>
				Server Port: <?php echo $_SERVER['SERVER_PORT']; ?><br>
				DB Version: <?php print mysqli_get_client_info(); ?><br>
				OS: <?php echo php_uname();	?></td>
		</tr>
		<tr>
		  <th>File Permissions</th>
			<td>[application/logs:]
				<?php $logs = '../application/logs/'; 
					$uploads = '../public/uploads/'; 
					$images = '../public/uploads/item_pics/'; 
					$import = '../import_items.csv';
					$importcustomers = '../import_customers.csv';
					
					if (is_writable($logs)) {
						echo ' -  ' . substr(sprintf("%o",fileperms($logs)),-4) . ' |  ' . '<font color="green">  Writable &#x2713 </font>';
					} else {
						echo ' -  ' . substr(sprintf("%o",fileperms($logs)),-4) . ' |  ' . '<font color="red">  Not Writable &#x2717 </font>';						
					} 
					clearstatcache();
				?><br>
				[public/uploads:]
				<?php 
					if (is_writable($uploads)) {
						echo ' -  ' . substr(sprintf("%o",fileperms($uploads)),-4) . ' |  ' . '<font color="green">  Writable &#x2713 </font>';
					} else {
						echo ' -  ' . substr(sprintf("%o",fileperms($uploads)),-4) . ' |  ' . '<font color="red"> Not Writable &#x2717 </font>';
					} 
					clearstatcache();
				?><br>
				[public/uploads/item_pics:] 	
				<?php 
					if (is_writable($images)) {
						echo ' -  ' . substr(sprintf("%o",fileperms($images)),-4) . ' |  ' . '<font color="green"> Writable &#x2713 </font>';
					} else {
						echo ' -  ' . substr(sprintf("%o",fileperms($images)),-4) . ' |  ' . '<font color="red"> Not Writable &#x2717 </font>';
					} 
					clearstatcache();
				?><br>
				[import_items.csv:]
				<?php 
					if (is_writable($import)) {
						echo ' -  ' . substr(sprintf("%o",fileperms($import)),-4) . ' |  ' . '<font color="green">  Writable &#x2713 </font>';
					} else {
						echo ' -  ' . substr(sprintf("%o",fileperms($import)),-4) . ' |  ' . '<font color="red"> Not Writable &#x2717 </font>';
					}
					clearstatcache();					
				?><br>
				[import_customers.csv:] 
				<?php 
					if (is_writable($importcustomers)) {
						echo ' -  ' . substr(sprintf("%o",fileperms($importcustomers)),-4) . ' |  ' . '<font color="green">  Writable &#x2713 </font>';
					} else {
						echo ' -  ' . substr(sprintf("%o",fileperms($importcustomers)),-4) . ' |  ' . '<font color="red"> Not Writable &#x2717 </font>';
					} 
					clearstatcache();
				?><br>
				<a href="https://github.com/opensourcepos/opensourcepos/issues/new" target="_blank"> Report An issue </a>
			</td></tr>
	</table>
	</div>