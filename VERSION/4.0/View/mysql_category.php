<?php !defined('_Amysql') && exit; ?>

<h2>AMH » MySQL</h2>
<div id="category">
<a href="index.php?c=mysql&a=mysql_list" id="mysql_list" >数据库</a>
<a href="index.php?c=mysql&a=mysql_create" id="mysql_create">快速建库</a>
<a href="index.php?c=mysql&a=mysql_password" id="mysql_password">修改密码</a>
<a href="index.php?c=mysql&a=mysql_setparam" id="mysql_setparam">参数配置</a>
<script>
var action = '<?php echo $_GET['a'];?>';
var action_dom = G(action) ? G(action) : G('mysql_list');
action_dom.className = 'activ';
</script>
</div>