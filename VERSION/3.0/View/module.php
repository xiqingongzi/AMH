<?php include('header.php'); ?>
<?php if (isset($_GET['action'])) {?>
<script>
// 面板php重启
Ajax.get('./index.php?c=host&a=host&run=amh-web&m=php&g=reload&confirm=y');
</script>
<?php } ?>

<div id="body">
<h2>AMH » Module</h2>

<p>模块扩展&程序管理列表:</p>
<?php
	if (!empty($notice)) echo '<div style="margin:18px 2px;"><p id="' . $status . '">' . $notice . '</p></div>';
?>
<div id="module_list">
	<?php
	foreach ($module_list_data['data'] as $key=>$val)
	{
	?>
		<div class="item"  onmouseover="this.className='item_hover'" onmouseout="this.className='item'">
			<h3><?php echo $val['AMH-ModuleName'];?><i><font><?php echo $val['AMH-ModuleDate'];?></font></i></h3>
			<p><?php echo $val['AMH-ModuleDescription'];?></p>
			<em><a href="<?php echo $val['AMH-ModuleWebSite'];?>" target="_blank"><?php echo $val['AMH-ModuleWebSite'];?></a></em>
			<i class="by">ModuleScript By: <?php echo $val['AMH-MoudleScriptBy'];?></i>
			<a id="Deactivate" class="button" href="./index.php?c=module&a=module_list&name=<?php echo $val['AMH-ModuleName'];?>&action=<?php echo $val['AMH-ModuleAction'];?>&page=<?php echo $page;?>" onclick="return confirm('确认<?php echo $val['AMH-ModuleButton'];?><?php echo $val['AMH-ModuleName'];?> 吗?');"><?php echo $val['AMH-ModuleButton'];?></a>
			<?php if($val['AMH-ModuleStatus'] == 'true' && $val['AMH-ModuleAdmin'] != '') { ?>
			<a id="Deactivate" class="button" href="<?php echo $val['AMH-ModuleAdmin'];?>" target="_blank" style="right: 120px;">管理模块</a>
			<?php } ?>
			<div style="clear:both;"></div>
		</div>
	<?php
	}
	?>
</div>

<div id="page_list">总<?php echo $total_page;?>页 - 共<?php echo $module_list_data['sum'];?>个模块扩展 » 页码 <?php echo $page_list;?> </div>


<div id="notice_message" style="width:470px;">
<h3>» SSH Module</h3>
1) 有步骤提示操作: <br />
ssh执行命令: amh module <br />
然后选择对应的模块进行管理。<br />
<br />
2) 或直接操作: <br />
<ul>
<li>模块信息: amh module [模块名字] info</li>
<li>安装模块: amh module [模块名字] install</li>
<li>卸载模块: amh module [模块名字] uninstall</li>
<li>管理模块: amh module [模块名字] admin</li>
<li>安装状态: amh module [模块名字] status</li>
</ul>
3) 支持用户创建编写新的功能模块，模块脚本目录 /root/amh/modules
<br />模块编程规范请查阅官方论坛文档。
<br />
4) 注意: 安装非官方提供的模块，必要验证确认模块安全性。
</div>
</div>
<?php include('footer.php'); ?>
