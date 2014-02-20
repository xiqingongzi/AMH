<?php include('header.php'); ?>

<script>
window.onload = function ()
{
	var host_domain_dom = G('host_domain');
	var host_root_dom = G('host_root');
	var host_log_dom = G('host_log');

	host_domain_dom.onkeyup = function ()
	{
		host_root_dom.innerHTML = (this.value == '') ? '主标识域名' : this.value;
		host_log_dom.innerHTML = (this.value == '') ? '主标识域名' : this.value;
	}
	host_domain_dom.onkeyup();
}
</script>
<style>
#STable td {
padding: 4px 5px 3px 5px;
_padding: 2px 3px;
}
</style>

<div id="body">
<h2>AMH » Host</h2>

<?php 
if(is_array($host_list) && count($host_list) > 0)
	$list_show = true;
?>


<?php
	if (isset($top_notice)) echo '<div style="margin:18px 2px;"><p id="' . $status . '">' . $top_notice . '</p></div>';
?>
<p>虚拟主机列表:</p>
<table border="0" cellspacing="1"  id="STable" style="width:<?php echo isset($list_show) ? 'auto':'1111px';?>">
	<tr>
	<th>ID</th>
	<th>标识域名</th>
	<th>绑定域名</th>
	<th>网站根目录</th>
	<th>默认主页</th>
	<th>Rewrite<br />规则</th>
	<th>404页面</th>
	<th>访问<br />日志</th>
	<th>错误<br />日志</th>
	<th>所属组</th>
	<th>添加时间</th>
	<th>运行维护</th>
	<th>操作</th>
	</tr>
	<?php 
	if(!isset($list_show))
	{
	?>
		<tr><td colspan="13">暂无虚拟主机</td></tr>
	<?php	
	}
	else
	{
		foreach ($host_list as $key=>$val)
		{
	?>
			<tr>
			<th class="i"><?php echo $val['host_id'];?></th>
			<td><a href="http://<?php echo $val['host_domain'];?>" target="_blank"><?php echo $val['host_domain'];?></a></td>
			<td><?php echo str_replace(',' , '<br />', $val['host_server_name']);?></td>
			<td><?php echo $val['host_root'];?></td>
			<td><?php echo str_replace(',' , '<br />', $val['host_index_name']);?></td>
			<td><?php echo empty($val['host_rewrite']) ? '无' : $val['host_rewrite'];?></td>
			<td><?php echo $val['host_not_found'];?></td>
			<td><?php echo $val['host_log'] == '1' ? '开启' : '关闭';?></td>
			<td><?php echo $val['host_error_log'] == '1' ? '开启' : '关闭';?></td>
			<td><?php echo $val['host_type'];?></td>
			<td><?php echo date('Y-m-d\<\b\r\>H:i:s', strtotime($val['host_time']));?>&nbsp; </td>
			<td>
			<a href="index.php?c=index&a=host&run=<?php echo $val['host_domain'];?>&m=host&g=<?php echo $val['host_nginx'] ? 'stop' : 'start';?>" >
			<span <?php echo $val['host_nginx'] ? 'class="run_start" title="主机运行正常"' : 'class="run_stop" title="主机已停止"';?>>Host</span>
			</a>
			<a href="index.php?c=index&a=host&run=<?php echo $val['host_domain'];?>&m=php&g=<?php echo $val['host_php'] ? 'stop' : 'start';?>">
				<span <?php echo $val['host_php'] ? 'class="run_start" title="PHP运行正常"' : 'class="run_stop" title="PHP已停止"';?>>PHP</span></td>
			</a>
			<td>
			<a href="index.php?c=index&a=host&edit=<?php echo $val['host_domain'];?>">编辑</a>
			<a href="index.php?c=index&a=host&del=<?php echo $val['host_domain'];?>" onclick="return confirm('确认删除虚拟主机:<?php echo $val['host_domain'];?>?');">删除</a>
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
	if (isset($notice)) echo '<div style="margin:18px 2px;"><p id="' . $status . '">' . $notice . '</p></div>';
?>

<p>
<?php echo isset($edit_host) ? '编辑' : '新增';?>虚拟主机:<?php echo isset($edit_host) ? $_POST['host_domain'] : '';?>
</p>
<form action="index.php?c=index&a=host" method="POST"  id="host_edit" />
<table border="0" cellspacing="1"  id="STable" style="width:900px;">
	<tr>
	<th> &nbsp; </th>
	<th>值</th>
	<th>说明</th>
	</tr>
	<tr><td>主标识域名</td>
	<td><input type="text" id="host_domain" name="host_domain" class="input_text <?php echo isset($edit_host) ? ' disabled' : '';?>" value="<?php echo $_POST['host_domain'];?>" <?php echo isset($edit_host) ? 'disabled=""' : '';?>/></td>
	<td><p> &nbsp; <font class="red">*</font> 用于唯一标识的主域名 </p>
	<p> &nbsp; 不需填写http:// 格式例如: amysql.com</p>
	</td>
	</tr>
	<tr><td>绑定域名</td>
	<td><input type="text" id="host_server_name" name="host_server_name" class="input_text" value="<?php echo $_POST['host_server_name'];?>" </td>
	<td><p> &nbsp; 主机绑定的域名</p>
	<p> &nbsp; 例如: amysql.com,www.amysql.com 多项请用英文逗号分隔 </p>
	</td>
	</tr>
	<tr><td>网站根目录</td>
	<td>/home/wwwroot/<span id="host_root" class="red">主标识域名</span>/web</td>
	<td><p> &nbsp;  网站的根目录</td>
	</tr>
	<tr><td>主机日志目录</td>
	<td>/home/wwwroot/<span id="host_log" class="red">主标识域名</span>/log</td>
	<td><p> &nbsp;  主机访问与错误日志文件目录</td>
	</tr>
	<tr><td>默认主页	</td>
	<td><input type="text" name="host_index_name" class="input_text" value="<?php echo isset($_POST['host_index_name']) ? $_POST['host_index_name'] : 'index.html,index.htm,index.php';?>" /></td>
	<td><p> &nbsp;  主机默认的主页，多项请用英文逗号分隔 </p></td>
	</tr>
	<tr><td>Rewrite规则</td>
	<td>
	<select name="host_rewrite" id="host_rewrite">
	<option value="">选择虚拟Rewrite规则</option>
	<?php
		foreach ($Rewrite as $key=>$val)
			echo '<option value="' . $val . '">' . $val . '</option>';
	?>
	</select>
	<script>
	G('host_rewrite').value = '<?php echo isset($_POST['host_rewrite']) ? $_POST['host_rewrite'] : '';?>';
	</script>
	</td>
	<td><p> &nbsp; URL重写规则</p><p> &nbsp; Rewrite存放文件夹 /usr/local/nginx/conf/rewrite</p></td>
	</tr>
	<tr><td>404页面</td>
	<td><input type="text" name="host_not_found" class="input_text" value="<?php echo isset($_POST['host_not_found']) ? $_POST['host_not_found'] : '/404.html';?>" /></td>
	<td><p> &nbsp; 找不到的页面即转至此页面</p></td>
	</tr>
	<tr><td>访问日志	</td>
	<td><input type="checkbox" name="host_log" <?php echo ($_POST['host_log'] == '1') ? ' checked=""' : '';?> /></td>
	<td><p> &nbsp; 是否开启访问日志</p></td>
	</tr>
	<tr><td>错误日志</td>
	<td><input type="checkbox" name="host_error_log"  <?php echo ($_POST['host_error_log'] == '1') ? ' checked=""' : '';?> /></td>
	<td><p> &nbsp; 是否开启错误日志</p></td>
	</tr>
</table>

<?php if (isset($edit_host)) { ?>
	<input type="hidden" name="save_eidt" value="<?php echo $_POST['host_domain'];?>" />
<?php } else { ?>
	<input type="hidden" name="save" value="y" />
<?php }?>

<input type="submit" value="保存" />
</form>


<div id="notice_message">
<h3>» SSH Host</h3>
1) 有步骤提示操作: <br />
ssh执行命令: /root/amh/host <br />
然后选择对应的1~7的选项进行操作。<br />

2) 或直接操作: <br />
<ul>
<li>启动虚拟主机: /root/amh/host start [主标识域名] 缺省主标识域名即为所有</li>
<li>停止虚拟主机: /root/amh/host stop [主标识域名] 缺省主标识域名即为所有</li>
<li>虚拟主机列表: /root/amh/host list </li>
<li>新增虚拟主机: /root/amh/host add [主标识域名] [绑定域名] [默认主页] [Rewrite规则] [404页面] [访问日志 on/off] [错误日志 on/off]</li>
<li>编辑虚拟主机: /root/amh/host edit [主标识域名] [绑定域名] [默认主页] [Rewrite规则] [404页面] [访问日志 on/off] [错误日志 on/off]</li>
<li>删除虚拟主机: /root/amh/host del [主标识域名]</li>

</ul>

温馨提示:<br />
增加或编辑虚拟主机忽略参某参数请填写0，如参数有多项请使用英文逗号分隔。 <br />
例如: /root/amh/host add amysql.com amysql.com,www.amysql.com index.html,index.php 0 0 0 on off<br />
以上命令为增加一虚拟主机，主标识域名为amysql.com，绑定域名amysql.com与ww.amysql.com，默认主页为index.html与index.php，并开启错误日志。<br />
</div>
</div>
<?php include('footer.php'); ?>
