<?php

class indexs extends AmysqlModel
{

	// 取得系统配置
	function get_amh_config()
	{
		$sql = "SELECT * FROM amh_config";
		$result = $this -> _query($sql);
		while ($rs = mysql_fetch_assoc($result))
			$data[$rs['config_name']] = $rs;
		Return $data;
	}
	// 更新系统配置
	function up_amh_config()
	{
		$data_name = array('HelpDoc', 'LoginErrorLimit');
		$Affected = 0;
		foreach ($data_name as $val)
		{
			if (isset($_POST[$val]) && $_POST[$val] != $_POST[$val.'_old'])
			{
				$this -> _update('amh_config', array('config_value' => $_POST[$val]), " WHERE config_name = '$val' ");
				$Affected += $this -> Affected;
			}
		}
		Return $Affected;
	}

	// 更改密码
	function change_pass($user_password)
	{
		$user_name = $_SESSION['amh_user_name'];
		$user_password = md5(md5($user_password.'_amysql-amh'));
		$sql = "UPDATE amh_user SET user_password = '$user_password' WHERE user_name = '$user_name'";
		$this -> _query($sql);
		Return $this -> Affected;
	}
	
	// 日志写记录
	function log_insert($txt)
	{
		if(empty($txt)) return;
		$data['log_user_id'] = $_SESSION['amh_user_id'];
		$data['log_text'] = $txt;
		$data['log_ip'] = $_SERVER["REMOTE_ADDR"];
		$this -> _insert('amh_log', $data);
	}

	// 日志列表
	function log_list()
	{
		$sql = "SELECT al.*, au.user_name FROM amh_log AS al LEFT JOIN amh_user AS au ON al.log_user_id = au.user_id ORDER BY al.log_id DESC LIMIT 10";
		Return $this -> _all($sql);
	}

	// 登录验证
	function logins($user, $password)
	{
		$password = md5(md5($password.'_amysql-amh'));
		$sql = "SELECT user_id FROM amh_user WHERE user_name = '$user' AND user_password = '$password'";
		$data = mysql_fetch_assoc(mysql_query($sql));
		if(isset($data['user_id']))
			Return $data['user_id'];
		Return false;
	}

	// 是否允许登录
	function login_allow()
	{
		$amh_config = $this -> get_amh_config();
		$LoginErrorLimit = (int)$amh_config['LoginErrorLimit']['config_value'];
		$LoginErrorLimit = $LoginErrorLimit > 0 ? $LoginErrorLimit : 5;

		$login_ip = $_SERVER["REMOTE_ADDR"];
		$sql = "SELECT * FROM amh_login WHERE login_ip = '$login_ip' AND login_error_tag = '1' ORDER BY login_id DESC";
		$result = mysql_query($sql);
		$num = mysql_num_rows($result);
		$n = ceil($num/$LoginErrorLimit);
		if ($num >= $LoginErrorLimit && $num%$LoginErrorLimit == 0)
		{		
			$data = mysql_fetch_assoc($result);
			$allow_time = strtotime($data['login_time']) + pow($n,3)*60;
			if (time() < $allow_time)
				Return array('status' => false, 'allow_time' => $allow_time, 'login_error_sum' => $num);
		}
		Return array('status' => true, 'login_error_sum' => $num);
	}

	// 登录写记录
	function login_insert($login_success, $user_name)
	{
		$login_ip = $_SERVER["REMOTE_ADDR"];
		$login_error_tag = $login_success ? '0' : '1';
		$sql = "INSERT INTO amh_login(login_user_name, login_ip, login_success, login_error_tag) VALUES('$user_name', '$login_ip', '$login_success', '$login_error_tag')";
		$this -> _query($sql);

		if($login_success)
		{
			$sql = "UPDATE amh_login SET login_error_tag = '0' WHERE login_ip = '$login_ip'";
			$this -> _query($sql);
		}
	}

	// 登录记录列表
	function login_list()
	{
		$sql = "SELECT * FROM amh_login ORDER BY login_id DESC LIMIT 10";
		Return $this -> _all($sql);
	}

