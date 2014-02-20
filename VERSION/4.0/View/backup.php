<?php include('header.php'); ?>

<style>
#STable td.td_block {
	padding:10px 20px;
	text-align:left;
	line-height:23px;
}
</style>
<div id="body">
<?php 
	include('backup_category.php'); 
	if(isset($category))
		include($category . '.php');
?>

</div>
<?php include('footer.php'); ?>
