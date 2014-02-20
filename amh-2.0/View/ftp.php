<?php include('header.php'); ?>

<div id="body">
<h2>AMH » FTP</h2>

<?php
	if (!empty($top_notice)) echo '<div style="margin:18px 2px;"><p id="' . $status . '">' . $top_notice . '</p></div>';
?>
<p>FTP账号列表:</p>
<table border="0" cellspacing="1"  id="STable" style="width:800px;">
	<tr>
	<th>ID</th>
	<th>账号</th>
	<th>密码</th>
	<th>根目录</th>
	<th>所属组</th>
	<th>添加时间</th>
	<th>操作</th>
	</tr>
	<?php 
	if(!is_array($ftp_list) || count($ftp_list) < 1)
	{
	?>
		<tr><td colspan="7">暂无FTP账号</td></tr>
	<?php	
	}
	else
	{
		foreach ($ftp_list as $key=>$val)
		{
	?>
			<tr>
			<th class="i"><?php echo $val['ftp_id'];?></th>
			<td><?php echo $val['ftp_name'];?></td>
			<td>******</th>
			<td><?php echo $val['ftp_root'];?></td>
			<td><?php echo $val['ftp_type'];?></td>
			<td><?php echo $val['ftp_time'];?></td>
			<td>
			<?php if($val['ftp_type'] == 'ssh') { ?>
			<a href="javascript:" class="button disabled"><span class="pen icon disabled"></span> 编辑</a>
			<a href="javascript:" class="button disabled"><span class="cross icon disabled"></span> 删除</a>
			<?php } else {?>
			<a href="index.php?c=index&a=ftp&edit=<?php echo $val['ftp_name'];?>" class="button"><span class="pen icon"></span> 编辑</a>
			<a href="index.php?c=index&a=ftp&del=<?php echo $val['ftp_name'];?>" class="button" onclick="return confirm('确认删除FTP账号:<?php echo $val['ftp_name'];?>?');"><span class="cross icon"></span> 删除</a>

			<?php } ?>
			</td>
			</tr>
	<?php
		}
	}
	?>
</table>
<br />
<br />

<?php
	if (!empty($notice)) echo '<div style="margin:18px 2px;"><p id="' . $status . '">' . $notice . '</p></div>';
?>

