<?php

class index extends AmysqlController
{
	public $indexs = null;
	public $action_name = array('start' => '启动' , 'stop' => '停止' , 'reload' => '重启');
	public $notice = null;
	public $top_notice = null;

	// Model
	function AmysqlModelBase()
	{
		if($this -> indexs) return;
		$this -> _class('Functions');
		$this -> indexs = $this ->  _model('indexs');
	}

	// Check Login
	function CheckLogin()
	{
		if (!isset($_SESSION['amh_user_name']) || empty($_SESSION['amh_user_name']))
			$this -> login();
	}

	// Login
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
						$_SESSION['amh_config'] = $this -> indexs -> get_amh_config();
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

	// Home
	function IndexAction()
	{
		$this -> title = 'AMH - Home';
		$this -> CheckLogin();
		$this -> AmysqlModelBase();
		
		$m = isset($_GET['m']) ? $_GET['m'] : '';
		$g = isset($_GET['g']) ? $_GET['g'] : '';

		if (!empty($m) && !empty($g) && in_array($m, array('host', 'php', 'nginx', 'mysql')) && in_array($g, array('start', 'stop', 'reload')) ) 
		{
			$cmd = "amh $m $g";
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


	// INFO
	function infos()
	{
		$this -> CheckLogin();
		$this -> AmysqlModelBase();
		$cmd = "amh info";
		$result = shell_exec($cmd);
		$result = trim(Functions::trim_result($result), "\n ");
		$this -> infos = $result;
		$this -> _view('infos');
	}

	// phpinfo
	function phpinfo()
	{
		$this -> CheckLogin();
		$this -> _view('phpinfos');
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
				$cmd = "amh $m $g $domain";
				$cmd = Functions::trim_cmd($cmd);
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

		$Rewrite = trim(shell_exec("amh ls_rewrite"), "\n");
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

		$ftp_list_ssh = explode("\n", trim(shell_exec("amh ls_ftp_list"), "\n"));
		$this -> indexs -> ftp_update($ftp_list_ssh);
		$this -> ftp_list = $this -> indexs -> ftp_list();

		$dir_str = trim(shell_exec("amh ls_wwwroot"), "\n");
		$this -> dirs = explode("\n", $dir_str);

		$_POST['ftp_root'] = explode('/', $_POST['ftp_root']);
		$_POST['ftp_root'] = $_POST['ftp_root'][3];

		
		$this -> indexs -> log_insert($this -> top_notice . $this -> notice);
		$this -> _view('ftp');
	} 





	// 备份 ****************************************
	function backup()
	{
		$this -> title = 'AMH - Backup';
		$this -> CheckLogin();
		$this -> AmysqlModelBase();
		$this -> status = 'error';

		$category = isset($_GET['category']) ? $_GET['category'] : 'backup_list';
		$category_array = array('backup_list', 'backup_remote',  'backup_now', 'backup_revert');
		if (!in_array($category, $category_array)) $category = 'backup_list';

		$input_item = array('remote_type', 'remote_status', 'remote_ip', 'remote_path', 'remote_user', 'remote_password');
	
		if ($category == 'backup_list')
		{
			$this -> title = 'AMH - Backup - 备份列表';
			
			if (isset($_GET['del']))
			{
				$del_id = (int)$_GET['del'];
				$del_info = $this -> indexs -> get_backup($del_id);
				if (isset($del_info['backup_file']))
				{
					$file = str_replace('.amh', '', $del_info['backup_file']);
					$cmd = "amh del_backup $file";
					$cmd = Functions::trim_cmd($cmd);
					$result = shell_exec($cmd);
					$this -> status = 'success';
					$this -> notice = "删除备份文件({$file}.amh)执行完成。";
				}
				
			}

			$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
			$page_sum = 20;

			$this -> indexs -> backup_list_update();
			$backup_list = $this -> indexs -> get_backup_list($page, $page_sum);

			$total_page = ceil($backup_list['sum'] / $page_sum);						
			$page_list = Functions::page('BackupList', $backup_list['sum'], $total_page, $page);		// 分页列表

			global $Config;
			$Config['XSS'] = false;
			$this -> page = $page;
			$this -> total_page = $total_page;
			$this -> backup_list = $backup_list;
			$this -> page_list = $page_list;
		}
		elseif ($category == 'backup_remote')
		{
			$this -> title = 'AMH - Backup - 远程设置';

			// 连接测试
			if (isset($_GET['check']))
			{
				$id = (int)$_GET['check'];
				$data = $this -> indexs -> get_backup_remote($id);
				if($data['remote_type'] == 'FTP')
					$cmd = "amh BRftp check $id";
				else
					$cmd = "amh BRssh check $id";
				$cmd = Functions::trim_cmd($cmd);
				$result = shell_exec($cmd);
				$result = trim(Functions::trim_result($result), "\n ");
				echo $result;
				exit();
			}
			// 保存远程配置
			if (isset($_POST['save']))
			{
				$save = true;
				foreach ($input_item as $val)
				{
					if(empty($_POST[$val]))
					{
						$this -> notice = '新增远程备份配置失败，请填写完整数据，*号为必填项。';
						$save = false;
					}
				}
				if($save)
				{
					$id = $this -> indexs -> backup_remote_insert();
					if ($id)
					{
						$this -> status = 'success';
						$this -> notice = 'ID:' . $id . ' 新增远程备份配置成功。';
						$_POST = array();
					}
					else
						$this -> notice = ' 新增远程备份配置失败。';
				}
			}

			// 删除远程配置
			if (isset($_GET['del']))
			{
				$id = (int)$_GET['del'];
				if(!empty($id))
				{
					$result = $this -> indexs -> backup_remote_del($id);
					if ($result)
					{
						$this -> status = 'success';
						$this -> notice = 'ID:' . $id . ' 删除远程备份配置成功。';
					}
					else
						$this -> notice = 'ID:' . $id . ' 删除远程备份配置失败。';
				}
			}

			// 编辑远程配置
			if (isset($_GET['edit']))
			{
				$id = (int)$_GET['edit'];
				$_POST = $this -> indexs -> get_backup_remote($id);
				if($_POST['remote_id'])
				{
					$this -> edit_remote = true;
				}
			}

			// 保存编辑远程配置
			if (isset($_POST['save_eidt']))
			{
				$id = $_POST['remote_id'] = (int)$_POST['save_eidt'];
				$save = true;
				foreach ($input_item as $val)
				{
					if(empty($_POST[$val]) && $val != 'remote_password')
					{
						$this -> notice = 'ID:' . $id . ' 编辑远程备份配置失败。*号为必填项。';
						$save = false;
						$this -> edit_remote = true;
					}
				}
				if ($save)
				{
					$result = $this -> indexs -> backup_remote_update();
					if ($result)
					{
						$this -> status = 'success';
						$this -> notice = 'ID:' . $id . ' 编辑远程备份配置成功。';
						$_POST = array();
					}
					else
					{
						$this -> notice = 'ID:' . $id . ' 编辑远程备份配置失败。';
						$this -> edit_remote = true;
					}
				}
				
			}

			$this -> remote_list = $this -> indexs -> backup_remote_list();
		}
		elseif ($category == 'backup_now')
		{
			$this -> title = 'AMH - Backup - 即时备份';

			if (isset($_POST['backup_now']))
			{
				$backup_retemo = ($_POST['backup_retemo'] == 'on') ? 'y' : 'n';
				$backup_password = (!empty($_POST['backup_password'])) ? $_POST['backup_password'] : 'n';
				$backup_comment = (!empty($_POST['backup_comment'])) ? $_POST['backup_comment'] : '';

				if ((!empty($_POST['backup_password2']) || !empty($_POST['backup_password'])) && $_POST['backup_password'] != $_POST['backup_password2'])
				{
					$this -> notice = ' 两次密码不一致，请确认。' ;
				}
				else
				{
				    $cmd = "amh backup $backup_retemo $backup_password $backup_comment";
					$cmd = Functions::trim_cmd($cmd);
					$result = shell_exec($cmd);
					$result = trim(Functions::trim_result($result), "\n ");
					if (strpos($result, '[OK]') !== false)
					{
						$this -> status = 'success';
						$this -> notice = $result . ' 已成功创建备份文件。';
						$_POST = array();
					}
					else
						$this -> notice = $result. ' 备份文件创建失败' ;
				}
			}
		}
		elseif ($category == 'backup_revert')
		{
			$this -> title = 'AMH - Backup - 一键还原';

			$revert_id = isset($_GET['revert_id']) ? (int)$_GET['revert_id'] : '';
			if (!empty($revert_id))
				$revert = $this -> indexs -> get_backup($revert_id);

			if (isset($_POST['revert_submit']))
			{
				$backup_file = $revert['backup_file'];
				$backup_password = $_POST['backup_password'];
				$cmd = "amh revert $backup_file $backup_password";
				$cmd = Functions::trim_cmd($cmd);
				$result = shell_exec($cmd);
				$result = trim(Functions::trim_result($result), "\n ");
				if (strpos($result, '[OK]') !== false)
				{
					$this -> status = 'success';
					$this -> notice = $backup_file . ' 数据还原成功。';
				}
				else
					$this -> notice = $result . ' ' . $backup_file . ' 还原失败。' ;

			}
			$this -> revert = $revert;
		}
		

		$this -> indexs -> log_insert($this -> notice);
		$this -> category = $category;
		$this -> _view('backup');
	}
		








	// 账号 ****************************************
	function account()
	{
		$this -> title = 'AMH - Account';
		$this -> CheckLogin();
		$this -> AmysqlModelBase();
		$this -> status = 'error';

		$category = isset($_GET['category']) ? $_GET['category'] : 'account_info';
		$category_array = array('account_info', 'account_config');
		if (!in_array($category, $category_array)) $category = 'account_info';
	
		if ($category == 'account_info')
		{
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
		}
		elseif  ($category == 'account_config')
		{
			if (isset($_POST['submit']))
			{
				$_POST['LoginErrorLimit'] = (int)$_POST['LoginErrorLimit'];
				if(empty($_POST['LoginErrorLimit'])) $_POST['LoginErrorLimit'] = 1;
				if(!isset($_POST['HelpDoc'])) $_POST['HelpDoc'] = 'no';

				$up_status = $this -> indexs -> up_amh_config();
				if($up_status)
				{
					$status = 'success';
					$this -> notice = '系统配置更改成功。';
				}
				else
					$this -> notice = '系统配置更改失败。';
			}
			
			$amh_config = $this -> indexs -> get_amh_config();
			if($status == 'success')
			{
				$_SESSION['amh_config'] = $amh_config;
				$this -> status = $status;
			}
			$this -> amh_config = $amh_config;

		}
		

		$this -> indexs -> log_insert($this -> notice);
		$this -> category = $category;
		$this -> _view('account');
	} 




	// 任务计划 ****************************************
	function task()
	{
		$this -> title = 'AMH - Task';
		$this -> CheckLogin();
		$this -> AmysqlModelBase();
		$this -> status = 'error';

		if (isset($_POST['save_submit']))
		{
			$id = (int)$_POST['crontab_id'];
			$save_status = $this -> indexs -> save_task($id);
			if($save_status)
			{
				$this -> status = 'success';
				$this -> notice = 'ID' . $id . ' : 编辑保存任务计划成功。';
				$_POST = array();
			}
			else
				$this -> notice = '编辑保存任务计划失败。';
		}
		elseif (isset($_POST['task_submit']))
		{
			$insert_status = $this -> indexs -> insert_task();
			if($insert_status)
			{
				$this -> status = 'success';
				$this -> notice = 'ID' . $insert_status . ' : 新增任务计划成功。';
				$_POST = array();
			}
			else
				$this -> notice = '新增任务计划失败。';
		}
		elseif (isset($_GET['del']))
		{
			$id = (int)$_GET['del'];
			$del_status = $this -> indexs -> del_task($id);
			if($del_status)
				{
					$this -> status = 'success';
					$this -> top_notice = 'ID' . $id . ' : 删除任务计划成功。';
				}
				else
					$this -> top_notice = '删除任务计划失败。';
		}
		elseif (isset($_GET['edit']))
		{
			$id = (int)$_GET['edit'];
			$edit_task = $this -> indexs -> get_task($id);
			if(is_array($edit_task) && $edit_task['crontab_type'] != 'ssh')
			{
				foreach ($edit_task as $key=>$val)
				{
					if (in_array($key, array('crontab_minute', 'crontab_hour', 'crontab_day', 'crontab_month', 'crontab_week')))
					{
						$_key = str_replace('crontab_', '', $key);
						if (strpos($val, '/') !== false)
						{
							$_val = explode('/', $val);
							$_val2 = explode('-', $_val[0]);
							$_POST[$_key.'_average_start'] = $_val2[0];
							$_POST[$_key.'_average_end'] = isset($_val2[1]) ? $_val2[1] : '*';
							$_POST[$_key.'_average_input'] = $_val[1];
							$_POST[$_key.'_select'] = '/';
						}
						elseif (strpos($val, '-') !== false)
						{
							$_val = explode('-', $val);
							$_POST[$_key.'_period_start'] = $_val[0];
							$_POST[$_key.'_period_end'] = $_val[1];
							$_POST[$_key.'_select'] = '-';
						}
						elseif (strpos($val, ',') !== false)
						{
							$_POST[$_key.'_respectively'] = explode(',', $val);
							$_POST[$_key.'_select'] = ',';
						}
						else
						{
							$_POST[$_key.'_time'] = $val;
							$_POST[$_key.'_select'] = '*';
						}
					}
					else
					{
						$_POST[$key] = $val;
					}
				}
				$this -> edit_task = true;
			}
			else
			{
				$this -> top_notice = 'WEB不可编辑的任务计划。';
			}
		}
		
		$this -> crontab_list = $this -> indexs -> get_task_list();
		$this -> indexs -> log_insert($this -> top_notice . $this -> notice);
		$this -> _view('crontab');
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
		$html = file_get_contents('http://amysql.com/index.php?c=index&a=AMH&tag=ajax&V=2.0');
		$html = htmlspecialchars($html);
		$html = str_replace('[br]', '<br />', $html);
		$html = preg_replace('/\[url\]([a-z\_]+)\[\/url\]/i', '<a href="http://amysql.com/AMH.htm?tag=$1" target="_blank"> http://amysql.com/AMH.htm?tag=$1</a>', $html);
		echo $html;
		exit();
	}

}