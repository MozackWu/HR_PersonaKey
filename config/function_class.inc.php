<?php
	//驗證LoginToken
	function Verify_LoginToken($Verify_Token,$Time)
	{
		//Token 一小時內有效
		if((time() - $Time) < 3600)
		{
			// return $Verify_Token;
			$Token1 = base64_encode(md5(base64_encode('95430016') . "HR System" . $Time ));
			$Token2 = base64_encode(md5(base64_encode('00023817') . "HR System" . $Time ));
			$Token3 = base64_encode(md5(base64_encode('00127691') . "HR System" . $Time ));
			
			if($Verify_Token === $Token1 || $Verify_Token === $Token2 || $Verify_Token === $Token3)
				return True;
			
		}
		else
			return false;
	}

	function GetSecurCode($Verify_Token,$Time)
	{
		$Token1 = base64_encode(md5(base64_encode('95430016') . "HR System" . $Time ));
		$Token2 = base64_encode(md5(base64_encode('00023817') . "HR System" . $Time ));
		$Token3 = base64_encode(md5(base64_encode('00127691') . "HR System" . $Time ));

		if($Verify_Token === $Token1 )
			return '95430016';
		else if($Verify_Token === $Token2 )
			return '00023817';
		else if($Verify_Token === $Token3 )
			return '00127691';
		else 
			return '-';
	}
	
	/**
	* 將字串部分內容替換成星號或其他符號
	* @param string $string 原始字串
	* @param string $symbol 替換的符號
	* @param int $begin_num 顯示開頭幾個字元
	* @param int $end_num 顯示結尾幾個字元
	* return string
	*/
	function replace_symbol_text($string,$symbol,$begin_num = 0,$end_num = 0)
	{
		$string_length = strlen($string);
		$begin_num = (int)$begin_num;
		$end_num = (int)$end_num;
		$string_middle = '';

		$check_reduce_num = $begin_num + $end_num;

		if($check_reduce_num >= $string_length)
		{
			for ($i=0; $i < $string_length; $i++) 
			{
				$string_middle .= $symbol;
			}
			return $string_middle;
		}

		$symbol_num = $string_length - ($begin_num + $end_num);
		$string_begin = substr($string, 0,$begin_num);
		$string_end = substr($string, $string_length-$end_num);

		for ($i=0; $i < $symbol_num; $i++) 
		{
			$string_middle .= $symbol;
		}

		return $string_begin.$string_middle.$string_end;
	}
?>
