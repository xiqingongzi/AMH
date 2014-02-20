<h2>AMH » Host</h2>
<div id="category">
<a href="index.php?c=host&a=vhost" id="vhost" >虚拟主机</a>
<a href="index.php?c=host&a=php_setparam" id="php_setparam">PHP配置</a>
<script>
var action = '<?php echo $_GET['a'];?>';
var action_dom = G(action) ? G(action) : G('vhost');
action_dom.className = 'activ';
</script>
</div>