	// 数据库列表
	function databases()
	{
		$sql = "SHOW DATABASES";
		$result = mysql_query($sql);
		while ($rs = mysql_fetch_assoc($result))
		{
			$DBname = $rs['Database'];
			$sql = "SHOW TABLES FROM `$DBname` ";
			$rs['sum'] = mysql_num_rows(mysql_query($sql));

			$sql = "SHOW CREATE DATABASE `$DBname` ";
			$collations = mysql_fetch_assoc(mysql_query($sql));
			$collations = explode(' ', $collations['Create Database']);
			$rs['collations'] = $collations[7];
			$data[] = $rs;
		}
		Return $data;
	}
	
	// HOST ********************************************************************************
	// host列表
	function host_list()
	{
		$sql = "SELECT * FROM amh_host ORDER BY host_id ASC";
		Return $this -> _all($sql);
	}

	// 取得host
	function get_host($host_domain)
	{
		$sql = "SELECT * FROM amh_host WHERE host_domain = '$host_domain'";
		Return $this -> _row($sql);
	}

	// host列表更新
	function host_update()
	{
		$host_list = array();
		$cmd = 'amh ls_vhost';
		$result = trim(shell_exec($cmd), "\n");
		$run_list = explode("\n", $result);
		foreach ($run_list as $key=>$val)
		{
			if(!empty($val))
			{
				$cmd = 'amh cat_vhost ' . substr($val, 0, -5);
				$host_list[$val]['conf'] = trim(shell_exec($cmd), "\n");
				$host_list[$val]['host_nginx'] = 1;
			}
		}

		$cmd = 'amh ls_vhost_stop';
		$result = trim(shell_exec($cmd), "\n");
		$stop_list = explode("\n", $result);
		foreach ($stop_list as $key=>$val)
		{
			if(!empty($val))
			{
				$cmd = 'amh cat_vhost_stop ' . substr($val, 0, -5);
				$host_list[$val]['conf'] = trim(shell_exec($cmd), "\n");
				$host_list[$val]['host_nginx'] = 0;
			}
		}

		foreach ($host_list as $key=>$val)
		{
			$conf = $val['conf'];
			preg_match_all('/server_name(.*);#server_name end/', $conf, $host_server_name);
			$host_list[$key]['host_server_name'] = str_replace(' ', ',', trim($host_server_name[1][0]));

			preg_match_all('/root(.*);#root end/', $conf, $host_root);
			$host_list[$key]['host_root'] = trim($host_root[1][0]);

			preg_match_all('/index(.*);#index end/', $conf, $host_index_name);
			$host_list[$key]['host_index_name'] = str_replace(' ', ',', trim($host_index_name[1][0]));

			preg_match_all('/include rewrite\/(.*);#rewrite end/', $conf, $host_rewrite);
			$host_list[$key]['host_rewrite'] = trim($host_rewrite[1][0]);

			preg_match_all('/error_page 404 =(.*);#error_page end/', $conf, $host_not_found);
			$host_list[$key]['host_not_found'] = trim($host_not_found[1][0]);

			preg_match_all('/access_log(.*);#access_log end/', $conf, $host_log);
			$host_list[$key]['host_log'] = strpos($host_log[1][0] , 'access.log') !== false ? 1 : 0;

			preg_match_all('/error_log(.*);#error_log end/', $conf, $host_error_log);
			$host_list[$key]['host_error_log'] = strpos($host_error_log[1][0], 'error.log') !== false ? 1 : 0;

			$host_list[$key]['host_domain'] = str_replace('.conf', '', $key);
			$cmd = 'amh php_pid php-fpm-' . $host_list[$key]['host_domain'];
			$host_list[$key]['host_php'] = strlen(trim(shell_exec($cmd), "\n")) > 1 ? 1 : 0;
			unset($host_list[$key]['conf']);
		}

		$all_host_name = array();
		foreach ($host_list as $key=>$val)
		{
			$get_host = $this -> get_host($val['host_domain']);
			if (isset($get_host['host_domain']))
				$this -> _update('amh_host', $val, " WHERE host_domain = '$val[host_domain]' ");
			else
			{
			    $val['host_type'] = 'ssh';
				$this -> _insert('amh_host', $val);
			}
			$all_host_name[] = $val['host_domain'];
		}

		if(count($all_host_name) > 0)
		{
			$sql = "DELETE FROM amh_host WHERE host_domain NOT IN ('" . implode("','", $all_host_name) . "')";
			$this -> _query($sql);
		}
		else
		{
		    $sql = "TRUNCATE TABLE `amh_host`";
			$this -> _query($sql);
		}
	}

