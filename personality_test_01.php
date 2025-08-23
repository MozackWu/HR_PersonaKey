<?php 
require_once 'config/function_rand_questions.php';
require_once 'config/mysql_connect.inc.php';

error_reporting(E_ALL);
ini_set('display_errors','On');

$page = 'personality_test_01';
$page_questions = $_SESSION['page_questions'][$page] ?? [];
?>

<script LANGUAGE="JavaScript">
function check_radio(input1)	// input1 為form name變數
{    	
	var radio_items = [];  //宣告空陣列-存放不同radio name
	var temp;
	//找出all HTML元件並符合type=radio,用意取出radio name
	for(var i=0;i<document.forms[input1].elements.length-2;i++)
	{
		var e=document.forms[input1].elements[i];
		if(e.type=='radio')        //判斷型態為radio
		{
			if (temp !=e.name)    //比對上一筆radio name與目前這一筆radio name是否相同 
			{
				radio_items.push(e.name);  //資料radio name存入陣列
				temp=e.name;
			}
		}

	}
	var radio_name;  //radio name  
	var obj, flag;   // flag 判斷是否有被選取
	
	var flag_v=0;
	var unSelect_Num=1;
	// radio_items.length-陣列長度
	for (var i=0;i<radio_items.length;i++) 
	{
		flag = true;
		radio_name=radio_items[i];
		obj=document.forms[input1].elements[radio_name];
		j=radio_name.substr(11);      //取radio_name(radiobutton)後面編號
		var arrayStr=j.split("_");    //抓id ex:1_1, 1_2 ...etc 
		
		//判斷每一題內的選項是否有被選取
		for (var y = 0, x; x = obj[y]; ++y) 
		{
			if (x.checked)   //選取 
			{            
				flag = false;
				unSelect_Num++;
			}
		}
		if (flag)  //未選取 
		{
			// alert("問題 " + arrayStr[1] +" 未選擇");
			alert("本頁問題 " + unSelect_Num +" 未選擇");
			obj[0].focus();   //將焦點停留在最前面RadioButton,But IE8以上無效果
			flag_v=1;
			return false;
		}
	}
	
	if(flag_v == 0 )
		form1.submit();
}

function check_recheck(input1)	// input1 為form name變數
{    	
	var radio_items = [];  //宣告空陣列-存放不同radio name
	var temp;
	//找出all HTML元件並符合type=radio,用意取出radio name
	for(var i=0;i<document.forms[input1].elements.length-2;i++)
	{
		var e=document.forms[input1].elements[i];
		if(e.type=='radio')        //判斷型態為radio
		{
			if (temp !=e.name)    //比對上一筆radio name與目前這一筆radio name是否相同 
			{
				radio_items.push(e.name);  //資料radio name存入陣列
				temp=e.name;
			}
		}

	}
	var radio_name;  //radio name  
	var obj, flag;   // flag 判斷是否有被選取
	
	// radio_items.length-陣列長度
	for (var i=0;i<radio_items.length;i++) 
	{
		flag = true;
		radio_name=radio_items[i];
		obj=document.forms[input1].elements[radio_name];
		j=radio_name.substr(11);      //取radio_name(radiobutton)後面編號
		var arrayStr=j.split("_");    //抓id ex:1_1, 1_2 ...etc 

		//判斷每一題內的選項是否有被選取
		for (var y = 0, x; x = obj[y]; ++y) 
		{
			//如果先前有被選，要將選項勾選
			if(x.value == '<%= Session["radiobutton1_"+arrayStr[1]].ToString() %>')
				x.checked = true;
		}
	}
}
</script>

<html>
<head>
	<title>人格分析問卷</title>
</head>

<body>

	<!-- 設定網頁編碼為UTF-8 -->
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<!-- 設定手機的視覺大小 -->
	<meta name="viewport" content="width=600, initial-scale=1.0">

<?php
	include("config/function_class.inc.php");
	
	// echo Verify_LoginToken($_SESSION['Verify_Token']);
	if(!empty($_SESSION['Token']) && !empty($_SESSION['Time']))
	{
		if(Verify_LoginToken($_SESSION['Token'],$_SESSION['Time']))
		{
			//有效驗證
			?>
			<!--讀入對應的值-->
			<body onload="check_recheck('form1');">
				<table width="100%" height="auto">
					<tr>
						<td align="center" valign="top">
							<form id="form1" name="form1" method="post" action="personality_test_02.php">
									<font color="#154360"><big><b>第一部份</b></big></font><p><font color="#9B59B6">下列答案的描述中，哪一個最貼近您平時思考和行為模式<br>(請著重在詞句的 "意思" 上，而不是它們好聽或好看！)</font><p><hr size="1px" align="center" width="80%">
									<table width="640" style="line-height:40px;" border='0'>
									<!--<table style="border-top:3px #FFD382 solid;border-bottom:3px #82FFFF solid;" cellpadding="10" border='0'>-->
									<!--------------------------------------------------------------------------------------->
									<?php $Number_Set = 1;?>
									<?php foreach ($page_questions as $question): ?>
										<tr><td colspan="2" align="center"><font color="#996600"><b><?= "1-" . $Number_Set . "：" . $question['QUESTION'] ?></b></font></td></tr>
										<tr>
											<td align="center" valign="top">
												<input type="radio" name="radiobutton1_<?= $question['UID'] ?>" value="A" required> <?= $question['ANSWER_A'] ?>
											</td>
											<td align="center" valign="top">
												<input type="radio" name="radiobutton1_<?= $question['UID'] ?>" value="B"> <?= $question['ANSWER_B'] ?>
											</td>
										</tr>
										<tr><td colspan="2" align="center"><hr size="1px" align="center" width="100%"></td></tr>
									<?php $Number_Set++;?>
									<?php endforeach; ?>				
									<!--------------------------------------------------------------------------------------->
									</table>
								<!--人格測試開始
								登入碼：<input type="password" name="Verify_Token" />
								<input type="submit" name="button" value="登入" />	-->	
								<p><hr size="1px" align="center" width="50%">
								<input name="Submit1" type="button" value="下一頁" onClick="check_radio('form1')">
								<p>
								<p>
								<p>
							</form>
						</td>
					</tr>
				</table>
			</body>
		
<?php
			// echo "YES";
		}
		else //無效驗證
			echo '<meta http-equiv=REFRESH CONTENT=0;url=index.php>';
	}
	else
		echo '<meta http-equiv=REFRESH CONTENT=0;url=index.php>';
?>

</body>

</html>





