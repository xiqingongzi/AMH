// AJAX请求
var Ajax={};
Ajax._xmlHttp = function(){ return new (window.ActiveXObject||window.XMLHttpRequest)("Microsoft.XMLHTTP");}
Ajax._AddEventToXHP = function(xhp,fun,isxml){
	xhp.onreadystatechange=function(){
		if(xhp.readyState==4&&xhp.status==200)
			fun(isxml?xhp.responseXML:xhp.responseText);
	}	
}
Ajax.get=function(url,fun,isxml,bool){
	var _xhp = this._xmlHttp();	
	this._AddEventToXHP(_xhp, fun || function(){} ,isxml);
	_xhp.open("GET",url,bool);
	_xhp.send(null);	
}
Ajax.post=function(url,data,fun,isxml,bool){	
	var _xhp = this._xmlHttp();	
	this._AddEventToXHP(_xhp, fun || function(){},isxml);
	_xhp.open("POST",url,bool);
	_xhp.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
	_xhp.send(data);
}

// 创建元素
var C = function (tag, attr, CssOrHtml)
{
	var o = (typeof(tag) != 'object') ? document.createElement(tag) : tag;
	if (attr == 'In')
	{
		if(CssOrHtml  && typeof(CssOrHtml) == 'object') 
		{
			if(CssOrHtml.length > 1 && CssOrHtml.constructor == Array )
			{
				for (x in CssOrHtml)
					if(CssOrHtml[x]) o.appendChild(CssOrHtml[x]);
			}
			else
			    o.appendChild(CssOrHtml);
		}
		else
			o.innerHTML = CssOrHtml;
		return o;
	}

	if (typeof(attr) == 'object')
	{
		for (k in attr )
			if(attr[k] != '') o[k] = attr[k];
	}

	if (typeof(CssOrHtml) == 'object')
	{
	    for (k in CssOrHtml )
			if(CssOrHtml[k] != '') o.style[k] = CssOrHtml[k];
	}
	return o;
}
// 取得元素
var G = function (id) {return document.getElementById(id); }

// 获取class元素
var getElementByClassName = function (cls,elm) 
{  
	var arrCls = new Array();  
	var seeElm = elm;  
	var rexCls = new RegExp('(|\\\\s)' + cls + '(\\\\s|)','i');  
	var lisElm = document.getElementsByTagName(seeElm);  
	for (var i=0; i<lisElm.length; i++ ) 
	{  
		var evaCls = lisElm[i].className;  
		if(evaCls.length > 0 && (evaCls == cls || rexCls.test(evaCls))) 
			arrCls.push(lisElm[i]);  
	}  
	return arrCls;  
}
// 生成下拉框
function CreatesSelect (arr, name)
{
	if(typeof(name) != 'string') name = '';
	name = name.toUpperCase();
	var selected = false;
	var S = C('select');
	for (var x in arr )
	{
		if (typeof(arr[x]) != 'object')
		{
			var O = C('option');
			S.options.add(O);
			var arr_split = arr[x].split('|');
			if(arr_split.length > 1)
			{
				O.text = arr_split[0];
				O.value = arr_split[1];
			}
			else
				O.text = O.value = arr[x];

			if(O.text.toUpperCase() == name) 
				selected = O.selected = true;
		}
		else
		{
		    var O = C('optgroup');
			O.label = arr[x][0];
			for (var xx in arr[x][1] )
			{
				var SO = C('option');
				O.appendChild(SO);
				var arr_split = arr[x][1][xx].split('|');
				if(arr_split.length > 1)
				{
					SO.text = arr_split[0];
					SO.value = arr_split[1];
				}
				else
					SO.value = SO.text = arr[x][1][xx];
				if(SO.text.toUpperCase() == name && !selected) SO.selected = true;
			}
			S.appendChild(O);
		}
	}
	return S;
}


// 模块实时进程
var module_ing_name;			// 模块名称
var module_ing_actionName;		// 动作名称
var show_result_dom = null;		// 实时进度DOM
var temp_scrollTop = 0;			// 当前滚动条上方高度
var ing_status;
var module_ing_status = false;	// 最终运行状态
var module_ing_button;			// 按钮
var module_ing = function ()	
{
	if(!show_result_dom) show_result_dom = G('show_result');
	if(parseInt(Math.max(show_result_dom.scrollHeight, show_result_dom.scrollHeight)) > 430 ) _module_ing();
}
var _module_ing = function ()
{
	temp_scrollTop = show_result_dom.scrollTop;
	show_result_dom.scrollTop = parseInt(show_result_dom.scrollTop) + 15;
	if(temp_scrollTop != parseInt(show_result_dom.scrollTop))
	{
		setTimeout(function ()
		{
			_module_ing()
		}, 100);
	}
}
var module_end = function ()
{
	module_ing_button = G('module_ing_button');
	ing_status = G('ing_status');

	// ssh返回false即为成功
	if (!module_ing_status)
	{
		ing_status.id = 'success';
		ing_status.innerHTML = module_ing_name + ' ' + module_ing_actionName + '成功。'
		Ajax.get('./index.php?c=host&a=host&run=amh-web&m=php&g=reload&confirm=y');
	}
	else
	{
		ing_status.id = 'error';
		ing_status.innerHTML = module_ing_name + ' ' + module_ing_actionName + '失败。'
	}
	module_ing_button.disabled = false;
	module_ing_button.value = '返回模块程序列表';
	module_ing_button.onclick = function ()
	{
		window.location = './index.php?c=module&a=module_list&page=' + page;
	}
}