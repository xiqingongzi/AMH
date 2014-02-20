<?php include('header.php'); ?>
<script>
window.onload = function ()
{
	var amh_news_dom = G('amh_news');
	Ajax.get('index.php?c=index&a=ajax',function (msg){
		amh_news_dom.innerHTML = msg;
	})
}
</script>

<div id="body">
<h2>欢迎使用LNMP虚拟主机面板 - AMH</h2>

<?php
	if (isset($notice)) echo '<div style="margin:18px 2px;"><p id="' . $status . '">' . $notice . '</p></div>';
?>

<h3>» Host <span>虚拟主机全局运行</span></h3>
<a href="index.php?m=host&g=start">启动</a>
<a href="index.php?m=host&g=stop">停止</a>

<h3>» PHP <span>虚拟主机PHP全局运行</span></h3>
<a href="index.php?m=php&g=start">启动</a>
<a href="index.php?m=php&g=stop">停止</a>
<a href="index.php?m=php&g=reload">重启</a>


<h3>» Nginx <span>系统Nginx运行</span></h3>
<a href="index.php?m=nginx&g=start">启动</a>
<a href="index.php?m=nginx&g=stop" onclick="return confirm('强行停止Nginx吗? 停止后需使用SSH启动。');">停止</a>
<a href="index.php?m=nginx&g=reload">重启</a>

<h3>» MySQL <span>系统MySQL运行</span></h3>
<a href="index.php?m=mysql&g=start">启动</a>
<a href="index.php?m=mysql&g=stop" onclick="return confirm('强行停止MySQL吗? 停止后需使用SSH启动。');">停止</a>
<a href="index.php?m=mysql&g=reload">重启</a>



<br /><br />

<h3>» SSH 管理命令</h3>
<ul>
<li>Nginx Management: amh nginx</li>
<li>PHP Management: amh php</li>
<li>MySQL Management: amh mysql</li>
<li>Host Management: amh host</li>
<li>FTP Management: amh ftp</li>
</ul>

<h3>» 相关目录</h3>
<ul>
<li>Web root dir: /home/wwwroot</li>
<li>Nginx dir: /usr/local/nginx</li>
<li>PHP dir: /usr/local/php</li>
<li>MySQL dir: /usr/local/mysql</li>
<li>MySQL data dir: /usr/local/mysql/data</li>
</ul>

<div id="amh_info">
<b>Amysql 官方消息</b>
<div id="amh_news"><img src="View/images/loading.gif" /> Loading...</div>

<b>AMH 面板软件版本</b>
<div id="amh_version">
AMH 1.1	<br />
AMS 1.0	<br />
Nginx 1.2.1 <br />
MySQL 5.5.25 <br />
PHP 5.3.13 <br />
<i>2012-08-29</i>
</div>

</div>
</div>
<?php include('footer.php'); ?>
