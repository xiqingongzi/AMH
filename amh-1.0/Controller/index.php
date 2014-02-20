<?php

class index extends AmysqlController
{
	public $indexs = null;
	public $action_name = array('start' => '启动' , 'stop' => '停止' , 'reload' => '重启');
	public $notice = null;
	public $top_notice = null;


	function AmysqlModelBase()
	{
		if($this -> indexs) return;
		$this -> _class('Functions');
		$this -> indexs = $this ->  _model('indexs');
	}

	
	function CheckLogin()
	{
		if (!isset($_SESSION['amh_user_name']) || empty($_SESSION['amh_user_name']))
			$this -> login();
	}

	function login()
	{
		$this -> title = 'AMH - Login';
		$this -> AmysqlModelBase();
		if (isset($_POST['login']))
		{
			$login_allow = $this -> indexs -> login_allow();

			// 允许登录
			if($login_allow['status'])
			{
				$user = $_POST['user'];
				$password = $_POST['password'];
				if(empty($user) || empty($password))
					$this -> LoginError = '请输入用户名与密码。';
				else
				{
					$user_id = $this -> indexs -> logins($user, $password);
					if($user_id)
					{
						$this -> indexs -> login_insert(1, $user);
						$_SESSION['amh_user_name'] = $user;
						$_SESSION['amh_user_id'] = $user_id;
						header('location:./');
						exit();
					}

					$this -> LoginError = '账号或密码错误，登录失败。(' . ($login_allow['login_error_sum']+1) . '次)';
					$this -> login_error_sum = $login_allow['login_error_sum'];
					$this -> indexs -> login_insert(0, $user);
				}
			}
			else
			{
			    $this -> LoginError = '登录出错已有' . $login_allow['login_error_sum'] . '次。当前禁止登录，下次允许登录时间:' . date('Y-m-d H:i:s', $login_allow['allow_time']);
			}
		}
		$this -> _view('login');
		exit();
	}


