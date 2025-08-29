<?php
	session_start();
	
	//強制導頁
	//header("Location: http://10.37.1.210");
	
	//清除Session
	session_destroy();
	if(isset($_SESSION['Token']))
		setcookie("Token"		,"",time()-3600);
	
	if(isset($_SESSION['ID']))
		setcookie("ID"			,"",time()-3600);
	
	if(isset($_SESSION['Name']))
		setcookie("Name"		,"",time()-3600);
	
	if(isset($_SESSION['Time']))
		setcookie("Time"		,"",time()-3600);
?>

<script LANGUAGE="JavaScript">
	function CheckInput() 
	{
		var error_v=0;
		
		if ( ! checkLength( document.form1.Name.value, 2 ) )
		{
			window.alert( "姓名資料錯誤!" );
			error_v =1;
		}
		
		document.form1.ID.value = document.form1.ID.value.toUpperCase();
		if ( ! checkID( document.form1.ID.value) )
		{
			window.alert( "身份證字號錯誤!" );
			error_v =1;
		}
		
		if(error_v == 0)
			form1.submit();
	}

	function checkLength( dat, len ) 
	{
	   return (dat.length >= len);
	}

	//檢查身份證字號
	function checkID( id ) 
	{
		//禁用A123456789
		// if(id == "A123456789") return false;
		
		tab = "ABCDEFGHJKLMNPQRSTUVXYWZIO"                     
		A1 = new Array (1,1,1,1,1,1,1,1,1,1,2,2,2,2,2,2,2,2,2,2,3,3,3,3,3,3 );
		A2 = new Array (0,1,2,3,4,5,6,7,8,9,0,1,2,3,4,5,6,7,8,9,0,1,2,3,4,5 );
		Mx = new Array (9,8,7,6,5,4,3,2,1,1);

		if ( id.length != 10 ) return false;
		i = tab.indexOf( id.charAt(0) );
		if ( i == -1 ) return false;
		sum = A1[i] + A2[i]*9;

		for ( i=1; i<10; i++ ) 
		{
			v = parseInt( id.charAt(i) );
			if ( isNaN(v) ) return false;
			sum = sum + v * Mx[i];
		}
		if ( sum % 10 != 0 ) return false;
		return true;
	}
	
</script>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>人格分析問卷</title>
	<link rel="stylesheet" href="assets/main.css">
	<style>
		body {
			margin: 0;
			padding: 0;
			font-family: 'Microsoft JhengHei', '微軟正黑體', Arial, sans-serif;
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			min-height: 100vh;
			display: flex;
			align-items: center;
			justify-content: center;
		}
		.login-container {
			background: rgba(255, 255, 255, 0.95);
			border-radius: 20px;
			padding: 40px;
			box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
			backdrop-filter: blur(10px);
			max-width: 400px;
			width: 100%;
			text-align: center;
		}
		.login-title {
			font-size: 2rem;
			color: #333;
			margin-bottom: 10px;
			font-weight: bold;
		}
		.login-subtitle {
			color: #666;
			margin-bottom: 30px;
			font-size: 0.9rem;
		}
		.form-group {
			margin-bottom: 20px;
			text-align: left;
		}
		.form-label {
			display: block;
			margin-bottom: 5px;
			color: #333;
			font-weight: 500;
		}
		.form-input {
			width: 100%;
			padding: 12px 16px;
			border: 2px solid #e1e5e9;
			border-radius: 10px;
			font-size: 16px;
			transition: all 0.3s ease;
			box-sizing: border-box;
		}
		.form-input:focus {
			outline: none;
			border-color: #667eea;
			box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
		}
		.login-btn {
			width: 100%;
			padding: 14px;
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			color: white;
			border: none;
			border-radius: 10px;
			font-size: 16px;
			font-weight: 600;
			cursor: pointer;
			transition: transform 0.2s ease, box-shadow 0.2s ease;
			margin-top: 10px;
		}
		.login-btn:hover {
			transform: translateY(-2px);
			box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
		}
		.login-btn:active {
			transform: translateY(0);
		}
		.version-info {
			margin-top: 30px;
			padding-top: 20px;
			border-top: 1px solid #e1e5e9;
			color: #999;
			font-size: 0.8rem;
		}
		.error-message {
			color: #e74c3c;
			background: #fdf2f2;
			padding: 10px;
			border-radius: 8px;
			margin-bottom: 20px;
			font-size: 14px;
			border: 1px solid #f5c6cb;
		}
	</style>
</head>

<body class="login-body">

	<div class="login-container">
		<div class="login-title">人格分析問卷</div>
		<div class="login-subtitle">探索您的內在特質，了解真實的自己</div>
		
		<form id="loginForm" method="post" action="connect.php">
			<div class="form-group">
				<label class="form-label" for="name">姓名</label>
				<input type="text" id="name" name="Name" class="form-input" placeholder="請輸入您的姓名" autocomplete="off" required>
			</div>
			
			<div class="form-group">
				<label class="form-label" for="id">身份證號</label>
				<input type="text" id="id" name="ID" class="form-input" placeholder="請輸入身份證號" autocomplete="off" required>
			</div>
			
			<div class="form-group">
				<label class="form-label" for="token">登入碼</label>
				<input type="password" id="token" name="Token" class="form-input" placeholder="請輸入登入碼" required>
			</div>
			
			<input type="hidden" name="Type" value="Login">
			<button type="button" class="login-btn" onclick="validateAndSubmit()">開始測驗</button>
		</form>
		
		<div class="version-info">Ver.1909a18.009.6</div>
	</div>
	
	<script src="assets/main.js"></script>
</body>

</html>


