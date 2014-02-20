<?php !defined('_Amysql') && exit; ?>

<h2>AMH » Account </h2>
<div id="category">
<a href="index.php?c=account&a=account_log" id="account_log">管理日志</a>
<a href="index.php?c=account&a=account_login_log" id="account_login_log" >登录日志</a>
<a href="index.php?c=account&a=account_pass" id="account_pass" >更改密码</a>
</div>
<script>
var action = '<?php echo $_GET['a'];?>';
var action_dom = G(action) ? G(action) : G('account_log');
action_dom.className = 'activ';
</script>
