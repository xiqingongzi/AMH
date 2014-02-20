<?php include('header.php'); ?>

<div id="body" style="height:535px;">
<?php include('module_category.php'); ?>
<div style="width:500px;"><div style="margin-top:-3px">
<p id="ing_status"><?php echo "{$module_ing_name} {$module_ing_actionName}";?>进行中：</p>
</div>
</div>

</div>
<input type="button" value="<?php echo $module_ing_actionName;?>中，请稍候…" disabled="" id="module_ing_button"  class="cmd_ing_button"/>
<script>
module_ing_name = <?php echo json_encode($module_ing_name);?>;
module_ing_actionName = <?php echo json_encode($module_ing_actionName);?>;
page = <?php echo json_encode($page);?>;
</script>
<?php include('footer.php'); ?>