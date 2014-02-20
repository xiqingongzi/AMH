<?php include('header.php'); ?>
<script src="View/js/My97DatePicker/WdatePicker.js"></script>

<div id="body">
<?php include('account_category.php'); ?>

<p>最近登录记录:</p>

<form action="" method="GET">
<input type="hidden" value="account_login_log" name="a"/>
<input type="hidden" value="account" name="c"/>
搜索 <select id="field" name="field" style="width:100px;">
<option value="0">用户名</option>
<option value="1">IP</option>
</select>
<script>G('field').value = '<?php echo isset($_GET['field']) ? $_GET['field'] : '0';?>';</script>
<input type="text" name="search" class="input_text" style="width:120px;" value="<?php echo isset($_GET['search']) ? $_GET['search'] : '';?>" /> 
&nbsp; 登录状态 <select id="login_success" name="login_success" style="width:100px;">
<option value="">所有</option>
<option value="1">成功</option>
<option value="2">失败</option>
</select>
<script>G('login_success').value = '<?php echo isset($_GET['login_success']) ? $_GET['login_success'] : '';?>';</script>

&nbsp; 时间 <input class="Wdate" type="text" name="start_time" onFocus="WdatePicker({isShowClear:false})" value="<?php echo isset($_GET['start_time']) ? $_GET['start_time'] : '';?>"/> 至 
<input class="Wdate" type="text" name="end_time"  onFocus="WdatePicker({isShowClear:false})" value="<?php echo isset($_GET['end_time']) ? $_GET['end_time'] : '';?>"/>&nbsp;
<button type="submit" class="primary button" >搜索</button> 
</form>

<table border="0" cellspacing="1"  id="STable" style="width:auto;">
	<tr>
	<th>&nbsp; ID &nbsp;</th>
	<th>&nbsp; 用户名 &nbsp;</th>
	<th width="160">登录IP</th>
	<th width="80">登录状态</th>
	<th width="160">登录时间</th>
	</tr>
<?php
	foreach ($login_list['data'] as $key=>$val)
	{
?>
	<tr>
	<th class="i"><?php echo $val['login_id'];?></th>
	<td><?php echo $val['login_user_name'];?></td>
	<td><?php echo $val['login_ip'];?></td>
	<td><?php echo $val['login_success'] ? '成功' : '失败';?></td>
	<td><?php echo $val['login_time'];?></td>
	</tr>
<?php
	}
?>
</table>
<div id="page_list">总<?php echo $total_page;?>页 - <?php echo $login_list['sum'];?>记录 » 页码 <?php echo htmlspecialchars_decode($page_list);?> </div>
<br />

</div>
<?php include('footer.php'); ?>