	//新增host ssh
	function host_insert_ssh($data)
	{
		$data_name = array('host_domain', 'host_server_name', 'host_index_name', 'host_rewrite', 'host_not_found', 'host_log', 'host_error_log');

		$cmd = 'amh host add';
		foreach ($data_name as $key=>$val)
			$cmd .= (isset($data[$val]) && !empty($data[$val])) ? ' ' . $data[$val] : ' 0 ';
		$cmd = Functions::trim_cmd($cmd);
		$result = shell_exec($cmd);
		Return Functions::trim_result($result);
	}

	// 新增host
	function host_insert($data)
	{
		$data_name = array('host_domain', 'host_root', 'host_server_name', 'host_index_name', 'host_rewrite', 'host_not_found', 'host_log', 'host_error_log');
		foreach ($data_name as $val)
			$insert_data[$val] = $data[$val];
		$insert_data['host_type'] = isset($data['host_type']) ? $data['host_type'] : 'web';
		$insert_data['host_root'] = '/home/wwwroot/' . $data['host_domain'] . '/web';
		$insert_data['host_log'] = ($data['host_log']) ? '1' : '0';
		$insert_data['host_error_log'] = ($data['host_error_log']) ? '1' : '0';
		Return $this -> _insert('amh_host', $insert_data);
	}

	// 编辑host
	function edit_host()
	{
		$data_name = array('host_domain', 'host_server_name',  'host_index_name', 'host_rewrite', 'host_not_found', 'host_log', 'host_error_log');
		$_POST['host_log'] = ($_POST['host_log']) ? 'on' : 'off';
		$_POST['host_error_log'] = ($_POST['host_error_log']) ? 'on' : 'off';

		$cmd = 'amh host edit';
		foreach ($data_name as $key=>$val)
			$cmd .= (isset($_POST[$val]) && !empty($_POST[$val])) ? ' ' . $_POST[$val] : ' 0 ';

		$cmd = Functions::trim_cmd($cmd);
		Return Functions::trim_result(shell_exec($cmd));
	}

	// 删除host ssh
	function host_del_ssh($host_domain)
	{
		$cmd = "amh host del $host_domain";
		$cmd = Functions::trim_cmd($cmd);
		Return Functions::trim_result(shell_exec($cmd));
	}



	
	
	// FTP ********************************************************************************
	// ftp列表
	function ftp_list()
	{
		$sql = "SELECT * FROM amh_ftp ORDER BY ftp_id ASC";
		Return $this -> _all($sql);
	}

	// 取得ftp
	function get_ftp($ftp_name)
	{
		$sql = "SELECT * FROM amh_ftp WHERE ftp_name = '$ftp_name'";
		Return $this -> _row($sql);
	}

	// ftp新增
	function ftp_insert($data)
	{
		$data['ftp_password'] = md5(md5($data['ftp_password']));
		$data_name = array('ftp_name', 'ftp_password', 'ftp_root', 'ftp_upload_bandwidth', 'ftp_download_bandwidth', 'ftp_upload_ratio', 'ftp_download_ratio', 'ftp_max_files', 'ftp_max_mbytes', 'ftp_max_concurrent', 'ftp_allow_time');
		foreach ($data_name as $val)
			$insert_data[$val] = $data[$val];
		$insert_data['ftp_type'] = isset($data['ftp_type']) ? $data['ftp_type'] : 'web';
		Return $this -> _insert('amh_ftp', $insert_data);
	}

	// ftp新增 ssh
	function ftp_insert_ssh()
	{
		if($_POST['ftp_root'] == 'index' || strpos($_POST['ftp_root'], '..') !== false || strpos($_POST['ftp_root'], '/') !== false ) 
			Return ' 禁止使用的根目录。';

		$data_name = array('ftp_name', 'ftp_password', 'ftp_root', 'ftp_upload_bandwidth', 'ftp_download_bandwidth', 'ftp_upload_ratio', 'ftp_download_ratio', 'ftp_max_files', 'ftp_max_mbytes', 'ftp_max_concurrent', 'ftp_allow_time');
		$_POST['ftp_root'] = '/home/wwwroot/' . $_POST['ftp_root'] . '/web';

		if (!is_dir($_POST['ftp_root']))
			Return ' 根目录不存在。';

		$get_ftp = $this -> get_ftp($_POST['ftp_name']);
		if (isset($get_ftp['ftp_name']))
			Return ' 已存在账号。';
			
		$cmd = 'amh ftp add';
		foreach ($data_name as $key=>$val)
			$cmd .= (isset($_POST[$val]) && !empty($_POST[$val])) ? ' ' . $_POST[$val] : ' 0 ';

		$cmd = Functions::trim_cmd($cmd);
		$result = shell_exec($cmd);
		Return Functions::trim_result($result);
	}

