<div id="config_wrapper">
	<table class="table text-left" >
		<tbody>
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

				Server Software: 
				<?php echo $_SERVER['SERVER_SOFTWARE']; ?><br>
				PHP Version: 
				<?php echo PHP_VERSION; ?> <br>
				Server Port: 
				<?php echo $_SERVER['SERVER_PORT']; ?><br>
				DB Version: 
				<?php print mysqli_get_client_info(); ?><br>
				OS: 
				<?php echo php_uname();	?></td>
		</tr>
		<tr>
		  <th>File's Permissions</th>
			<td>Application/logs: 
				<?php $logs = '../application/logs/'; 
					$uploads = '../public/uploads/'; 
					$images = '../public/uploads/item_pics/'; 
					$import = '../import_items.csv';
					$importcustomers = '../import_customers.csv';
					
					if (is_writable($logs)) {
						echo 'Writable &#x2713';
					} else {
						echo 'NOT Writable &#x2717 ';
					} 
				?><br>
				Public/uploads: 
				<?php 
					if (is_writable($uploads)) {
						echo 'Writable &#x2713';
					} else {
						echo 'NOT Writable &#x2717 ';
					} 
				?><br>
				public/uploads/item_pics: 	
				<?php 
					if (is_writable($images)) {
						echo 'Writable &#x2713';
					} else {
						echo 'NOT Writable &#x2717 ';
					} 
				?><br>
				import_items.csv: 
				<?php 
					if (is_writable($import)) {
						echo 'Writable &#x2717';
					} else {
						echo 'NOT Writable/Read Only &#x2713';
					} 
				?><br>
				import_customers.csv: 
				<?php 
					if (is_writable($importcustomers)) {
						echo 'Writable &#x2717 <br>';
					} else {
						echo 'NOT Writable/Read Only &#x2713 <br>';
					} 
				chmod("../.htaccess",0644);
				chmod("../application/.htaccess",0644);
				chmod("../public/.htaccess",0644); 
				chmod("../import_items.csv",0444);
				chmod("../import_customers.csv",0444); 
				echo "<br>";
				echo ".htaccess permissions were reset to 0644 <br>"; 
				echo "CSV permissions were reset to 0444"; 
				?><br>
				<a href="https://github.com/opensourcepos/opensourcepos/issues/new" target="_blank"> Report An issue </a>
			</td>
	</table>
</div>
