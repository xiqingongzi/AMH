<?php !defined('_Amysql') && exit; ?>

<h2>AMH » Config </h2>
<div id="category">
<a href="index.php?c=config&a=config_index" id="config_index">面板配置</a>
<a href="index.php?c=config&a=config_upgrade" id="config_upgrade" >在线升级</a>
<a href="index.php?c=config&a=config_about" id="config_about" >关于AMH</a>
</div>
<script>
var action = '<?php echo $_GET['a'];?>';
var action_dom = G(action) ? G(action) : G('config_index');
action_dom.className = 'activ';
</script>