	// ftp更新列表
	function ftp_update($ftp_list_ssh)
	{
		$data_name = array('ftp_name', 'ftp_password', 'ftp_root', 'ftp_upload_bandwidth', 'ftp_download_bandwidth', 'ftp_upload_ratio', 'ftp_download_ratio', 'ftp_max_files', 'ftp_max_mbytes', 'ftp_max_concurrent', 'ftp_allow_time');
		$all_ftp_name = array();

		foreach ($ftp_list_ssh as $key=>$val)
		{
			list($ftp_name,$ftp_password,$uid,$gid,$gecos,$ftp_root,$ftp_upload_bandwidth,$ftp_download_bandwidth,$ftp_upload_ratio,$ftp_download_ratio,$ftp_max_concurrent,$ftp_max_files,$ftp_max_mbytes,$authorized_local_IPs,$refused_local_IPs,$authorized_client_IPs,$refused_client_IPs,$ftp_allow_time) = explode(':', $val);

			if (!empty($ftp_name))
			{
				$all_ftp_name[] = $ftp_name;
				$ftp_root = rtrim($ftp_root , './');
				foreach ($data_name as $key=>$val)
				{
					$data[$val] = $$val;
					if(empty($data[$val])) $data[$val] = '0';
				}
				
				$get_ftp = $this -> get_ftp($ftp_name);
				if (isset($get_ftp['ftp_name']))
				{
					unset($data['ftp_password']);
					$this -> _update('amh_ftp', $data, " WHERE ftp_name = '$ftp_name' ");
				}
				else
				{
					$data['ftp_type'] = 'ssh';
					$this -> ftp_insert($data);
				}
			}
		}

		if(count($all_ftp_name) > 0)
		{
			$sql = "DELETE FROM amh_ftp WHERE ftp_name NOT IN ('" . implode("','", $all_ftp_name) . "')";
			$this -> _query($sql);
		}
		else
		{
		    $sql = "TRUNCATE TABLE `amh_ftp`";
			$this -> _query($sql);
		}
	}

	// 编辑ftp
	function edit_ftp()
	{

		if($_POST['ftp_root'] == 'index' || strpos($_POST['ftp_root'], '..') !== false || strpos($_POST['ftp_root'], '/') !== false ) 
			Return ' 禁止使用的根目录。';

		$data_name = array('ftp_name', 'ftp_password', 'ftp_root', 'ftp_upload_bandwidth', 'ftp_download_bandwidth', 'ftp_upload_ratio', 'ftp_download_ratio', 'ftp_max_files', 'ftp_max_mbytes', 'ftp_max_concurrent', 'ftp_allow_time');
		$_POST['ftp_root'] = '/home/wwwroot/' . $_POST['ftp_root'] . '/web';

		if (!is_dir($_POST['ftp_root']))
			Return ' 根目录不存在。';


		$cmd = 'amh ftp edit';
		foreach ($data_name as $key=>$val)
			$cmd .= (isset($_POST[$val]) && !empty($_POST[$val])) ? ' ' . $_POST[$val] : ' 0 ';

		$cmd = Functions::trim_cmd($cmd);
		$result_change = Functions::trim_result(shell_exec($cmd));

		if (!empty($_POST['ftp_password']))
		{
			$cmd = 'amh ftp pass ' . $_POST['ftp_name'] . ' ' . $_POST['ftp_password'];
			$cmd = Functions::trim_cmd($cmd);
			$result_pass = Functions::trim_result(shell_exec($cmd));
			if (strpos($result_pass, '[OK]') !== false)
			{
				$data['ftp_password'] = md5(md5($_POST['ftp_password']));
				$this -> _update('amh_ftp', $data, " WHERE ftp_name = '$_POST[ftp_name]' ");
			}
		}
		Return array($result_change, $result_pass);
	}


