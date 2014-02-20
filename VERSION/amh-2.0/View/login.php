<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo isset($title) ? $title : 'AMH';?></title>
<base href="<?php echo _Http;?>" /> 
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link type="text/css" rel="stylesheet" href="View/css/index.css" />
</head>
<body>
	<div id="login">
		<div id="header" style="width:auto">
			<a href="index.php" class="logo"></a>
			<div style="clear:both"></div>
		</div>
		<?php
			if (isset($LoginError)) echo '<div style="margin:18px 62px;"><p id="error">' . $LoginError . '</p></div>';
		?>
		<form id="LoginForm" action="index.php?c=index&a=login" method="POST" autocomplete="off" >
			<div id="SelectDom">
			</div>			
			<p>
				<dl><dt id="UserDom">username:</dt><dd><input type="text" name="user" class="input_text" value="" / ></dd></dl>
				<dl><dt id="PassDom">password:</dt><dd><input type="password" name="password" class="input_text" / ></dd></dl>
				<dl><dd id="login_submit"><input type="submit" name="login" id="SubmitDom" value="login"  / ></dd></dl>
			</p>
		</form>
		<p id="footer" style="width:auto">Powered by Amysql.com</p>
	</div>
</body>
</html>
