<?php

class indexs extends AmysqlModel
{

	// 更改密码
	function change_pass($user_password)
	{
		$user_name = $_SESSION['amh_user'];
		$user_password = md5(md5($user_password));
		$sql = "UPDATE amh_user SET user_password = '$user_password' WHERE user_name = '$user_name'";
		$this -> _query($sql);
		Return $this -> Affected;
	}
	
	// 日志写记录
	function log_insert($txt)
	{
		if(empty($txt)) return;
		$data['log_user_id'] = $_SESSION['amh_user_id'];
		$data['log_text'] = htmlspecialchars($txt);
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
		$password = md5(md5($password));
		$sql = "SELECT user_id FROM amh_user WHERE user_name = '$user' AND user_password = '$password'";
		$data = mysql_fetch_assoc(mysql_query($sql));
		if(isset($data['user_id']))
			Return $data['user_id'];
		Return false;
	}

	// 是否允许登录
	function login_allow()
	{
		$login_ip = $_SERVER["REMOTE_ADDR"];
		$sql = "SELECT * FROM amh_login WHERE login_ip = '$login_ip' AND login_error_tag = '1' ORDER BY login_id DESC";
		$result = mysql_query($sql);
		$num = mysql_num_rows($result);
		$n = ceil($num/5);
		if ($num >= 5 && $num%5 == 0)
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
		$sql = "SELECT * FROM amh_host";
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
		$cmd = 'amh ls /usr/local/nginx/conf/vhost';
		$result = trim(shell_exec($cmd), "\n");
		$run_list = explode("\n", $result);
		foreach ($run_list as $key=>$val)
		{
			if(!empty($val))
			{
				$cmd = 'amh cat /usr/local/nginx/conf/vhost/' . $val;
				$host_list[$val]['conf'] = trim(shell_exec($cmd), "\n");
				$host_list[$val]['host_nginx'] = 1;
			}
		}

		$cmd = 'amh ls /usr/local/nginx/conf/vhost_stop';
		$result = trim(shell_exec($cmd), "\n");
		$stop_list = explode("\n", $result);
		foreach ($stop_list as $key=>$val)
		{
			if(!empty($val))
			{
				$cmd = 'amh cat /usr/local/nginx/conf/vhost_stop/' . $val;
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
			$cmd = 'amh cat /usr/local/php/var/run/pid/php-fpm-' . $host_list[$key]['host_domain'] . '.pid';
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

		$cmd = 'amh /root/amh/host add';
		foreach ($data_name as $key=>$val)
			$cmd .= (isset($data[$val]) && !empty($data[$val])) ? ' ' . $data[$val] : ' 0 ';
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

		$cmd = 'amh /root/amh/host edit';
		foreach ($data_name as $key=>$val)
			$cmd .= (isset($_POST[$val]) && !empty($_POST[$val])) ? ' ' . $_POST[$val] : ' 0 ';

		Return Functions::trim_result(shell_exec($cmd));
	}

	// 删除host ssh
	function host_del_ssh($host_domain)
	{
		Return Functions::trim_result(shell_exec("amh /root/amh/host del $host_domain"));
	}



	
	
	// FTP ********************************************************************************
	// ftp列表
	function ftp_list()
	{
		$sql = "SELECT * FROM amh_ftp";
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
			
		$cmd = 'amh /root/amh/ftp add';
		foreach ($data_name as $key=>$val)
			$cmd .= (isset($_POST[$val]) && !empty($_POST[$val])) ? ' ' . $_POST[$val] : ' 0 ';

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


		$cmd = 'amh /root/amh/ftp edit';
		foreach ($data_name as $key=>$val)
			$cmd .= (isset($_POST[$val]) && !empty($_POST[$val])) ? ' ' . $_POST[$val] : ' 0 ';

		$result_change = Functions::trim_result(shell_exec($cmd));

		if (!empty($_POST['ftp_password']))
		{
			$cmd = 'amh /root/amh/ftp pass ' . $_POST['ftp_name'] . ' ' . $_POST['ftp_password'];
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
		Return Functions::trim_result(shell_exec("amh /root/amh/ftp del $del_name"));
	}


	
}

?>