<?php include('header.php'); ?>

<div id="body">
<?php include('mysql_category.php'); ?>

<p>MySQL数据库列表:</p>
<table border="0" cellspacing="1"  id="STable" style="width:auto;margin-bottom:5px;">
	<tr>
	<th>&nbsp; ID &nbsp;</th>
	<th>数据库</th>
	<th width="150">字符集</th>
	<th  width="80">表数量</th>
	</tr>
	<?php
		foreach ($databases as $key=>$val)
		{
	?>
	<tr>
	<th class="i"><?php echo $key+1;?></th>
	<td style="padding:5px 30px">
		<a href="index.php?c=mysql&a=mysql_list&ams=database&name=<?php echo urlencode($val['Database']);?>" target="_blank"><?php echo $val['Database'];?></a>
	</td>
	<td><?php echo $val['collations'];?></td>
	<td><?php echo $val['sum'];?></td>
	<?php
		}
	?>
</table>
<img src="View/images/logo_ams.gif" align="top"/> 
<input type="button" value="MySQL管理" onclick="WindowOpen('index.php?c=mysql&a=mysql_list&ams=index');"/>

<div id="notice_message" style="width:470px;">
<h3>» SSH MySQL</h3>
1) 有步骤提示操作: <br />
ssh执行命令: amh mysql <br />
然后选择对应的1~6的选项进行操作。<br />

2) 或直接操作: <br />
<ul>
<li>启动MySQL: amh mysql start</li>
<li>停止MySQL: amh mysql stop </li>
<li>重载MySQL: amh mysql reload </li>
<li>重启MySQL: amh mysql restart</li>
<li>强制重载MySQL: amh mysql force-reload </li>
</ul>
3) MySQL本地连接地址使用 127.0.0.1
</div>
</div>
<?php include('footer.php'); ?>
