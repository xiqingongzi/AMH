<?php

/************************************************
 * Amysql Host - AMH 4.2
 * Amysql.com 
 * @param Object account AccountController
 * Update:2013-11-01
 * 
 */

class account extends AmysqlController
{
	public $indexs = null;
	public $accounts = null;
	public $notice = null;
	public $top_notice = null;

	// Load Data Model
	function AmysqlModelBase()
	{
		if($this -> indexs) return;
		$this -> _class('Functions');
		$this -> indexs = $this ->  _model('indexs');
		$this -> accounts = $this ->  _model('accounts');
	}

	// Default Action
	function IndexAction()
	{
		$this -> account_log();
	}

	// Account Logs
	function account_log()
	{
		$this -> title = 'Operation Logs - Account - AMH';
		$this -> AmysqlModelBase();
		Functions::CheckLogin();

		$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
		$page_sum = 20;
		$log_list = $this -> accounts -> log_list($page, $page_sum);
		$total_page = ceil($log_list['sum'] / $page_sum);						
		$page_list = Functions::page('AccountLog', $log_list['sum'], $total_page, $page);		// PageNavList

		$this -> page = $page;
		$this -> total_page = $total_page;
		$this -> page_list = $page_list;
		$this -> log_list = $log_list;
		$this -> _view('account_log');
	}

	// Login Logs
	function account_login_log()
	{
		$this -> title = 'Login Log - Account - AMH';
		$this -> AmysqlModelBase();
		Functions::CheckLogin();

		$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
		$page_sum = 20;
		$login_list = $this -> accounts -> login_list($page, $page_sum);
		$total_page = ceil($login_list['sum'] / $page_sum);						
		$page_list = Functions::page('AccountLog', $login_list['sum'], $total_page, $page);		// PageNavList

		$this -> page = $page;
		$this -> total_page = $total_page;
		$this -> page_list = $page_list;
		$this -> login_list = $login_list;
		$this -> _view('account_login_log');
	}

	// Change Password
	function account_pass()
	{
		$this -> title = 'ChangePassword - Account - AMH';
		$this -> AmysqlModelBase();
		Functions::CheckLogin();

		if (isset($_POST['submit']))
		{
			$user_password = $_POST['user_password'];
			$new_user_password = $_POST['new_user_password'];
			$new_user_password2 = $_POST['new_user_password2'];
			$error = '';
			$this -> status = 'error';

			$status = $this -> indexs -> logins($_SESSION['amh_user_name'], $user_password);
			if ($status)
			{
				if(empty($new_user_password) || empty($new_user_password2))
					$error = 'Password Could not Empty.';
				elseif($new_user_password != $new_user_password2)
					$error = 'The Two Password Is Not Matched';
			}
			else
				$error = 'Old Password Is Wrong
				';

			if (empty($error))
			{
				$status = $this -> accounts -> change_pass($new_user_password);
				if($status)
				{
					$this -> status = 'success';
					$this -> notice = 'Change Password Success!';
				}
				else
					$this -> notice = 'Change Password Failed!';
			}
			else
				$this -> notice = $error;
		}

		$this -> indexs -> log_insert($this -> notice);
		$this -> _view('account_pass');
	} 

}

?>