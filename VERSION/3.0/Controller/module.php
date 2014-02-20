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

	function module_list()
	{
		$this -> title = 'AMH - Module';
		$this -> AmysqlModelBase();
		Functions::CheckLogin();

		if (isset($_GET['action']) && isset($_GET['name']))
		{
			$name = $_GET['name'];
			$action = $_GET['action'];
			$cmd = "amh module $name $action ";
			$cmd = Functions::trim_cmd($cmd);
			$result = trim(shell_exec($cmd), "\n");
			$result = Functions::trim_result($result);

			if (strpos($result, '[OK]') !== false)
			{
				$this -> status = 'success';
				$this -> notice = "$name $action 成功。";
			}
			else
			{
				$this -> status = 'error';
				$this -> notice = "$name $action 失败。";
			}
		}

		$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
		$page_sum = 5;
		
		$get_module_list_data = $this -> modules -> get_module_list_data($page, $page_sum);
		$total_page = ceil($get_module_list_data['sum'] / $page_sum);						
		$page_list = Functions::page('ModuleList', $get_module_list_data['sum'], $total_page, $page, 'c=module&a=module_list');		// 分页列表
		
		global $Config;
		$Config['XSS'] = false;
		$this -> page = $page;
		$this -> total_page = $total_page;
		$this -> page_list = $page_list;
		$this -> module_list_data = $get_module_list_data;

		$this -> indexs -> log_insert($this -> notice);
		$this -> _view('module');
	} 


}

?>