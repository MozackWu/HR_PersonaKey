<?php
	//資料庫設定
	//資料庫位置
	$db_server = "localhost";
	//資料庫名稱
	$db_name = "hr_database";
	//資料庫管理者帳號
	$db_user = "admin";
	//資料庫管理者密碼
	$db_passwd = "1234@5678@90";

	//對資料庫連線
	$link = @mysqli_connect($db_server, $db_user, $db_passwd, $db_name);

	/* check connection */
	if (mysqli_connect_errno()) 
	{
		printf("Connect failed: %s\n", mysqli_connect_error());
		exit();
	}

	//資料庫連線採UTF8
	if (!mysqli_set_charset($link, "utf8")) 
	{
		printf("Error loading character set utf8: %s\n", mysqli_error($link));
		exit();
	}
	
?>