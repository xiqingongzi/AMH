<?php

/************************************************
 * Amysql Host - AMH 4.2
 * Amysql.com 
 * @param Object backup Panel Backup Controller
 * Update:2013-09-05
 * 
 */

class backup extends AmysqlController
{
	public $indexs = null;
	public $backups = null;
	public $notice = null;
	public $top_notice = null;

	// Load Data Model
	function AmysqlModelBase()
	{
		if($this -> indexs) return;
		$this -> _class('Functions');
		$this -> indexs = $this ->  _model('indexs');
		$this -> backups = $this ->  _model('backups');
	}

	// Default Action
	function IndexAction()
	{
		$this -> backup_list();
	}
	
	// Data Backup List
	function backup_list()
	{
		$this -> title = 'BackUps List - BackUp - AMH';
		$this -> AmysqlModelBase();
		Functions::CheckLogin();

		if(isset($_GET['category']) && $_GET['category'] == 'backup_remote')
		{
			$_GET['a'] = 'backup_remote';
			$this -> backup_remote();
			exit();
		}

		$this -> status = 'error';
		if (isset($_GET['del']))
		{
			$del_id = (int)$_GET['del'];
			$del_info = $this -> backups -> get_backup($del_id);
			if (isset($del_info['backup_file']))
			{
				$file = str_replace('.amh', '', $del_info['backup_file']);
				$cmd = "amh rm_backup $file";
				$cmd = Functions::trim_cmd($cmd);
				$result = shell_exec($cmd);
				$this -> status = 'success';
				$this -> notice = "Delete File ({$file}.amh) Done";
			}
			
		}

		$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
		$page_sum = 20;

		$this -> backups -> backup_list_update();
		$backup_list = $this -> backups -> get_backup_list($page, $page_sum);

		$total_page = ceil($backup_list['sum'] / $page_sum);						
		$page_list = Functions::page('BackupList', $backup_list['sum'], $total_page, $page, 'c=backup&a=backup_list&category=backup_list');		// PageNavList

		$this -> page = $page;
		$this -> total_page = $total_page;
		$this -> backup_list = $backup_list;
		$this -> page_list = $page_list;
		
		$this -> indexs -> log_insert($this -> notice);
		$this -> _view('backup_list');
	}

	
	// Remote Settings
	function backup_remote()
	{
		$this -> title = 'Remote Settings - Backup - AMH';
		$this -> AmysqlModelBase();
		Functions::CheckLogin();
		$this -> status = 'error';
		$input_item = array('remote_type', 'remote_status', 'remote_ip', 'remote_path', 'remote_user', 'remote_password');

		// Connect Test
		if (isset($_GET['check']))
		{
			$id = (int)$_GET['check'];
			$data = $this -> backups -> get_backup_remote($id);
			if($data['remote_type'] == 'FTP')
				$cmd = "amh BRftp check $id";
			if($data['remote_type'] == 'SSH')
				$cmd = "amh BRssh check $id";
			if ($cmd)
			{
				$cmd = Functions::trim_cmd($cmd);
				$result = shell_exec($cmd);
				$result = trim(Functions::trim_result($result), "\n ");
				echo $result;
			}
			exit();
		}
		// Save Remote Settings
		if (isset($_POST['save']))
		{
			$save = true;
			foreach ($input_item as $val)
			{
				if(empty($_POST[$val]))
				{
					$this -> notice = 'Add Remote Settings Failed,Please InPut EveryBlock，* Is must Be Filled.';
					$save = false;
				}
			}
			if($save)
			{
				$id = $this -> backups -> backup_remote_insert();
				if ($id)
				{
					$this -> status = 'success';
					$this -> notice = 'ID:' . $id . ' Add Remote Settings Success.';
					$_POST = array();
				}
				else
					$this -> notice = ' Add Remote Settings Failed.';
			}
		}

		// Delete Remote Settings
		if (isset($_GET['del']))
		{
			$id = (int)$_GET['del'];
			if(!empty($id))
			{
				$result = $this -> backups -> backup_remote_del($id);
				if ($result)
				{
					$this -> status = 'success';
					$this -> top_notice = 'ID:' . $id . ' Delete Remote BackUp Settings Success.';
				}
				else
					$this -> top_notice = 'ID:' . $id . ' Delete Remote BackUpSettingsFailed.';
			}
		}

		// edit Remote Settings
		if (isset($_GET['edit']))
		{
			$id = (int)$_GET['edit'];
			$_POST = $this -> backups -> get_backup_remote($id);
			if($_POST['remote_id'])
			{
				$this -> edit_remote = true;
			}
		}

		// Save edited Remote Settings
		if (isset($_POST['save_edit']))
		{
			$id = $_POST['remote_id'] = (int)$_POST['save_edit'];
			$save = true;
			foreach ($input_item as $val)
			{
				if(empty($_POST[$val]) && $val != 'remote_password')
				{
					$this -> notice = 'ID:' . $id . ' edit Remote BackUp Settings Failed.';
					$save = false;
					$this -> edit_remote = true;
				}
			}
			if ($save)
			{
				$result = $this -> backups -> backup_remote_update();
				if ($result)
				{
					$this -> status = 'success';
					$this -> notice = 'ID:' . $id . ' edit  Remote BackUp Settings Success.';
					$_POST = array();
				}
				else
				{
					$this -> notice = 'ID:' . $id . ' edit  Remote BackUp Settings Failed.';
					$this -> edit_remote = true;
				}
			}
			
		}

		$this -> remote_list = $this -> backups -> backup_remote_list();
		$this -> indexs -> log_insert($this -> notice);
		$this -> _view('backup_remote');
	}

