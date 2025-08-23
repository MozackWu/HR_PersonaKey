<?php

	$Key = array("林偲瑜","李哲豪","黃若萍","林羣曜","鍾孟修","談嘉豪","許博舜","闕永翔","曾鈺鈞","江宇皓","林沁葦","林思妍","江銘得","陳彥融","王璿庭");
	
	if(isset($_REQUEST["token"]))
	{
		// echo $_REQUEST["token"] . "<br>";
		
		$result = 0;
		for($i=0;$i<count($Key);$i++)
		{
			if( $_REQUEST["token"] === substr(md5("NOC:". $Key[$i]), -6))
			{
				echo $Key[$i] . "：" . md5("NOC:". $Key[$i]) . "：" . base64_encode("NOC:". $Key[$i]);
				$result = 1;
			}
		}
		
		if(!$result)
			echo "error" ;
	}
	
	if(isset($_REQUEST["base"]))
	{
		for($i=0;$i<count($Key);$i++)
			echo $Key[$i] . "：" . md5("NOC:". $Key[$i]) . "<br>";
		/*
		echo "林偲瑜" . "：" . md5("NOC:林偲瑜") . "<br>";
		echo "李哲豪" . "：" . md5("NOC:李哲豪") . "<br>";
		echo "黃若萍" . "：" . md5("NOC:黃若萍") . "<br>";
		echo "林羣曜" . "：" . md5("NOC:林羣曜") . "<br>";
		echo "鍾孟修" . "：" . md5("NOC:鍾孟修") . "<br>";
		echo "談嘉豪" . "：" . md5("NOC:談嘉豪") . "<br>";
		echo "許博舜" . "：" . md5("NOC:許博舜") . "<br>";
		echo "闕永翔" . "：" . md5("NOC:闕永翔") . "<br>";
		echo "曾鈺鈞" . "：" . md5("NOC:曾鈺鈞") . "<br>";
		echo "江宇皓" . "：" . md5("NOC:江宇皓") . "<br>";
		echo "林沁葦" . "：" . md5("NOC:林沁葦") . "<br>";
		echo "林思妍" . "：" . md5("NOC:林思妍") . "<br>";
		echo "江銘得" . "：" . md5("NOC:江銘得") . "<br>";
		echo "陳彥融" . "：" . md5("NOC:陳彥融") . "<br>";
		echo "王璿庭" . "：" . md5("NOC:王璿庭") . "<br>";
		*/
	}

?>