	// 删除ftp ssh
	function ftp_del_ssh($del_name)
	{
		$cmd = "amh ftp del $del_name";
		$cmd = Functions::trim_cmd($cmd);
		Return Functions::trim_result(shell_exec($cmd));
	}




	
	
	// Backup ********************************************************************************

	// 取得列表
	function get_backup_list($page = 1, $page_sum = 20)
	{
		$sql = "SELECT * FROM amh_backup_list";
		$sum = $this -> _sum($sql);

		$limit = ' LIMIT ' . ($page-1)*$page_sum . ' , ' . $page_sum;
		$sql = "SELECT * FROM amh_backup_list ORDER BY backup_id ASC $limit";
		Return array('data' => $this -> _all($sql), 'sum' => $sum);
	}
	// 取得指定备份
	function get_backup($id = null, $backup_file = null)
	{
		$where = '';
		$where .= (!empty($id)) ? " AND backup_id = '$id' " : '';
		$where .= (!empty($backup_file)) ? " AND backup_file = '$backup_file' " : '';
		$sql = "SELECT * FROM amh_backup_list WHERE 1 $where ";
		Return $this -> _row($sql);
	}
	// 更新列表
	function backup_list_update()
	{
		$cmd = 'amh ls_backup';
		$result = trim(shell_exec($cmd), "\n");
		$backup_list = explode("\n", $result);

		foreach ($backup_list as $key=>$val)
		{
			$val_arr = explode(' ', ereg_replace("[ ]{1,}", " ",$val));
			if(substr($val_arr[count($val_arr)-1], -3, 3) == 'amh')
			{
				$backup_file = $val_arr[8];
				$backup_size = number_format($val_arr[4]/1024/1024, 2);
				$backup_password = (strpos($backup_file, 'tar.gz') !== false) ? '0' : '1';
				$backup_file_arr = explode('.', $backup_file);
				$backup_time = date('Y-m-d H:i:s', strtotime(str_replace('-', '', $backup_file_arr[0])));
				$all_backup_file[] = $backup_file;
				
				$backup_info = $this -> get_backup(null, $backup_file);
				if(isset($backup_info['backup_id']))
				{
					$this -> _update('amh_backup_list', array('backup_size' => $backup_size, 'backup_password' => $backup_password, 'backup_time' => $backup_time), " WHERE backup_file = '$backup_file' ");
				}
				else
				{
					$this -> _insert('amh_backup_list', array('backup_file' => $backup_file, 'backup_size' => $backup_size, 'backup_password' => $backup_password, 'backup_time' => $backup_time));
				}
			}
		}

		if(count($all_backup_file) > 0)
		{
			$sql = "DELETE FROM amh_backup_list WHERE backup_file NOT IN ('" . implode("','", $all_backup_file) . "')";
			$this -> _query($sql);
		}
		else
		{
		    $sql = "TRUNCATE TABLE `amh_backup_list`";
			$this -> _query($sql);
		}

	}
	// 远程配置列表
	function backup_remote_list()
	{
		$sql = "SELECT * FROM amh_backup_remote ORDER BY remote_id ASC ";
		Return $this -> _all($sql);	
	}

	// 保存远程配置
	function backup_remote_insert()
	{
		$data_name = array('remote_type', 'remote_status', 'remote_ip', 'remote_path', 'remote_user', 'remote_pass_type', 'remote_password', 'remote_comment');
		foreach ($data_name as $val)
			$insert_data[$val] = $_POST[$val];
		Return $this -> _insert('amh_backup_remote', $insert_data);
	}

	// 编辑保存远程配置
	function backup_remote_update()
	{
		$data_name = array('remote_type', 'remote_status', 'remote_ip', 'remote_path', 'remote_user', 'remote_pass_type', 'remote_password', 'remote_comment');
		foreach ($data_name as $val)
		{
			if($val != 'remote_password' || !empty($_POST['remote_password']))
				$insert_data[$val] = $_POST[$val];
		}
		Return $this -> _update('amh_backup_remote', $insert_data,  " WHERE remote_id = '$_POST[remote_id]' ");
	}
	
	// 取得远程配置
	function get_backup_remote($remote_id)
	{
		$sql = "SELECT * FROM amh_backup_remote WHERE remote_id = '$remote_id'";
		Return $this -> _row($sql);
	}

	// 删除远程配置
	function backup_remote_del($remote_id)
	{
		$sql = "DELETE FROM amh_backup_remote WHERE remote_id = '$remote_id'";
		$this -> _query($sql);
		Return $this -> Affected;
	}




