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
	
	if(isset($_SESSION['Date']))
		setcookie("Date"		,"",time()-3600);
	
	if(isset($_SESSION['Time']))
		setcookie("Time"		,"",time()-3600);
?>

<link rel="stylesheet" href="css/jquery-ui.min.css">
<script src="jquery/1.10.2/jquery.min.js"></script>
<script src="jqueryui/1.10.4/jquery-ui.min.js"></script>
<link rel="stylesheet" href="css/style.css">

<script>
	$(function() 
	{
		var currentDate = new Date();
		$( "#datepicker" ).datepicker({dateFormat: "yy-mm-dd"});
		$( "#datepicker" ).datepicker({dateFormat: "yy-mm-dd"});
		$( "#datepicker" ).datepicker("setDate",currentDate);
	});
	
	function CheckInput() 
	{
		var error_v=0;
		
		if(document.form1.QUERYNAME.checked)
		{
			if ( ! checkLength( document.form1.ID.value, 2 ) )
			{
				window.alert( "姓名資料錯誤!" );
				error_v =1;
			}
		}
		
		if(document.form1.QUERYID.checked)
		{
			if ( ! checkID( document.form1.ID.value.toUpperCase() ) )
			{
				window.alert( "身份證字號錯誤!" );
				error_v =1;
			}
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
	
	function getValue()
	{
		// method 1 
		var radio = document.getElementsByName("QueryType");
		for (i=0; i<radio.length; i++)
		{
			if (radio[i].checked) 
			{
				if(radio[i].value == "ID")
					$("#QueryText").replaceWith("<label id='QueryText'>"+"身份證字號："+"</label>");
				else
					$("#QueryText").replaceWith("<label id='QueryText'>"+"姓名："+"</label>");
				// alert(radio[i].value)
			}
		}
	}
	
	function ChangeFontColor()
	{
		var OriginalFont_1=document.getElementById("StringFont_1").innerHTML;
		var OriginalFont_2=document.getElementById("StringFont_2").innerText;
		document.getElementById("StringFont_1").innerHTML='<font color="blue">'+OriginalFont_1+'</font>';
		document.getElementById("StringFont_2").innerHTML=OriginalFont_2;
	}
</script>

<html>
<head>
	<link rel="stylesheet" href="css/style.css">
	<title>人格分析問卷</title>
</head>

<body>

	<!-- 設定網頁編碼為UTF-8 -->
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<!-- 設定手機的視覺大小 -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	
	<table width="100%" height="65%">
		<tr>
			<td align="center" valign="center">
				<form id="form1" name="form1" method="post" action="connect.php">
					<table width="350" style="line-height:30px;" border='0'>
							<tr>
								<td colspan="3" align="center" valign="top">
									<span style="font-family:DFKai-sb;margin-right:5px;font-size:1cm;">人格分析問卷-查詢</span>
								</td>
							</tr>
							<tr>
								<td colspan="3" align="center" valign="top">
									<!--<div style="margin: 30px 8px 20px 6px;border-top:1px dotted #C0C0C0;"></div>-->
									<div id="er-theme" style="border:0px green solid;height:10px;"><p class="line"> </p></div>
								</td>
							</tr>
							<tr>
								<td colspan="3" align="center" valign="top">
									<div style="border:0px green solid;height:45px;"></div>
								</td>
							</tr>
							<tr>
								<td align="right" valign="center">　　查詢類型：</td>
								<td align="center" valign="center"><input type="radio" name="QueryType" id ="QUERYID" value="ID" onClick="getValue()" checked="true" />身份證字號　<input type="radio" name="QueryType" id="QUERYNAME" value="NAME" onClick="getValue()" />姓名</td>
								<td align="left" valign="center"></td>
							</tr>
							<tr>
								<td align="right" valign="center">日期：</td>
								<td align="center" valign="center"><input type="text" name="Date" id="datepicker" autocomplete="off"/></td>
								<td align="left" valign="center"><input type="hidden" name="Type" value="Query"/></td>
							</tr>
							<tr>
								<td align="right" valign="center"><label id="QueryText">身份證字號：</label></td>
								<td align="center" valign="center"><input style="text-transform: uppercase" type="text" name="ID" autocomplete="off" /></td>
								<td align="left" valign="center"></td>
							</tr>
							<tr>
								<td align="right" valign="center">登入碼：</td>
								<td align="center" valign="center"><input type="password" id="TokenText" name="Token" /></td>
								<td align="left" valign="center"><input type="button" id="Submit1" name="Submit1" value="確認" onClick="CheckInput()"/></td>
							</tr>
							<tr>
								<td colspan="3" align="center" valign="bottom">
									<hr size="1px" align="center" width="100%">
								</td>
							</tr>
							<tr>
								<td colspan="3" align="center" valign="top">
									<font color="#805300" size="1" ><b>Ver.1909a18.009.6</b></font>
								</td>
							</tr>
					</table>
				</form>
			</td>
		</tr>
	</table>
	
	<script>
		var input = document.getElementById("TokenText");
		input.addEventListener("keyup", function(event)
		{
			if (event.keyCode === 13) 
			{
				event.preventDefault();
				document.getElementById("Submit1").click();
		  }
		});
	</script>
</body>

</html>


