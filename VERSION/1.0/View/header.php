<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo isset($title) ? $title : 'AMH';?></title>
<base href="<?php echo _Http;?>" /> 
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Language" content="zh-cn">
<link type="text/css" rel="stylesheet" href="View/css/index.css" />
<script src="View/js/index.js"></script>
</head>
<body>
<div id="header">
<a href="index.php" class="logo"></a>
<div id="navigation">
<font>Hi，<?php echo $_SESSION['amh_user_name'];?></font>
<a href="index.php">Home</a>
<a href="index.php?c=index&a=host">Host</a>
<a href="index.php?c=index&a=mysql">MySQL</a>
<a href="index.php?c=index&a=ftp">FTP</a>
<a href="index.php?c=index&a=account">Account</a>
<a href="index.php?c=index&a=logout">Logout</a>
</div>
</div>