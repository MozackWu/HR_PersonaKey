<?php
	session_start();

	// 保存使用者的回答
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		foreach ($_POST as $question_key => $answer) {
			$question_id = str_replace('radiobutton1_', '', $question_key);
			$_SESSION['answers'][$question_id] = $answer;
		}
	}

	error_reporting(E_ALL);
	ini_set('display_errors','On');
?>


<link rel="stylesheet" type="text/css" href="chart/css/style.css">
<script src="chart/js/Chart.min.js"></script>
<script src="chart/js/utils.js"></script>

<script LANGUAGE="JavaScript">
	function CheckInput() 
	{
		form1.submit();
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
	date_default_timezone_set('Asia/Taipei');
	include("config/function_class.inc.php");
	require("config/mysql_connect.inc.php");
	
	// echo Verify_LoginToken($_SESSION['Verify_Token']);
	if(!empty($_SESSION['Token']) && !empty($_SESSION['Time']))
	{
		if(Verify_LoginToken($_SESSION['Token'],$_SESSION['Time']))
		{
			$SecurityCode = GetSecurCode($_SESSION['Token'],$_SESSION['Time']);
			// echo '<script>alert("'.$SecurityCode.'")</script>'; 
			
			// 初始化結果計數
			$results = [
				'E' => 0,
				'I' => 0,
				'S' => 0,
				'N' => 0,
				'F' => 0,
				'T' => 0,
				'J' => 0,
				'P' => 0
			];
			

			//結果字串
			$AnswerStr="";
			
			
			foreach ($_SESSION['answers'] as $question_id  => $answer) {
				// $question_id = str_replace('radiobutton1_', '', $question_key);
				// $sql = 'SELECT * FROM `analysis_question` WHERE `UID` = ' . $question_id . ';';
				$sql = ("SELECT * FROM `analysis_question` WHERE `UID` = '" . mysqli_real_escape_string($link,$question_id) . "' LIMIT 1;");
				// echo $sql . "<br>";
				$result = mysqli_query($link,$sql);
				// $result = mysqli_query($link,$query);

				
				if ($result->num_rows > 0) {
					$question = $result->fetch_assoc();
					if ($answer == 'A') {
						$results[$question['RESULT_A']]++;
						$AnswerStr=$AnswerStr . $question_id . ":" . $question['RESULT_A'] . ",";
						// echo '$question["RESULT_A"]：' . $question['RESULT_A'] . "<br>";
						// echo '$results[$question["RESULT_A"]]：' . $results[$question['RESULT_A']] . "<br>";
					} else {
						$results[$question['RESULT_B']]++;
						$AnswerStr=$AnswerStr . $question_id . ":" . $question['RESULT_B'] . ",";
						// echo '$question["RESULT_B"]：' . $question['RESULT_B'] . "<br>";
						// echo '$results[$question["RESULT_B"]]：' . $results[$question['RESULT_B']] . "<br>";
					}
				}
			}
			//刪除最後一個字元
			$AnswerStr = substr($AnswerStr,0,-1);
			
			//各數值
			$AnswerE=0;$AnswerI=0;$AnswerS=0;$AnswerN=0;$AnswerT=0;$AnswerF=0;$AnswerJ=0;$AnswerP=0;
			//最後結果
			$Answer1="";$Answer2="";$Answer3="";$Answer4="";
			
			$AnswerE=$results['E'];
			// echo "$AnswerE：" . $AnswerE;
			$AnswerI=$results['I'];
			// echo "$AnswerI：" . $AnswerI;
			$AnswerS=$results['S'];
			// echo "$AnswerS：" . $AnswerS;
			$AnswerN=$results['N'];
			// echo "$AnswerN：" . $AnswerN;
			$AnswerT=$results['T'];
			// echo "$AnswerT：" . $AnswerT;
			$AnswerF=$results['F'];
			// echo "$AnswerF：" . $AnswerF;
			$AnswerJ=$results['J'];
			// echo "$AnswerJ：" . $AnswerJ;
			$AnswerP=$results['P'];
			// echo "$AnswerP：" . $AnswerP;
			
			//刪除最後一個字元
			// $AnswerStr = substr($AnswerStr,0,-1);
			
			//產生備註
			$Note = "";
		
			//第一象限
			if($AnswerE > $AnswerI)
			{
				$Answer1='<font color="#E74C3C">E</font>';
				$Note = $Note . "E";
			}
			else if($AnswerE == $AnswerI)
			{
				$Answer1='<font color="#E74C3C">E</font><br><font color="#9B59B6">I</font>';
				$Note = $Note . "(E,I)";
			}
			else
			{
				$Answer1='<font color="#9B59B6">I</font>';
				$Note = $Note . "I";
			}
			
			//第二象限
			if($AnswerS > $AnswerN)
			{
				$Answer2='<font color="#3498DB">S</font>';
				$Note = $Note . "-S";
			}
			else if($AnswerS == $AnswerN)
			{
				$Answer2='<font color="#3498DB">S</font><br><font color="#48C9B0">N</font>';
				$Note = $Note . "-(S,N)";
			}
			else
			{
				$Note = $Note . "-N";
				$Answer2='<font color="#48C9B0">N</font>';
			}
			
			//第三象限
			if($AnswerT > $AnswerF)
			{
				$Answer3='<font color="#D4AC0D">T</font>';
				$Note = $Note . "-T";
			}
			else if($AnswerT == $AnswerF)
			{
				$Answer3='<font color="#D4AC0D">T</font><br><font color="#DC7633">F</font>';
				$Note = $Note . "-(T,F)";
			}
			else
			{
				$Answer3='<font color="#DC7633">F</font>';
				$Note = $Note . "-F";
			}
			
			//第四象限
			if($AnswerJ > $AnswerP)
			{
				$Answer4='<font color="#566573">J</font>';
				$Note = $Note . "-J";
			}
			else if($AnswerJ == $AnswerP)
			{
				$Answer4='<font color="#566573">J</font><br><font color="#154360">P</font>';
				$Note = $Note . "-(J,P)";
			}
			else
			{
				$Answer4='<font color="#154360">P</font>';
				$Note = $Note . "-P";
			}
			
			//儲存身份帳字號
			$ID = "";
			if(isset($_SESSION['ID'])) $ID = $_SESSION['ID'];
			
			//儲存姓名
			$Name = "";
			if(isset($_SESSION['Name'])) $Name = $_SESSION['Name'];
			
			//寫入資料庫
			$query = "INSERT INTO `personalit_analysis` (`UID`,`COMP`, `ID`, `NAME`, `E`, `I`, `S`, `N`, `T`, `F`, `J`, `P`, `RESULT`, `NOTE`) VALUES (NULL, '".mysqli_real_escape_string($link,$SecurityCode)."', '".mysqli_real_escape_string($link,$ID)."', '".mysqli_real_escape_string($link,$Name)."', '".mysqli_real_escape_string($link,$AnswerE)."', '".mysqli_real_escape_string($link,$AnswerI)."', '".mysqli_real_escape_string($link,$AnswerS)."', '".mysqli_real_escape_string($link,$AnswerN)."', '".mysqli_real_escape_string($link,$AnswerT)."', '".mysqli_real_escape_string($link,$AnswerF)."', '".mysqli_real_escape_string($link,$AnswerJ)."', '".mysqli_real_escape_string($link,$AnswerP)."', '".mysqli_real_escape_string($link,$AnswerStr)."', '".mysqli_real_escape_string($link,$Note)."');";

			if (mysqli_query($link, $query) === false) {
				printf("Error: %s\n", mysqli_sqlstate($link));
			}
			/* close connection */
			mysqli_close($link);		
			
			if(isset($_SESSION['Token']))
				setcookie("Token"		,"",time()-3600);
			
			if(isset($_SESSION['ID']))
				setcookie("ID"			,"",time()-3600);
			
			if(isset($_SESSION['Name']))
				setcookie("Name"		,"",time()-3600);
			
			if(isset($_SESSION['Time']))
				setcookie("Time"		,"",time()-3600);
			
			// 清除Session
			session_destroy();
			?>
			<!-- HTML -->
			<body>
				<table width="100%" height="auto">
					<tr>
						<td align="center" valign="top">
							<font color="#154360"><big><b>總結</b></big></font>
							<p><hr size="1px" align="center" width="80%">
							<table width="400" style="line-height:40px;" border='0'>
								<?php
								//顯示姓名和身份證號
								echo '<tr>
										<td align="center" valign="center"><big><font color="#1A5276">'.$Name.'</font></big></td>
										<td align="center" valign="center"><big><font color="#1A5276">'.replace_symbol_text($ID,'＊',3,2).'</font></big></td>
									</tr>';						
								?>
							</table>
							<table width="400" style="line-height:15px;" border='0'>
								<?php
								$strE="";$strI="";$strS="";$strN="";$strT="";$strF="";$strJ="";$strP="";
								
								// cal AnserE
								for($i=1;$i<=$AnswerE;$i++) 
									$strE=$strE . '▃<br>';
								
								// cal AnserI
								for($i=1;$i<=$AnswerI;$i++) 
									$strI=$strI . '▃<br>';
								
								// cal AnserS
								for($i=1;$i<=$AnswerS;$i++) 
									$strS=$strS . '▃<br>';
								
								// cal AnserN
								for($i=1;$i<=$AnswerN;$i++) 
									$strN=$strN . '▃<br>';
								
								// cal AnserT
								for($i=1;$i<=$AnswerT;$i++) 
									$strT=$strT . '▃<br>';
								
								// cal AnserF
								for($i=1;$i<=$AnswerF;$i++) 
									$strF=$strF . '▃<br>';
								
								// cal AnserJ
								for($i=1;$i<=$AnswerJ;$i++) 
									$strJ=$strJ . '▃<br>';
								
								// cal AnserP
								for($i=1;$i<=$AnswerP;$i++) 
									$strP=$strP . '▃<br>';
								
								//顯示圖表
								{
									echo '<br>';
									//顯示量圖
									echo '<tr><td colspan="8" align="center"><div class="chart-wapper" style="width:450px;height:400px;"><canvas id="chart-0" width="400" height="350"></canvas></div></td></tr>';
									// echo '<tr>
										// <td align="center" valign="top"><font color="#E74C3C">
											// '.$AnswerE.'
										// </font></td>
										// <td align="center" valign="top"><font color="#9B59B6">
											// '.$AnswerI.'
										// </font></td>
										// <td align="center" valign="top"><font color="#3498DB">
											// '.$AnswerS.'
										// </font></td>
										// <td align="center" valign="top"><font color="#48C9B0">
											// '.$AnswerN.'
										// </font></td>
										// <td align="center" valign="top"><font color="#D4AC0D">
											// '.$AnswerT.'
										// </font></td>
										// <td align="center" valign="top"><font color="#DC7633">
											// '.$AnswerF.'
										// </font></td>
										// <td align="center" valign="top"><font color="#566573">
											// '.$AnswerJ.'
										// </font></td>
										// <td align="center" valign="top"><font color="#154360">
											// '.$AnswerP.'
										// </font></td>
									// </tr>';
									// echo '<tr><td colspan="8" align="center"><hr size="1px" align="center" width="100%"></td></tr>';
									// 顯示分量表
									// echo 
										// '<tr>
											// <td align="center" valign="top"><font color="#E74C3C">
												// '. $strE .'
											// </font></td>
											// <td align="center" valign="top"><font color="#9B59B6">
												// '. $strI .'
											// </font></td>
											// <td align="center" valign="top"><font color="#3498DB">
												// '. $strS .'
											// </font></td>
											// <td align="center" valign="top"><font color="#48C9B0">
												// '. $strN .'
											// </font></td>
											// <td align="center" valign="top"><font color="#D4AC0D">
												// '. $strT .'
											// </font></td>
											// <td align="center" valign="top"><font color="#DC7633">
												// '. $strF .'
											// </font></td>
											// <td align="center" valign="top"><font color="#566573">
												// '. $strJ .'
											// </font></td>
											// <td align="center" valign="top"><font color="#154360">
												// '. $strP .'
											// </font></td>
										// </tr>';
									echo '<tr><td colspan="8" align="center"><hr size="1px" align="center" width="100%"></td></tr>';
								}
								?>
								</table>
								<table width="400" style="line-height:40px;" border='0'>
								<?php
									//顯示人格特碼
									// echo '<tr>
											// <td colspan="2" align="center" valign="center"><big><big><b>'.$Answer1.'</b></big></big></td>
											// <td colspan="2" align="center" valign="center"><big><big><b>'.$Answer2.'</b></big></big></td>
											// <td colspan="2" align="center" valign="center"><big><big><b>'.$Answer3.'</b></big></big></td>
											// <td colspan="2" align="center" valign="center"><big><big><b>'.$Answer4.'</b></big></big></td>
										// </tr>';
									
								?>
								</table>
								<form id="form1" name="form1" method="post" action="connect.php">
									<table width="400" style="line-height:40px;" border='0'>
										<tr>
											<td colspan="3" align="center" valign="top">
												<div style="border:0px green solid;height:40px;"></div>
											</td>
										</tr>
										<tr>
											<td align="right" valign="center">列印碼：</td>
											<td align="center" valign="center"><input type="password" name="Token" /><input type="hidden" name="Type" value="Query"/><input type="hidden" name="ID" value="<?php echo $ID; ?>"/><input type="hidden" name="Date" value="<?php echo date("Y-m-d"); ?>"/><input type="hidden" name="QueryType" value="ID"/></td>
											<td align="left" valign="center"><input type="button" name="Submit1" value="確認" onClick="CheckInput()"/></td>
										</tr>
									</table>
								</form>
							<p>
						</td>
					</tr>
				</table>
				
				<script>
					Chart.defaults.global.defaultFontSize = 12;
					// var DATA_COUNT = 7;

					var utils = Samples.utils;

					// utils.srand(110);

					function alternatePointStyles(ctx) {
						var index = ctx.dataIndex;
						return index % 2 === 0 ? "circle" : "rect";
					}

					function makeHalfAsOpaque(ctx) {
						var c = ctx.dataset.backgroundColor;
						return utils.transparentize(c);
					}

					function adjustRadiusBasedOnData(ctx) {
						var v = ctx.dataset.data[ctx.dataIndex];
						return v < 5 ? 4
							: v < 10 ? 5
							: v < 15 ? 6
							: v < 20 ? 7
							: 8;
					}
					<?php
					echo '
					var data = {
						labels: ["E（'.(int)$AnswerE.'）","I（'.(int)$AnswerI.'）","S（'.(int)$AnswerS.'）","N（'.(int)$AnswerN.'）","T（'.(int)$AnswerT.'）","F（'.(int)$AnswerF.'）","J（'.(int)$AnswerJ.'）","P（'.(int)$AnswerP.'）"],
						datasets: [{
							data: ['.(int)$AnswerE.','.(int)$AnswerI.','.(int)$AnswerS.','.(int)$AnswerN.','.(int)$AnswerT.','.(int)$AnswerF.','.(int)$AnswerJ.','.(int)$AnswerP.'],
							backgroundColor: Chart.helpers.color("#4dc9f6").alpha(0.2).rgbString(),
							borderColor: "#4dc9f6",
						}]
					};';
					
					$stepSize=0;
					$max=0;
					
					if($AnswerE>$max) $max=$AnswerE;
					if($AnswerI>$max) $max=$AnswerI;
					if($AnswerS>$max) $max=$AnswerS;
					if($AnswerN>$max) $max=$AnswerN;
					if($AnswerT>$max) $max=$AnswerT;
					if($AnswerF>$max) $max=$AnswerF;
					if($AnswerJ>$max) $max=$AnswerJ;
					if($AnswerP>$max) $max=$AnswerP;
					
					$stepSize = ceil($max  / 4);
					$max = $stepSize * 4;
					?>

					var options = {
						legend: false,
						// tooltips: true,
						elements: {
							point: {
								hoverBackgroundColor: makeHalfAsOpaque,
								radius: adjustRadiusBasedOnData,
								pointStyle: alternatePointStyles,
								hoverRadius: 10,
							}
						},
						scale: {
							display: true,
							reverse: true,
							pointLabels: {
								fontSize: 13,
								fontStyle: "bold",
								fontColor: [Chart.helpers.color("#800000").alpha(1).rgbString(),
											Chart.helpers.color("#800000").alpha(1).rgbString(),
											Chart.helpers.color("#FF4500").alpha(1).rgbString(),
											Chart.helpers.color("#FF4500").alpha(1).rgbString(),
											Chart.helpers.color("#228B22").alpha(1).rgbString(),
											Chart.helpers.color("#228B22").alpha(1).rgbString(),
											Chart.helpers.color("#000000").alpha(1).rgbString(),
											Chart.helpers.color("#000000").alpha(1).rgbString()], 
							},
							ticks: {
								stepSize: <?php echo $stepSize; ?>,
								min:0,
								max:<?php echo $max; ?>,
								fontSize: 12,
								fontStyle: "bold",
								fontColor: Chart.helpers.color("#B54FFF").alpha(1).rgbString(),
							}
						}
					};

					var chart = new Chart("chart-0", {
						type: "radar",
						data: data,
						options: options
					}
				);
				</script>
			</body>
			
			
			<?php
		}
		else //無效驗證
			echo '<meta http-equiv=REFRESH CONTENT=0;url=index.php>';
	}
	else
		echo '<meta http-equiv=REFRESH CONTENT=0;url=index.php>';
?>

</body>

</html>
