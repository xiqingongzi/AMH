<?php !defined('_Amysql') && exit; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo isset($title) ? $title : 'AMH';?></title>
<base href="<?php echo _Http;?>" /> 
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Language" content="zh-cn">
<link type="text/css" rel="stylesheet" href="View/css/index.css" />
<link type="text/css" rel="stylesheet" href="View/css/buttons.css" />
<script src="View/js/index.js"></script>
<style>
<?php if($_SESSION['amh_config']['HelpDoc']['config_value'] == 'no') { ?>
#notice_message {display:none;}
<?php }?>
</style>

<script>
var HTTP_HOST = '<?php echo $_SERVER['HTTP_HOST'];?>';
var amh_token = '<?php echo $_SESSION['amh_token'];?>';
var OpenCSRF = '<?php echo $_SESSION['amh_config']['OpenCSRF']['config_value'];?>';
</script>
</head>
<body>
<div id="header">
<a href="index.php" class="logo"></a>

<?php if(!empty($_SESSION['amh_config']['UpgradeSum']['config_value'])) { ?>
<a href="/index.php?c=config&a=config_upgrade" id="upgrade_notice">您现在有<?php echo $_SESSION['amh_config']['UpgradeSum']['config_value'];?>个更新</a>
<?php }?>

<div id="navigation">
<font>Hi, <?php echo $_SESSION['amh_user_name'];?></font>
<a href="index.php" id="home">主页</a>
<a href="index.php?c=host" id="host" >虚拟主机</a>
<a href="index.php?c=mysql" id="mysql" >MySQL</a>
<a href="index.php?c=ftp" id="ftp" >FTP</a>
<a href="index.php?c=backup" id="backup" >备份</a>
<a href="index.php?c=task" id="task" >任务计划</a>
<a href="index.php?c=module" id="module" >模块扩展</a>
<a href="index.php?c=account" id="account" >管理员</a>
<a href="index.php?c=config" id="config" >面板配置</a>
<a href="index.php?c=index&a=logout" >退出</a>
<?php $action_name = (!isset($_GET['c']) || in_array($_GET['c'], array('index', 'host', 'mysql', 'ftp', 'backup', 'task', 'account', 'config'))) ? $_GET['c'] : 'module';?>
<script>
var action = '<?php echo $action_name;?>';
var action_dom = G(action) ? G(action) : G('home');
action_dom.className = 'activ';
</script>
</div>
</div>
