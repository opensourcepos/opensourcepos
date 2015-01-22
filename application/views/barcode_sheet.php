<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
        "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><?php echo $this->lang->line('items_generate_barcodes'); ?></title>
</head>
<body>
<table width='50%' align='center' cellpadding='20'>
<tr>
<?php 
$count = 0;
foreach($items as $item)
{
	$barcode = $this->config->item('barcode_content') === "id" ? $item['id'] : $item['item_number'];
	$text = $this->config->item('barcode_content') === "id" ? $item['name'] : $item['item_number'];
	
	if ($count % 2 ==0 and $count!=0)
	{
		echo '</tr><tr>';
	}
	echo "<td><img src='".site_url()."/barcode?barcode=$barcode&text=$text&width=256' /></td>";
	$count++;
}
?>
</tr>
</table>
</body>
</html>
