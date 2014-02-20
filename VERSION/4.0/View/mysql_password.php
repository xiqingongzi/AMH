<?php include('header.php'); ?>
<script>
var mysql_password_submit = function ()
{
	if(G('user_password1').value == '')
		return confirm('确认更改用户为无密码吗?');
	return true;
}
</script>

<div id="body">
<?php include('mysql_category.php'); ?>

<?php
	if (!empty($notice)) echo '<div style="margin:5px 2px;width:500px;"><p id="' . $status . '">' . $notice . '</p></div>';
?>
<p>修改MySQL数据库用户密码:</p>
<form action="" method="POST"  id="mysql_password" onsubmit="return mysql_password_submit();">
<table border="0" cellspacing="1"  id="STable" style="width:auto;">
	<tr>
	<th width="130"></th>
	<th width="280">值</th>
	<th>说明</td>
	</tr>
	
	<tr>
	<td>选择用户 - 链接地址</td>
	<td>
	<select name="user_name" id="user_name" style="width: 190px;">
	<?php
	foreach ($mysql_user_list as $key=>$val)
	{?>
		<option value="<?php echo $key;?>"><?php echo $val['User'];?> - <?php echo $val['Host'];?></option>
	<?php } ?>
	</select>
	<script>G('user_name').value = '<?php echo isset($_POST['user_name']) ? $_POST['user_name'] : '0';?>';</script>
	</td>
	<td class="description">选择需要修改密码的用户</td>
	</tr>
	<tr>
	<td>新密码</td>
	<td><input type="password" name="user_password1" id="user_password1" class="input_text" value="<?php echo isset($_POST['user_password1']) ? $_POST['user_password1'] : '';?>"></td>
	<td class="description">填写新密码，不填即无密码</td>
	</tr>
	<tr>
	<td>确认新密码</td>
	<td><input type="password" name="user_password2" id="user_password2" class="input_text" value="<?php echo isset($_POST['user_password2']) ? $_POST['user_password2'] : '';?>"></td>
	<td class="description">再次输入新密码</td>
	</tr>
	</table>
	<input type="hidden" value="<?php echo base64_encode(json_encode($mysql_user_list));?>" name="mysql_user_list" />
<button type="submit" class="primary button" name="submit"><span class="check icon"></span>确认修改</button> 
</form>


<div id="notice_message" style="width:660px;">
<h3>» MySQL 用户密码修改</h3>
1) 新密码如果不填写密码，即更改用户为无密码。<br />
2) 面板配置如果开启面板数据私有保护，面板将不可更改MySQL root 账号密码。<br />
</div>

</div>
<?php include('footer.php'); ?>