	function IndexAction()
	{
		$this -> title = 'AMH - Home';
		$this -> CheckLogin();
		$this -> AmysqlModelBase();
		
		$m = isset($_GET['m']) ? $_GET['m'] : '';
		$g = isset($_GET['g']) ? $_GET['g'] : '';

		if (!empty($m) && !empty($g) && in_array($m, array('host', 'php', 'nginx', 'mysql')) && in_array($g, array('start', 'stop', 'reload')) ) 
		{
			$cmd = "amh /root/amh/$m $g";
			$result = shell_exec($cmd);
			$result = Functions::trim_result($result);
			if (strpos($result, '[OK]') !== false)
			{
				$this -> status = 'success';
				$this -> notice = "$m " . $this -> action_name[$g] . '成功。';
			}
			else
			{
			    $this -> status = 'error';
				$this -> notice = "$m " . $this -> action_name[$g] . '失败。';
			}
		}
		
		$this -> indexs -> log_insert($this -> notice);
		$this -> _view('index');
	}

			
			
			
	// Host ****************************************
	function host()
	{
		$this -> title = 'AMH - Host';
		$this -> CheckLogin();
		$this -> AmysqlModelBase();
		$this -> status = 'error';

		// 运行维护
		if (isset($_GET['run']))
		{
			$m = isset($_GET['m']) ? $_GET['m'] : '';
			$g = isset($_GET['g']) ? $_GET['g'] : '';
			$domain = $_GET['run'];
			if (!empty($m) && !empty($g) && in_array($m, array('php', 'host')) && in_array($g, array('start', 'stop')) ) 
			{
				$cmd = "amh /root/amh/$m $g $domain";
				$result = shell_exec($cmd);
				$result = Functions::trim_result($result);
				if($m == 'php') sleep(1);
				if (strpos($result, '[OK]') !== false)
				{
					$this -> status = 'success';
					$this -> top_notice = "$domain " . $m . $this -> action_name[$g] . '成功。';
				}
				else
				{
					$this -> status = 'error';
					$this -> top_notice = "$domain " .$m . $this -> action_name[$g] . '失败。';
				}
			}
		}

		// 删除host
		if (isset($_GET['del']))
		{
			$del_name = $_GET['del'];
			if(!empty($del_name))
			{
				$result = $this -> indexs -> host_del_ssh($del_name);
				if (strpos($result, '[OK]') !== false)
				{
					$this -> status = 'success';
					$this -> top_notice = $del_name . ' : 删除虚拟主机成功。';
				}
				else
					$this -> top_notice = $del_name . ' : 删除虚拟主机失败。' . $result;
			}
		}

		// 保存host
		if (isset($_POST['save']))
		{
			if (empty($_POST['host_domain']))
				$this -> notice = '请填写主标识域名。';
			else
			{
				$result = $this -> indexs -> host_insert_ssh($_POST);
				if (strpos($result, '[OK]') !== false)
				{
					$this -> indexs -> host_insert($_POST);
					$this -> status = 'success';
					$this -> notice = $_POST['host_domain'] . ' : 新增虚拟主机成功。';
					$_POST = array();
				}
				else
					$this -> notice = $_POST['host_domain'] . ' : 新增虚拟主机失败。' . $result;
			}
		}


		// 编辑host
		if (isset($_GET['edit']))
		{
			$edit_name = $_GET['edit'];
			$_POST = $this -> indexs -> get_host($edit_name);
			$this -> edit_host = true;
		}
		// 保存编辑host
		if (isset($_POST['save_eidt']))
		{
			$_POST['host_domain'] = $host_name = $_POST['save_eidt'];
			$this -> status = 'success';
			$result = $this -> indexs -> edit_host();
			if (strpos($result, '[OK]') !== false)
			{
				$status = true;
				$top_notice = $host_name . ' : 编辑虚拟主机配置成功。';
			}
			else
			{
				$this -> status = 'error';
				$top_notice = $host_name . ' : 编辑虚拟主机配置失败。' . $result;
			}
			
			if(isset($status)) 
				$_POST = array();
			else 
				$this -> edit_host = true;
			$this -> top_notice = $top_notice;
			
		}
			
		$this -> indexs -> host_update();
		$this -> host_list = $this -> indexs -> host_list();

		$Rewrite = trim(shell_exec("amh ls /usr/local/nginx/conf/rewrite"), "\n");
		$this -> Rewrite = explode("\n", $Rewrite);

		$this -> indexs -> log_insert($this -> top_notice . $this -> notice);
		
		$this -> _view('host');
	}



	
	
	
	// MySQL ****************************************
	function mysql()
	{
		$this -> title = 'AMH - MySQL';
		$this -> CheckLogin();
		$this -> AmysqlModelBase();
		if (isset($_GET['ams']))
		{
			// 打开数据库列表
			if ($_GET['ams'] == 'OpenDatabaseJs')
			{
				header('Content-type: application/x-javascript');
				$open_database = isset($_SESSION['open_database_name']) && !empty($_SESSION['open_database_name']) ? true : false;
				$AmysqlHomeStatus = $open_database ? 'Normal' : 'Activate';
				$_AmysqlTabJson = "var _AmysqlTabJson = [";
				$_AmysqlTabJson .= "{'type':'" . $AmysqlHomeStatus . "','id':'AmysqlHome','name':'AmysqlHome - localhost', 'url': '" . _Http . "ams/index.php?c=ams&a=AmysqlHome'}";
				if($open_database)
				{
					$ODN = $_SESSION['open_database_name'];
					$_AmysqlTabJson .= ", {'type':'Activate','id':'AmysqlDatabase_" . $ODN . "','name':'" . $ODN ."', 'url': '" . _Http . "ams/index.php?c=ams&a=AmysqlDatabase&DatabaseName=" . $ODN ."'}";
				}
				$_AmysqlTabJson .= "];";
				echo $_AmysqlTabJson;
				exit();
			}
			elseif ($_GET['ams'] == 'OpenCreate')
			{
				header('Content-type: application/x-javascript');
				if (isset($_SESSION['create_database']) && !empty($_SESSION['create_database']))
					echo " AddEvent({'load':function () { ActiveSetID='N_DatabaseAdd';} },window); ";
				exit();
			}
			elseif ($_GET['ams'] == 'index')
			{
				$_SESSION['open_database_name'] = null;
				$_SESSION['create_database'] = null;
			}
			elseif ($_GET['ams'] == 'database')
			{
			    if (!empty($_GET['name']))
					$_SESSION['open_database_name'] = $_GET['name'];
				$_SESSION['create_database'] = null;
			}
			elseif ($_GET['ams'] == 'create')
			{
				$_SESSION['open_database_name'] = null;
				$_SESSION['create_database'] = 'yes';
			}
			header('location:./ams/');
			exit();
		}
		$this -> databases = $this -> indexs -> databases();
		$this -> _view('mysql');
	} 


			
			

