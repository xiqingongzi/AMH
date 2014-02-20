<?php include('header.php'); ?>

<div id="body">
<h2>AMH » MySQL</h2>

<p>MySQL数据库列表:</p>
<table border="0" cellspacing="1"  id="STable" style="width:500px;margin-bottom:5px;">
	<tr>
	<th>ID</th>
	<th>数据库</th>
	<th>字符集</th>
	<th>表数量</th>
	</tr>
	<?php
		foreach ($databases as $key=>$val)
		{
	?>
	<tr>
	<th class="i"><?php echo $key+1;?></th>
	<td><a href="index.php?c=index&a=mysql&ams=database&name=<?php echo urlencode($val['Database']);?>" target="_blank"><?php echo $val['Database'];?></a></td>
	<td><?php echo $val['collations'];?></td>
	<td><?php echo $val['sum'];?></td>
	<?php
		}
	?>
</table>
<img src="View/images/logo_ams.gif" align="top"/> 
<input type="button" value="MySQL管理" onclick="window.open('index.php?c=index&a=mysql&ams=index');"/>
<input type="button" value="创建数据库" onclick="window.open('index.php?c=index&a=mysql&ams=create');"/>

</div>
<?php include('footer.php'); ?>
