<?php session_start(); ?>


<!-- 上方語法為啟用session，此語法要放在網頁最前方-->
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php
	
	//連接資料庫
	//只要此頁面上有用到連接MySQL就要include它
	// include("config\mysql_connect.inc.php");
	if(!empty($_POST['Token']))		$Verify_Token 	= $_POST['Token'];
	if(!empty($_POST['ID']))		$ID 			= $_POST['ID'];
	if(!empty($_POST['Name']))		$Name 			= $_POST['Name'];
	if(!empty($_POST['Date']))		$Date 			= $_POST['Date'];	
	if(!empty($_POST['Type']))		$Type 			= $_POST['Type'];
	if(!empty($_POST['QueryType']))	$QueryType		= $_POST['QueryType'];
	
	// echo $Verify_Token . $ID . $Date . $Type;
	// echo $_SERVER[' HTTP_REFERER '];
	// die();
	
	if( ($Verify_Token ==="00127691" || $Verify_Token ==="00127691" ) && $Type == "Login")
	{
		$Time = time();
		$Token = base64_encode(md5(base64_encode($Verify_Token) . "HR System" . $Time ));
		
		//寫入Token
		$_SESSION['Token'] 	= $Token;		//Token改為Session
		$_SESSION['ID'] 	= $ID;
		$_SESSION['Name'] 	= $Name;
		$_SESSION['Time'] 	= $Time;
		// setcookie("ID"			,$ID	,time()+3600);
		// setcookie("Name"		,$Name	,time()+3600);
		// setcookie("Time"		,$Time	,time()+3600);
		
		//跳轉測試第一頁
		echo '<meta http-equiv=REFRESH CONTENT=0;url=personality_test_01.php>';
		// echo print_r(session_get_SESSION_params());
	}
	else if( ($Verify_Token ==="00127691" || $Verify_Token ==="00127691") && $Type == "Query")
	{
		$Time = time();
		$Token = base64_encode(md5(base64_encode($Verify_Token) . "HR System" . $Time ));
		
		//寫入Token
		$_SESSION['Token'] 		= $Token;		//Token改為Session
		$_SESSION['ID'] 		= $ID;
		$_SESSION['Date'] 		= $Date;
		$_SESSION['Time'] 		= $Time;
		$_SESSION['QueryType'] 	= $QueryType;

		// setcookie("Token"		,$Token 	,time()+3600);
		// setcookie("ID"			,$ID		,time()+3600);
		// setcookie("Date"		,$Date		,time()+3600);
		// setcookie("Time"		,$Time		,time()+3600);
		// setcookie("QueryType"	,$QueryType	,time()+3600);
		
		//跳轉查詢頁
		echo '<meta http-equiv=REFRESH CONTENT=0;url=result.php>';
		// echo print_r(session_get_SESSION_params());
	}
	else
		echo "<script>history.back(-1)</script>";
?>