<p>
<?php echo isset($edit_ftp) ? '编辑' : '新增';?>FTP账号:<?php echo isset($edit_ftp) ? $_POST['ftp_name'] : '';?>
</p>
<form action="index.php?c=index&a=ftp" method="POST"  id="ftp_edit" />
<table border="0" cellspacing="1"  id="STable" style="width:700px;">
	<tr>
	<th> &nbsp; </th>
	<th>值 [填写0即忽略选项]</th>
	<th>说明 [<a href="javascript:;" onclick="ShowFtpTop()">打开 / 关闭 高级选项</a>] </th>
	</tr>
	<tr><td>账号</td>
	<td><input type="text" name="ftp_name" class="input_text <?php echo isset($edit_ftp) ? ' disabled' : '';?>" value="<?php echo $_POST['ftp_name'];?>" <?php echo isset($edit_ftp) ? 'disabled=""' : '';?>/></td>
	<td><p> &nbsp; <font class="red">*</font> 登录FTP账号</p></td>
	</tr>
	<tr><td>密码</td>
	<td><input type="password" name="ftp_password" class="input_text" value="<?php echo $_POST['ftp_password'];?>" /></td>
	<td><p> &nbsp; <font class="red">*</font> 登录FTP密码 <?php echo isset($edit_ftp) ? ' [不更改密码请留空]' : '';?></p></td>
	</tr>
	<tr><td>主机根目录</td>
	<td>
	<select name="ftp_root" id="ftp_root">
	<option value="">请选择虚拟主机根目录</option>
	<?php
		foreach ($dirs as $key=>$val)
		{
			if($val != 'index')
				echo '<option value="' . $val . '">/home/wwwroot/' . $val . '/web</option>';
		}
	?>
	</select>
	<script>
	G('ftp_root').value = '<?php echo isset($_POST['ftp_root']) ? $_POST['ftp_root'] : '';?>';
	</script>
	</td>
	<td><p> &nbsp; <font class="red">*</font> FTP根目录</p></td>
	</tr>
	<tr class="ftptop none"><td>上传总流量</td>
	<td><input type="text" name="ftp_upload_bandwidth" class="input_text" value="<?php echo isset($_POST['ftp_upload_bandwidth']) ? $_POST['ftp_upload_bandwidth'] : '0';?>" /></td>
	<td><p> &nbsp; 限制上传流量 [MB]</p></td>
	</tr>
	<tr class="ftptop none"><td>下载总流量</td>
	<td><input type="text" name="ftp_download_bandwidth" class="input_text" value="<?php echo isset($_POST['ftp_download_bandwidth']) ? $_POST['ftp_download_bandwidth'] : '0';?>" /></td>
	<td><p> &nbsp; 限制下载流量  [MB]</p></td>
	</tr>
	<tr class="ftptop none"><td>上传速度</td>
	<td><input type="text" name="ftp_upload_ratio" class="input_text" value="<?php echo isset($_POST['ftp_upload_ratio']) ? $_POST['ftp_upload_ratio'] : '0';?>" /></td>
	<td><p> &nbsp; 限制上传速度  [KB]</p></td>
	</tr>
	<tr class="ftptop none"><td>下载速度</td>
	<td><input type="text" name="ftp_download_ratio" class="input_text" value="<?php echo isset($_POST['ftp_download_ratio']) ? $_POST['ftp_download_ratio'] : '0';?>" /></td>
	<td><p> &nbsp; 限制下载速度  [KB]</p></td>
	</tr>
	<tr class="ftptop none"><td>文件数量</td>
	<td><input type="text" name="ftp_max_files" class="input_text" value="<?php echo isset($_POST['ftp_max_files']) ? $_POST['ftp_max_files'] : '0';?>" /></td>
	<td><p> &nbsp; 限制FTP文件个数</p></td>
	</tr>
	<tr class="ftptop none"><td>容量</td>
	<td><input type="text" name="ftp_max_mbytes" class="input_text" value="<?php echo isset($_POST['ftp_max_mbytes']) ? $_POST['ftp_max_mbytes'] : '0';?>" /></td>
	<td><p> &nbsp; 限制FTP空间容量 [GB]</p></td>
	</tr>
	<tr class="ftptop none"><td>连接并发数</td>
	<td><input type="text" name="ftp_max_concurrent" class="input_text" value="<?php echo isset($_POST['ftp_max_concurrent']) ? $_POST['ftp_max_concurrent'] : '0';?>" /></td>
	<td><p> &nbsp; 限制同时连接FTP数</p></td>
	</tr>
	<tr class="ftptop none"><td>使用时间限制</td>
	<td><input type="text" name="ftp_allow_time" class="input_text" value="<?php echo isset($_POST['ftp_allow_time']) ? $_POST['ftp_allow_time'] : '0';?>" /></td>
	<td><p> &nbsp; 限制只能在允许时间段内连接FTP</p>
	<p> &nbsp; 格式：小时分钟-小时分钟</p></td>
	</tr>
</table>

<?php if (isset($edit_ftp)) { ?>
	<input type="hidden" name="save_eidt" value="<?php echo $_POST['ftp_name'];?>" />
	<script>ShowFtpTop();</script>
<?php } else { ?>
	<input type="hidden" name="save" value="y" />
<?php }?>

<button type="submit" class="primary button" name="submit"><span class="check icon"></span>保存</button> 
</form>


<div id="notice_message">
<h3>» WEB FTP</h3>
1) web添加的ftp账号根目录只允许为虚拟主机的根目录。 <br />
2) ssh添加的ftp账号web不可删除与编辑。 <br />

<h3>» SSH FTP</h3>
1) 有步骤提示操作: <br />
ssh执行命令: amh ftp <br />
然后选择对应的1~6的选项进行操作。<br />

2) 或直接操作: <br />
<ul>
<li>查看ftp列表: amh ftp list </li>
<li>增加ftp用户: amh ftp add [账号] [密码] [根目录] [上传总流量] [下载总流量] [上传速度] [下载速度] [文件数量] [容量] [连接并发数] [使用时间限制]</li>
<li>编辑ftp用户: amh ftp edit [账号] [-] [根目录] [上传总流量] [下载总流量] [上传速度] [下载速度] [文件数量] [容量] [连接并发数] [使用时间限制]
<li>更改ftp密码: amh ftp pass [账号] [密码]
<li>删除ftp用户: amh ftp del [账号]</li>
</ul>

温馨提示:<br />
增加或编辑账号忽略参某参数请填0。 <br />
例如: amh ftp add testftp testpass /home/wwwroot 0 0 100 <br />
以上命令为增加ftp用户，账号为testftp密码为testpass，ftp根目录为/home/wwwroot，且限制上传速度为100kb。 <br />
</div>

</div>
<?php include('footer.php'); ?>
