<?php


class Functions
{
	
	// 过滤结果数据
	function trim_result($result)
	{
		$result = trim($result);
		$result = str_replace(
			array(
					'[LNMP/Nginx] Amysql Host - AMH 1.1',
					'http://Amysql.com',
					'============================================================='
			), '', $result);

		Return $result;
	}
}

?>