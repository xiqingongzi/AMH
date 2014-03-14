<?php

// System Setting **********************************************

$Config['HttpPath'] = false;				// Active index.php/Controller/Action/name/value Module
$Config['Filter'] = true;					// Active Filter $_GET、$_POST、$_COOKIE、$_FILES
$Config['XSS'] = true;						// Active XSS Security
$Config['SessionStart'] = true;				// Active SESSION
$Config['DebugPhp'] = false;				// Active PHP Debug Info
$Config['DebugSql'] = false;				// Active MySQL Debug Info
$Config['CharSet'] = 'utf-8';				// Set Character
$Config['UrlControllerName'] = 'c';			// Custom Controller Name Example: index.php?c=index
$Config['UrlActionName'] = 'a';				// Custom Action Name Example: index.php?c=index&a=IndexAction						


// Default MySQL Database Set *****************************************

$Config['ConnectTag'] = 'default';				// MySQL Connect Tags,Support Multi-Connection
$Config['Host'] = 'localhost';					// Mysql Host Address
$Config['User'] = 'root';						// Mysql Username
$Config['Password'] = 'MysqlPass';				// Mysql Password
$Config['DBname'] = 'amh';						// Database Name