	// FTP ****************************************
	function ftp()
	{
		$this -> title = 'AMH - FTP';
		$this -> CheckLogin();
		$this -> AmysqlModelBase();
		$this -> status = 'error';

		// 删除ftp
		if (isset($_GET['del']))
		{
			$del_name = $_GET['del'];
			if(!empty($del_name))
			{
				$get_ftp = $this -> indexs -> get_ftp($del_name);
				if($get_ftp['ftp_type'] == 'web')
				{
					$result = $this -> indexs -> ftp_del_ssh($del_name);
					if (strpos($result, '[OK]') !== false)
					{
						$this -> status = 'success';
						$this -> top_notice = $del_name . ' : 删除FTP账号成功。';
					}
					else
						$this -> top_notice = $del_name . ' : 删除FTP账号失败。' . $result;
				}
				else
				    $this -> top_notice = $del_name . ' : ssh FTP账号web端不可删除。';
			}
		}

		// 保存ftp
		if (isset($_POST['save']))
		{
			if (empty($_POST['ftp_name']) || empty($_POST['ftp_password']) || empty($_POST['ftp_root']))
				$this -> notice = '账号与密码与根目录不能为空。';
			else
			{
				$result = $this -> indexs -> ftp_insert_ssh($_POST);
				if (strpos($result, '[OK]') !== false)
				{
					$this -> indexs -> ftp_insert($_POST);
					$this -> status = 'success';
					$this -> notice = $_POST['ftp_name'] . ' : 新增FTP账号成功。';
					$_POST = array();
				}
				else
					$this -> notice = $_POST['ftp_name'] . ' : 新增FTP账号失败。' . $result;
			}
		}

		// 编辑ftp
		if (isset($_GET['edit']))
		{
			$edit_name = $_GET['edit'];
			$_POST = $this -> indexs -> get_ftp($edit_name);
			if($_POST['ftp_type'] == 'web')
			{
				$_POST['ftp_password'] = '';
				$_POST['ftp_upload_bandwidth'] /= 1024;
				$_POST['ftp_download_bandwidth'] /= 1024;
				$_POST['ftp_max_mbytes'] /= 1024*1024;
				$this -> edit_ftp = true;
			}
			else
			{
			     $this -> top_notice = $edit_name . ' : ssh FTP账号web端不可编辑。';
				 $_POST = array();
			}
		}
	
		// 保存编辑ftp
		if (isset($_POST['save_eidt']))
		{
			$_POST['ftp_name'] = $ftp_name = $_POST['save_eidt'];
			$edit_ftp = $this -> indexs -> get_ftp($ftp_name);
			if($edit_ftp['ftp_type'] == 'web')
			{
				$this -> status = 'success';
				$result = $this -> indexs -> edit_ftp();
				if (strpos($result[0], '[OK]') !== false)
				{
					$status = true;
					$top_notice = $ftp_name . ' : 编辑FTP账号成功。';
				}
				else
				{
					$this -> status = 'error';
					$top_notice = $ftp_name . ' : 编辑FTP账号失败。' . $result[0];
				}
				
				if (!empty($_POST['ftp_password']))
				{
					if (strpos($result[1], '[OK]') !== false)
					{
						$status = true;
						$top_notice .= $ftp_name . ' : 更改FTP密码成功。';
					}
					else
					{
						$this -> status = 'error';
						$top_notice .= $ftp_name . ' 更改FTP密码失败。' . $result[1];
					}
				}
				if(isset($status)) 
					$_POST = array();
				else 
					$this -> edit_ftp = true;
				$this -> top_notice = $top_notice;
			}
		}

		$ftp_list_ssh = explode("\n", trim(shell_exec("amh cat /etc/pureftpd.passwd"), "\n"));
		$this -> indexs -> ftp_update($ftp_list_ssh);
		$this -> ftp_list = $this -> indexs -> ftp_list();

		$dir_str = trim(shell_exec("amh ls /home/wwwroot"), "\n");
		$this -> dirs = explode("\n", $dir_str);

		$_POST['ftp_root'] = explode('/', $_POST['ftp_root']);
		$_POST['ftp_root'] = $_POST['ftp_root'][3];

		
		$this -> indexs -> log_insert($this -> top_notice . $this -> notice);
		$this -> _view('ftp');
	} 


		
		

	// 账号 ****************************************
	function account()
	{
		$this -> title = 'AMH - Account';
		$this -> CheckLogin();
		$this -> AmysqlModelBase();
		$this -> login_list = $this -> indexs -> login_list();
		$this -> log_list = $this -> indexs -> log_list();

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
					$error = '新密码与确认新密码不能为空。';
				elseif($new_user_password != $new_user_password2)
					$error = '新密码与确认新密码不一致。';
			}
			else
			    $error = '旧密码错误。';

			if (empty($error))
			{
				$status = $this -> indexs -> change_pass($new_user_password);
				if($status)
				{
					$this -> status = 'success';
				    $this -> notice = '更改密码成功。';
				}
				else
				    $this -> notice = '更改密码失败。';
			}
			else
			    $this -> notice = $error;
		}

		$this -> indexs -> log_insert($this -> notice);
		$this -> _view('account');
	} 



	// 退出 ****************************************
	function logout()
	{
		$this -> title = 'AMH - Logout';
		$_SESSION['amh_user_name'] = null;
		$_SESSION['amh_user_id'] = null;
		$_COOKIE['LoginKey'] = '';
		$this -> _view('logout');
	}


	// AMH AJAX ****************************************
	function ajax()
	{
		$this -> CheckLogin();
		$html = file_get_contents('http://amysql.com/index.php?c=index&a=AMH&tag=ajax');
		$html = htmlspecialchars($html);
		$html = str_replace('[br]', '<br />', $html);
		$html = preg_replace('/\[url\]([a-z\_]+)\[\/url\]/i', '<a href="http://amysql.com/index.php?c=index&a=AMH&tag=$1" target="_blank"> http://amysql.com/index.php?c=index&a=AMH&tag=$1</a>', $html);
		echo $html;
		exit();
	}

}