<?php

/************************************************
 * Amysql Host - AMH 4.2
 * Amysql.com 
 * @param Object index Controller
 * Update:2013-11-01
 * 
 */

class index extends AmysqlController
{
	public $indexs = null;
	public $configs = null;
	public $action_name = array('start' => 'Start' , 'stop' => 'Stop' , 'reload' => 'ReLoad', 'restart' => 'ReStart');
	public $notice = null;
	public $top_notice = null;

	// Load Data Model
	function AmysqlModelBase()
	{
		if($this -> indexs) return;
		$this -> _class('Functions');
		$this -> indexs = $this ->  _model('indexs');
		$this -> configs = $this ->  _model('configs');
	}


	// Panel Login
	function login()
	{
		$this -> title = 'Login - AMH';
		$this -> AmysqlModelBase();
		$amh_config = $this -> configs -> get_amh_config();

		if (isset($_POST['login']))
		{
			$login_allow = $this -> indexs -> login_allow($amh_config);

			// Login Permitted
			if($login_allow['status'])
			{
				$user = $_POST['user'];
				$password = $_POST['password'];
				$VerifyCode = $_POST['VerifyCode'];
				if ($amh_config['VerifyCode']['config_value'] == 'on' && strtolower($VerifyCode) != $_SESSION['VerifyCode'])
				{
					$this -> LoginError = 'VerifyCode Not Matched.Please Retype';
				}
				else
				{
					if(empty($user) || empty($password))
						$this -> LoginError = 'Please Input Username & Password';
					else
					{
						$user_id = $this -> indexs -> logins($user, $password);
						if($user_id)
						{

							$this -> indexs -> login_insert(1, $user);
							$_SESSION['amh_user_name'] = $user;
							$_SESSION['amh_user_id'] = $user_id;
							$_SESSION['amh_config'] = $amh_config;
							$_SESSION['amh_token'] = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0,8);
							$token = ($amh_config['OpenCSRF']['config_value'] == 'on') ? '?amh_token=' . $_SESSION['amh_token'] : '';
							header('location:./index.php' . $token);
							exit();
						}
						$_POST['password'] = '';
						$this -> LoginError = 'Password Or Username Wrong!You Failed (' . ($login_allow['login_error_sum']+1) . 'Times.)';
						$this -> login_error_sum = $login_allow['login_error_sum'];
						$this -> indexs -> login_insert(0, $user);
					}
				}
			}
			else
			{
			    $this -> LoginError = 'Login failed' . $login_allow['login_error_sum'] . 'Times.Login Is Baned.Please Retry at' . date('Y-m-d H:i:s', $login_allow['allow_time']);
			}
		}

		$this -> amh_config = $amh_config;
		$this -> _view('login');
		exit();
	}

	// Index Pages
	function IndexAction()
	{
		$this -> title = 'Index - AMH';
		$this -> AmysqlModelBase();
		Functions::CheckLogin();
		$_SESSION['amh_version'] = '4.2';

		$m = isset($_GET['m']) ? $_GET['m'] : '';
		$g = isset($_GET['g']) ? $_GET['g'] : '';

		if (!empty($m) && !empty($g) && in_array($m, array('host', 'php', 'nginx', 'mysql')) && in_array($g, array('start', 'stop', 'reload', 'restart')) ) 
		{
			$cmd = "amh $m $g";
			$cmd = Functions::trim_cmd($cmd);
			exec($cmd, $tmp, $status);
			if (!$status)
			{
				$this -> status = 'success';
				$this -> notice = "$m " . $this -> action_name[$g] . 'Success.';
			}
			else
			{
			    $this -> status = 'error';
				$this -> notice = "$m " . $this -> action_name[$g] . 'Failed.';
			}
		}
		
		$this -> indexs -> log_insert($this -> notice);
		$this -> _view('index');
	}


	// Panel System Panel
	function infos()
	{
		$this -> AmysqlModelBase();
		Functions::CheckLogin();
		$cmd = "amh info";
		$result = shell_exec($cmd);
		$result = trim(Functions::trim_result($result), "\n ");
		$this -> infos = $result;
		$this -> _view('infos');
	}

	// PHPINFO
	function phpinfo()
	{
		$this -> title = 'PHPINFO - AMH';
		$this -> AmysqlModelBase();
		Functions::CheckLogin();
		$this -> _view('phpinfos');
	}

	// CSRF Alert
	function index_csrf()
	{
		$this -> title = 'CSRF Alert - AMH';
		$this -> _view('index_csrf');
	}
			
			

	// Quit
	function logout()
	{
		$this -> title = 'Logout - AMH';
		$_SESSION['amh_user_name'] = null;
		$_SESSION['amh_user_id'] = null;
		unset($_SESSION['module_score']);
		$_COOKIE['LoginKey'] = '';
		$this -> _view('logout');
	}


	// Panel  Notice
	function ajax()
	{
		$this -> AmysqlModelBase();
		Functions::CheckLogin();
		$timeout = array(
			'http'=>array(
				'method'=>"GET",
				'timeout'=>8,
			)
		);
		$context = stream_context_create($timeout);
		$html = file_get_contents('http://amysql.com/index.php?c=index&a=AMH&tag=ajax&V=' . $_SESSION['amh_version'], false, $context);
		$html = htmlspecialchars($html);
		$html = str_replace('[br]', '<br />', $html);
		$html = preg_replace('/\[url\]([a-z\_]+)\[\/url\]/i', '<a href="http://amysql.com/AMH.htm?tag=$1" target="_blank"> http://amysql.com/AMH.htm?tag=$1</a>', $html);
		echo $html;
		exit();
	}

	// Module Notice
	function module_ajax()
	{
		$this -> AmysqlModelBase();
		Functions::CheckLogin();
		Functions::get_module_score();
		Functions::get_module_available();
		echo json_encode($_SESSION['module_available']);
	}

}