	// Task ********************************************************************************
	// 取得任务
	function get_task($id = null, $crontab_md5 = null)
	{
		$where = '';
		$where .= (!empty($id)) ? " AND crontab_id = '$id' " : '';
		$where .= (!empty($crontab_md5)) ? " AND crontab_md5 = '$crontab_md5' " : '';
		$sql = "SELECT * FROM amh_crontab WHERE 1 $where ";
		Return $this -> _row($sql);
	}
	// 取得任务属性
	function get_task_value($tag)
	{
		foreach ($_POST as $key=>$val)
		{
			if(strpos($key, $tag) !== false)
			{
				if(strpos($key, 'time') !== false)
					Return $_POST[$tag . '_time'];
				elseif(strpos($key, 'period') !== false)
					Return $_POST[$tag . '_period_start'] . '-' . $_POST[$tag . '_period_end'];
				elseif(strpos($key, 'average') !== false)
					Return $_POST[$tag . '_average_start'] . '-' . $_POST[$tag . '_average_end'] . '/' . $_POST[$tag . '_average_input'];
				elseif(strpos($key, 'respectively') !== false)
					Return implode(',', $_POST[$tag . '_respectively']);
			}
		}
	}
	// 新增任务
	function insert_task()
	{
		$crontab_ssh = trim($_POST['crontab_ssh']);
		if(substr($crontab_ssh, 0, 3) != 'amh')
			Return false;

		$crontab_ssh = Functions::trim_cmd($crontab_ssh);
		$crontab_minute = $this -> get_task_value('minute');
		$crontab_hour = $this -> get_task_value('hour');
		$crontab_day = $this -> get_task_value('day');
		$crontab_month = $this -> get_task_value('month');
		$crontab_week = $this -> get_task_value('week');
		$crontab_type = 'web';

		$crontab_tmp = '/home/wwwroot/index/tmp/crontab.tmp';
		$cmd = "amh crontab -l > $crontab_tmp";
		$result = shell_exec($cmd);
		$crontab_content = file_get_contents($crontab_tmp) . $crontab_minute . ' ' . $crontab_hour . ' ' . $crontab_day . ' ' . $crontab_month . ' ' . $crontab_week . ' ' . $crontab_ssh . "\n";
		file_put_contents($crontab_tmp, $crontab_content, LOCK_EX);
		$cmd = "amh crontab $crontab_tmp";
		$result = shell_exec($cmd);

		$crontab_md5 = md5($crontab_minute.$crontab_hour.$crontab_day.$crontab_month.$crontab_week.$crontab_ssh);
		Return $this -> _insert('amh_crontab', array('crontab_minute' => $crontab_minute, 'crontab_hour' => $crontab_hour, 'crontab_day' => $crontab_day, 'crontab_month' => $crontab_month, 'crontab_week' => $crontab_week, 'crontab_ssh' => $crontab_ssh, 'crontab_type' => $crontab_type, 'crontab_md5' => $crontab_md5));
	}
	// 删除任务
	function del_task($id = null, $crontab_md5 = null)
	{
		if (!empty($id))
		{
			$task = $this -> get_task($id);
			if (!isset($task['crontab_id'])) Return false;
		}
		if(!empty($crontab_md5)) $task['crontab_md5'] = $crontab_md5;

		$cmd = 'amh crontab -l';
		$result = shell_exec($cmd);
		$task_list = explode("\n", $result);
		$crontab_content = '';
		$crontab_tmp = '/home/wwwroot/index/tmp/crontab.tmp';

		foreach ($task_list as $key=>$val)
		{
			$val_arr = explode(' ', ereg_replace("[ ]{1,}", " ", trim($val)));
			if($val_arr[0] != '#' && $val_arr[0][0] != '#' && count($val_arr) > 5)
			{
				$crontab_ssh = '';
				foreach ($val_arr as $k=>$v)
				{
					if($k > 4) $crontab_ssh .= ' ' . $v;
				}
				$crontab_ssh = trim($crontab_ssh);
				$crontab_md5 = md5($val_arr[0].$val_arr[1].$val_arr[2].$val_arr[3].$val_arr[4].$crontab_ssh);

				if ($crontab_md5 != $task['crontab_md5'])
					$crontab_content .= $val . "\n";
			}
		}

		file_put_contents($crontab_tmp, $crontab_content, LOCK_EX);
		$cmd = "amh crontab $crontab_tmp";
		shell_exec($cmd);
		Return true;
		
	}
	// 保存任务
	function save_task($id)
	{
		$task = $this -> get_task($id);
		if (!isset($task['crontab_id']) || $task['crontab_type'] == 'ssh') Return false;

		$crontab_ssh = trim($_POST['crontab_ssh']);
		if(substr($crontab_ssh, 0, 3) != 'amh')
			Return false;

		$crontab_ssh = Functions::trim_cmd($crontab_ssh);
		$crontab_minute = $this -> get_task_value('minute');
		$crontab_hour = $this -> get_task_value('hour');
		$crontab_day = $this -> get_task_value('day');
		$crontab_month = $this -> get_task_value('month');
		$crontab_week = $this -> get_task_value('week');
		$crontab_type = 'web';
		$crontab_md5 = md5($crontab_minute.$crontab_hour.$crontab_day.$crontab_month.$crontab_week.$crontab_ssh);

		$this -> _update('amh_crontab', array('crontab_minute' => $crontab_minute, 'crontab_hour' => $crontab_hour, 'crontab_day' => $crontab_day, 'crontab_month' => $crontab_month, 'crontab_week' => $crontab_week, 'crontab_ssh' => $crontab_ssh, 'crontab_type' => $crontab_type, 'crontab_md5' => $crontab_md5), " WHERE crontab_id = '$id' ");
		$this -> del_task(null, $task['crontab_md5']);

		$crontab_tmp = '/home/wwwroot/index/tmp/crontab.tmp';
		$cmd = "amh crontab -l > $crontab_tmp";
		$result = shell_exec($cmd);
		$crontab_content = file_get_contents($crontab_tmp) . $crontab_minute . ' ' . $crontab_hour . ' ' . $crontab_day . ' ' . $crontab_month . ' ' . $crontab_week . ' ' . $crontab_ssh . "\n";
		file_put_contents($crontab_tmp, $crontab_content, LOCK_EX);
		$cmd = "amh crontab $crontab_tmp";
		$result = shell_exec($cmd);
		Return true;
	}
	// 取得任务列表
	function get_task_list()
	{
		$cmd = 'amh crontab -l';
		$result = shell_exec($cmd);
		$task_list = explode("\n", $result);

		foreach ($task_list as $key=>$val)
		{
			$val_arr = explode(' ', ereg_replace("[ ]{1,}", " ", trim($val)));
			if($val_arr[0] != '#' && $val_arr[0][0] != '#' && count($val_arr) > 5)
			{
				$crontab_minute = $val_arr[0];
				$crontab_hour = $val_arr[1];
				$crontab_day = $val_arr[2];
				$crontab_month = $val_arr[3];
				$crontab_week = $val_arr[4];
				$crontab_ssh = '';
				$crontab_type = 'ssh';
				foreach ($val_arr as $k=>$v)
				{
					if($k > 4) $crontab_ssh .= ' ' . $v;
				}
				$crontab_ssh = trim($crontab_ssh);

				$crontab_md5 = md5($crontab_minute.$crontab_hour.$crontab_day.$crontab_month.$crontab_week.$crontab_ssh);
				$all_task_list[] = $crontab_md5;
				
				$task_info = $this -> get_task(null, $crontab_md5);
				if(!isset($task_info['crontab_id']))
				{
					$this -> _insert('amh_crontab', array('crontab_minute' => $crontab_minute, 'crontab_hour' => $crontab_hour, 'crontab_day' => $crontab_day, 'crontab_month' => $crontab_month, 'crontab_week' => $crontab_week, 'crontab_ssh' => $crontab_ssh, 'crontab_type' => $crontab_type, 'crontab_md5' => $crontab_md5));
				}
			}
		}

		if(count($all_task_list) > 0)
		{
			$sql = "DELETE FROM amh_crontab WHERE crontab_md5 NOT IN ('" . implode("','", $all_task_list) . "')";
			$this -> _query($sql);
		}
		else
		{
		    $sql = "TRUNCATE TABLE `amh_crontab`";
			$this -> _query($sql);
		}

		$sql = "SELECT * FROM amh_crontab ORDER BY crontab_id ASC ";
		Return $this -> _all($sql);
	}
	
}

?>