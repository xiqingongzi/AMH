
/************************************************
 * Amysql Host - AMH 4.0
 * Amysql.com 
 * @param Javascript 面板主页
 * Update:2013-07-15
 * 
 */

var amh_news = function ()
{
	var amh_news_dom = G('amh_news');
	Ajax.get('/index.php?c=index&a=ajax&tag=' + Math.random(),function (msg){
		if (msg != '')
		{
			amh_news_dom.innerHTML = msg;
			upgrade_notice();
		}
	}, false, true)
}

var amh_info_ing = false;
var amh_info_go = false;
var amh_info = function ()
{
	if(amh_info_ing) return;
	amh_info_ing = true;
	var info_dom = G('ajax_info');
	Ajax.get('/index.php?c=index&a=infos&tag=' + Math.random(),function (msg){
		
		if (msg.indexOf('Login') != -1)
		{
			WindowLocation('/index.php?c=index&c=login');
			return;
		}

		G('phpinfo').style.display = 'block';
		info_dom.innerHTML = msg;
		setTimeout(function (){
			amh_info_ing = false;
			amh_info_go && amh_info();
		}, 380);
	}, false, true)
}
var info_go = function (obj)
{
	amh_info_go = amh_info_go ? false : true;
	amh_info();
	obj.style.backgroundPosition = amh_info_go ? '0px 6px' : '0px -45px';
	obj.title = amh_info_go ? '暂停实时' : '实时查看';
}