	// BackUp Now
	function backup_now()
	{
		$this -> title = 'BackUp - BackUp - AMH';
		$this -> AmysqlModelBase();
		Functions::CheckLogin();
		$this -> status = 'error';

		if (isset($_POST['backup_now']))
		{
			$backup_retemo = ($_SESSION['amh_config']['DataPrivate']['config_value'] != 'on' && !empty($_POST['backup_retemo'])) ? $_POST['backup_retemo'] : 'n';
			$backup_options = (!empty($_POST['backup_options'])) ? $_POST['backup_options'] : 'y';
			$backup_password = (!empty($_POST['backup_password'])) ? $_POST['backup_password'] : 'n';
			$backup_comment = (!empty($_POST['backup_comment'])) ? $_POST['backup_comment'] : '';

			if ((!empty($_POST['backup_password2']) || !empty($_POST['backup_password'])) && $_POST['backup_password'] != $_POST['backup_password2'])
			{
				$this -> notice = ' The Two Password Is Not Match' ;
			}
			else
			{
				set_time_limit(0);
				$this -> category = $category;
				$this -> _view('backup_now_ing');
				$cmd = "amh backup $backup_retemo $backup_options $backup_password $backup_comment";
				$cmd = Functions::trim_cmd($cmd);
				$popen_handle = popen($cmd, 'r');
				$i = 0;
				$_i = 50;
				echo '<div id="show_result">';
				while(!feof($popen_handle))
				{
					$line = fgets($popen_handle);
					echo $line . '<br />';
					if($i%200 == 0) ++$_i;
					if($i%$_i == 0) echo "<script>amh_cmd_ing();</script>\n";
					++$i;
					if(!empty($line)) $result = $line;
				}
				$backup_ing_status = json_encode((pclose($popen_handle)));
				$result_status = (!$backup_ing_status) ? true : false;
				if ($result_status)
				{
					$this -> status = 'success';
					$this -> notice = $result . ' Create BackUp Files Done.';
					$_POST = array();
				}
				else
				{
					$this -> status = 'error';
					$this -> notice = $result . ' BackUp Files Create Failed.' ;
				}
				$notice = json_encode($this -> notice);
				echo "<script>amh_cmd_ing();backup_ing_status = {$backup_ing_status}; backup_result = {$notice}; backup_end();</script>$line</div>";
				$this -> indexs -> log_insert($this -> notice);
				exit();
			}
		}

		$this -> indexs -> log_insert($this -> notice);
		$this -> _view('backup_now');
	}

	
	// OneKeyRevert 
	function backup_revert()
	{
		$this -> title = 'Revert  - BackUp - AMH';
		$this -> AmysqlModelBase();
		Functions::CheckLogin();
		$this -> status = 'error';

		$revert_id = isset($_GET['revert_id']) ? (int)$_GET['revert_id'] : '';
		if (!empty($revert_id))
			$revert = $this -> backups -> get_backup($revert_id);

		$this -> revert = $revert;
		if (isset($_POST['revert_submit']))
		{
			set_time_limit(0);
			$backup_file = $revert['backup_file'];
			$backup_password = empty($_POST['backup_password']) ? 'n' : $_POST['backup_password'];
			$this -> category = $category;
			$this -> _view('backup_revert_ing');
			$cmd = "amh revert $backup_file $backup_password noreload";
			$cmd = Functions::trim_cmd($cmd);
			$popen_handle = popen($cmd, 'r');
			$i = 0;
			$_i = 50;
			echo '<div id="show_result">';
			while(!feof($popen_handle))
			{
				$line = fgets($popen_handle);
				echo $line . '<br />';
				if($i%200 == 0) ++$_i;
				if($i%$_i == 0) echo "<script>amh_cmd_ing();</script>\n";
				++$i;
				if(!empty($line)) $result = $line;
			}
			$revert_ing_status = json_encode((pclose($popen_handle)));
			$result_status = (!$revert_ing_status) ? true : false;
			if ($result_status)
			{
				$this -> status = 'success';
				$this -> notice = $backup_file . '  Data  Revert Success.';
				$_POST = array();
			}
			else
			{
				$this -> status = 'error';
				$this -> notice = $result . $backup_file . ' Revert Failed.' ;
			}
			$notice = json_encode($this -> notice);
			echo "<script>amh_cmd_ing();revert_ing_status = {$revert_ing_status}; revert_result = {$notice}; revert_end();</script>$line</div>";
			$this -> indexs -> log_insert($this -> notice);
			exit();
		}

		$this -> indexs -> log_insert($this -> notice);
		$this -> _view('backup_revert');
	}
		
}

?>