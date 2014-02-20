<?php

class module extends AmysqlController
{
	public $indexs = null;
	public $modules = null;
	public $notice = null;
	public $top_notice = null;

	// Model
	function AmysqlModelBase()
	{
		if($this -> indexs) return;
		$this -> _class('Functions');
		$this -> indexs = $this ->  _model('indexs');
		$this -> modules = $this ->  _model('modules');
	}


	function IndexAction()
	{
		$this -> module_list();
	}

	// 模块管理
	function module_list()
	{
		$this -> title = 'AMH - Module';
		$this -> AmysqlModelBase();
		Functions::CheckLogin();

		$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
		$page_sum = 5;

		if (isset($_GET['action']) && isset($_GET['name']))
		{
			$name = $_GET['name'];
			$action = $_GET['action'];
			$action_list = array('install' => '安装' , 'uninstall' => '卸载', 'delete' => '删除');

			// 安装与卸载实时进程 ************************************
			$un_install = in_array($action, array('install', 'uninstall')) ? true : false;
			if ($un_install)
			{
				set_time_limit(0);
				$actionName = isset($_GET['actionName']) ? $_GET['actionName'] : $action_list[$action];
				$this -> module_ing_name = $name;
				$this -> module_ing_actionName = $actionName;
				$this -> page = $page;
				$this -> _view('module_ing');
				$cmd = "amh module $name $action y";
				$cmd = Functions::trim_cmd($cmd);
			    $popen_handle = popen($cmd, 'r');
				$result = '';
				$i = 0;
				echo '<div id="show_result">';
				while(!feof($popen_handle))
				{
					$line = fgets($popen_handle);
					echo $line . '<br />';
					if($i%5 == 0) echo "<script>module_ing();</script>\n";
					$result .= $line;
					++$i;
				}
				$module_ing_status = json_encode(!(strpos($result, '[OK]') !== false && strpos($result, '[Error]') == false));
				$result_status = ($module_ing_status == 'false') ? true : false;
				echo "<script>module_ing();module_ing_status = {$module_ing_status};module_end();</script>$line</div>";
				pclose($popen_handle);
			}
			// ***************************************************

			// 删除模块
			if ($action == 'delete')
			{
				$actionName = $action_list[$action];
				$result_status = $this -> modules -> module_delete($name);
			}

			if ($result_status)
			{
				$this -> status = 'success';
				$this -> notice = "$name {$actionName}成功。";
			}
			else
			{
				$this -> status = 'error';
				$this -> notice = "$name {$actionName}失败。";
			}

			$this -> indexs -> log_insert($this -> notice);
			if($un_install) exit();
		}

		
		$get_module_list_data = $this -> modules -> get_module_list_data($page, $page_sum);
		$total_page = ceil($get_module_list_data['sum'] / $page_sum);						
		$page_list = Functions::page('ModuleList', $get_module_list_data['sum'], $total_page, $page, 'c=module&a=module_list');		// 分页列表
		
		global $Config;
		$Config['XSS'] = false;
		$this -> page = $page;
		$this -> total_page = $total_page;
		$this -> page_list = $page_list;
		$this -> module_list_data = $get_module_list_data;
		$this -> _view('module_list');
	}

	// 下载模块
	function module_down()
	{
		$this -> title = 'AMH - Module';
		$this -> AmysqlModelBase();
		Functions::CheckLogin();

		if (isset($_POST['download_submit']))
		{
			$module_name = $_POST['module_name'];
			if (!empty($module_name))
			{
				if($this -> modules -> module_download($module_name))
				{
					$this -> status = 'success';
					$this -> notice = "模块下载成功：$module_name";
				}
				else
				{
				    $this -> status = 'error';
					$this -> notice = "模块下载失败：$module_name";
				}
			}
			else
			{
			    $this -> status = 'error';
				$this -> notice = "请输入模块名字。";
			}
		}

		$this -> indexs -> log_insert($this -> notice);
		$this -> _view('module_down');
	}


}

?>