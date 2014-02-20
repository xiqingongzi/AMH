<?php include('header.php'); ?>
<link type="text/css" rel="stylesheet" href="View/css/info.css" />

<script>
var amh_news = function ()
{
	var amh_news_dom = G('amh_news');
	Ajax.get('index.php?c=index&a=ajax&tag=' + Math.random(),function (msg){
		amh_news_dom.innerHTML = msg;
	}, false, true)
}

var amh_info_ing = false;
var amh_info = function ()
{
	if(amh_info_ing) return;
	amh_info_ing = true;
	var info_dom = G('ajax_info');
	Ajax.get('index.php?c=index&a=infos&tag=' + Math.random(),function (msg){
		
		if (msg.indexOf('Login') != -1)
		{
			window.location = './index.php?c=index&c=login';
			return;
		}

		G('phpinfo').style.display = 'block';
		info_dom.innerHTML = msg;
		setTimeout(function (){
			amh_info_ing = false;
			amh_info();
		}, 1000);
	}, false, true)
}
</script>

<div id="body">
	<div id="amh_home">
		<h2>欢迎使用LNMP虚拟主机面板 - AMH</h2>

		<?php
			if (!empty($notice)) echo '<div style="margin:18px 2px;"><p id="' . $status . '">' . $notice . '</p></div>';
		?>

		<h3>» Host <span>虚拟主机全局运行</span></h3>
		<a href="index.php?m=host&g=start">启动</a>
		<a href="index.php?m=host&g=stop">停止</a>

		<h3>» PHP <span>虚拟主机PHP全局运行</span></h3>
		<a href="index.php?m=php&g=start">启动</a>
		<a href="index.php?m=php&g=stop">停止</a>
		<a href="index.php?m=php&g=reload">重载</a>

		<h3>» Nginx <span>系统Nginx运行</span></h3>
		<a href="index.php?m=nginx&g=start">启动</a>
		<a href="index.php?m=nginx&g=stop" onclick="return confirm('强行停止Nginx吗? 停止后需使用SSH启动。');">停止</a>
		<a href="index.php?m=nginx&g=reload">重载</a>

		<h3>» MySQL <span>系统MySQL运行</span></h3>
		<a href="index.php?m=mysql&g=start">启动</a>
		<a href="index.php?m=mysql&g=stop" onclick="return confirm('强行停止MySQL吗? 停止后需使用SSH启动。');">停止</a>
		<a href="index.php?m=mysql&g=restart">重启</a>

		<br /><br />

		<h3>» SSH 管理命令</h3>
		<ul>
		<li>Host : amh host</li>
		<li>PHP : amh php</li>
		<li>Nginx : amh nginx</li>
		<li>MySQL : amh mysql</li>
		<li>FTP : amh ftp</li>
		<li>Backup : amh backup</li>
		<li>Revert : amh revert</li>
		<li>SetParam : amh SetParam</li>
		<li>Module : amh module</li>
		<li>Info : amh info</li>
		</ul>

		<h3>» 相关目录</h3>
		<ul>
		<li>WebSite : /home/wwwroot</li>
		<li>Nginx : /usr/local/nginx</li>
		<li>PHP : /usr/local/php</li>
		<li>MySQL : /usr/local/mysql</li>
		<li>MySQL-Data : /usr/local/mysql/data</li>
		</ul>
	</div>


	<div id="amh_info">
		<div id="info">
			<div id="ajax_info">
			<img src="View/images/loading.gif" onload="amh_info();"/> Loading...
			<?php include('info.php'); ?>
			</div>
			<p id="phpinfo" class="ico php" style="display:none;"> <a href="/index.php?c=index&a=phpinfo">PHPINFO</a></p>
		</div>

		<b>Amysql 官方消息</b>
		<div id="amh_news"> <img src="View/images/loading.gif" onload="amh_news();"/> Loading...</div>

		<b>AMH 面板软件信息</b>
		<div id="amh_version">
		AMH 3.2	<br />
		AMP 1.5 <br />
		AMS 1.5.0107 <br />
		Nginx 1.2.8 <br />
		MySQL 5.5.31 <br />
		PHP 5.3.24 <br />
		PureFTP 1.0.36 <br />
		<i>Update Time: 2013-05-01</i>
		</div>
	</div>
	<div style="clear:both"></div>
</div>
<?php include('footer.php'); ?>
<br /><br /><br />
