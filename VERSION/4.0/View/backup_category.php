<?php !defined('_Amysql') && exit; ?>

<h2>AMH » Backup </h2>
<div id="category">
<a href="index.php?c=backup&a=backup_list" id="backup_list">备份列表</a>
<a href="index.php?c=backup&a=backup_list&category=backup_remote" id="backup_remote" >远程设置</a>
<a href="index.php?c=backup&a=backup_list&category=backup_now" id="backup_now" >即时备份</a>
<a href="index.php?c=backup&a=backup_list&category=backup_revert" id="backup_revert">一键还原</a>
</div>
<script> G('<?php echo $category;?>').className = 'activ'; </script>