<?php
	if (!empty($notice)) echo '<div style="margin:18px 2px;"><p id="' . $status . '">' . $notice . '</p></div>';
?>
<p>更改账号密码:</p>
<form action="" method="POST"  id="account">
<table border="0" cellspacing="1"  id="STable" style="width:500px;">
	<tr>
	<th> &nbsp; </th>
	<th>值</th>
	<th>最后更新时间</th>
	</tr>
	<tr><td>登录出错次数限制 </td>
	<td><input type="text" name="LoginErrorLimit" class="input_text" value="<?php echo $amh_config['LoginErrorLimit']['config_value'];?>" />
	<input type="hidden" name="LoginErrorLimit_old"  value="<?php echo $amh_config['LoginErrorLimit']['config_value'];?>" />
	</td>
	<td><?php echo $amh_config['LoginErrorLimit']['config_time'];?> </td>
	</tr>
	<tr><td>是否显示版块说明 </td>
	<td>
	<input type="checkbox" name="HelpDoc" <?php echo ($amh_config['HelpDoc']['config_value'] == 'on' ) ? 'checked=""' : '';?> />
	<input type="hidden" name="HelpDoc_old"  value="<?php echo $amh_config['HelpDoc']['config_value'];?>" />
	</td>
	<td><?php echo $amh_config['HelpDoc']['config_time'];?> </td>
	</tr>
	</table>
<button type="submit" class="primary button" name="submit"><span class="check icon"></span>保存</button> 
</form>
