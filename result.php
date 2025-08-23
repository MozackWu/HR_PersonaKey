<?php
	session_start();
	
	require("config/mysql_connect.inc.php");
	include("config/function_class.inc.php");
	
	// $Errstr = $_SESSION['Date'] . " . " . $_SESSION['ID'] . " . " . $_SESSION['Token'] . " . " . $_SESSION['Time']; 
	// echo Verify_LoginToken($_SESSION['Token'],$_SESSION['Time']);
	if(!empty($_SESSION['Token']) && !empty($_SESSION['Time']))
	{
		if(Verify_LoginToken($_SESSION['Token'],$_SESSION['Time']))
		// if(Verify_LoginToken($_SESSION['Token'],$_SESSION['Time']))
		{

			$DAY=$_SESSION['Date'];
			$ID=$_SESSION['ID'];
			$QUERYTYPE=$_SESSION['QueryType'];
			
			//清除Session
			session_destroy();
			if(isset($_SESSION['Token']))
				setcookie("Token"		,"",time()-3600);
			
			if(isset($_SESSION["ID"]))
				setcookie("ID"			,"",time()-3600);
			
			if(isset($_SESSION["Date"]))
				setcookie("Date"		,"",time()-3600);
			
			if(isset($_SESSION["Time"]))
				setcookie("Time"		,"",time()-3600);			
			
			if(isset($_SESSION["QueryType"]))
				setcookie("QueryType"	,"",time()-3600);
				
			$P0=array();	
			$P1=array();
			$P2=array();
			$P3=array();
			$P4=array();
			$P5=array();
			$P6=array();
			
			$V1=array();
			$V2=array();
			$V3=array();
			$V4=array();
			
			$Value=array();
			
			$Type["ESTJ"]= "督察型";
			$Type["ESTP"]= "挑戰型";
			$Type["ESFJ"]= "主人型";
			$Type["ESFP"]= "表演家型";
			$Type["ENTJ"]= "將領型";
			$Type["ENTP"]= "發明家型";
			$Type["ENFJ"]= "教育家型";
			$Type["ENFP"]= "記者型";
			$Type["ISTJ"]= "會計型";
			$Type["ISTP"]= "工匠型";
			$Type["ISFJ"]= "保護者型";
			$Type["ISFP"]= "藝術家型";
			$Type["INTJ"]= "軍師型";
			$Type["INTP"]= "學者型";
			$Type["INFJ"]= "諮商師型";
			$Type["INFP"]= "哲學家型";
			
			$NAME = "";
			

			
			//讀取資料庫
			if($QUERYTYPE == "ID")
				$query = ("SELECT * FROM `personalit_analysis` WHERE `ID` = '".mysqli_real_escape_string($link,$ID)."' AND `TIME` >= '".mysqli_real_escape_string($link,$DAY)." 00:00:00' AND `TIME` <= '".mysqli_real_escape_string($link,$DAY)." 23:59:59' ORDER BY `TIME` DESC LIMIT 1;");
			else	
				$query = ("SELECT * FROM `personalit_analysis` WHERE `NAME` = '".mysqli_real_escape_string($link,$ID)."' AND `TIME` >= '".mysqli_real_escape_string($link,$DAY)." 00:00:00' AND `TIME` <= '".mysqli_real_escape_string($link,$DAY)." 23:59:59' ORDER BY `TIME` DESC LIMIT 1;");
			// $result = $link->query($query);
			// echo $query;
			$result = mysqli_query($link,$query);
			// printf("Error: %s\n", mysqli_error($result));
			
			// while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
				// print_r( $row);
			// }
			// printf("Error: %s\n", mysqli_error($result));
			
			if (mysqli_num_rows($result) > 0) 
			{
				while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) 
				{
					$Value["E"] = $row["E"];
					$Value["I"] = $row["I"];
					$Value["S"] = $row["S"];
					$Value["N"] = $row["N"];
					$Value["T"] = $row["T"];
					$Value["F"] = $row["F"];
					$Value["J"] = $row["J"];
					$Value["P"] = $row["P"];
					$NAME 		= $row["NAME"];
				}
				
				//判斷E、I
				($Value["E"]>$Value["I"]?array_push($V1, "E"):array_push($V1, "I"));
				
				//判斷S、N
				if($Value["S"]==$Value["N"])
				{
					for($i=0;$i<count($V1);$i++)
						array_push($V2, $V1[$i]."S");
					
					for($i=0;$i<count($V1);$i++)
						array_push($V2, $V1[$i]."N");	
				}
				else if($Value["S"]>$Value["N"])
				{
					for($i=0;$i<count($V1);$i++)
						array_push($V2, $V1[$i]."S");
				}
				else
				{
					for($i=0;$i<count($V1);$i++)
						array_push($V2, $V1[$i]."N");	
				}
				
				//判斷T、F
				if($Value["T"]==$Value["F"])
				{
					for($i=0;$i<count($V2);$i++)
						array_push($V3, $V2[$i]."T");
					
					for($i=0;$i<count($V2);$i++)
						array_push($V3, $V2[$i]."F");	
				}
				else if($Value["T"]>$Value["F"])
				{
					for($i=0;$i<count($V2);$i++)
						array_push($V3, $V2[$i]."T");
				}
				else
				{
					for($i=0;$i<count($V2);$i++)
						array_push($V3, $V2[$i]."F");	
				}
					
				//判斷P、J
				if($Value["P"]==$Value["J"])
				{
					for($i=0;$i<count($V3);$i++)
						array_push($V4, $V3[$i]."P");
					
					for($i=0;$i<count($V3);$i++)
						array_push($V4, $V3[$i]."J");	
				}
				else if($Value["P"]>$Value["J"])
				{
					for($i=0;$i<count($V3);$i++)
						array_push($V4, $V3[$i]."P");
				}
				else
				{
					for($i=0;$i<count($V3);$i++)
						array_push($V4, $V3[$i]."J");	
				}
				
				// for($i=0;$i<count($V4);$i++)
					// echo "	Value:" . $V4[$i] . "\n";
				
				//取代姓名
				if(mb_strlen($NAME, "UTF-8") == 2)
				{
					$NAME = mb_substr($NAME, 0, 1, "UTF-8") . "O";
				}
				else
				{
					$pattern = '/^(\X)(\X+)(\X)/u';
					preg_match($pattern, $NAME, $matches);
					$NAME = $matches[1]. str_repeat("O", mb_strlen($NAME, "UTF-8") - 2) . $matches[3];

				}
				
				//判斷
				// ($E>$I?$V1="E":$V1="I");
				// ($S>$N?$V2="S":$V2="N");
				// ($T>$F?$V3="T":$V3="F");
				// ($J>$P?$V4="J":$V4="P");
				for($i=0;$i<count($V4);$i++)
				{
					$query = "SELECT * FROM `analysis_result` WHERE `KEY_NAME` = '".mysqli_real_escape_string($link,$V4[$i])."';";
					$result1 = mysqli_query($link,$query);
					
					if (mysqli_num_rows($result1) > 0)
					{
						while($row = mysqli_fetch_array($result1, MYSQLI_ASSOC))
						{
							// array_push($Type, $row["KEY_NAME"]);
							array_push($P0, $row["P0"]);
							array_push($P1, $row["P1"]);
							array_push($P2, $row["P2"]);
							array_push($P3, $row["P3"]);
							array_push($P4, $row["P4"]);
							array_push($P5, $row["P5"]);
							array_push($P6, $row["P6"]);
						}
					}
				}
				
				echo '
				<!DOCTYPE html>
				<html>
					<head>
					<meta charset="utf-8">
					<meta name="viewport" content="width=device-width, initial-scale=1.0">
					<meta http-equiv="X-UA-Compatible" content="IE=edge,Chrome=1">
					<title>人格分析問卷</title>
					<!--bootstrap v3.3.6-->
					<link rel="stylesheet" href="css/vendor/bootstrap.min.css">
					<link rel="stylesheet" href="css/all.css">
					<link rel="stylesheet" media="print" href="css/print.css">
					<!--load customize webfont-->
					<link href="" rel="stylesheet"><!--[if IE]>
					<link rel="stylesheet" type="text/css" href="css/ie.css">
					<script src="./js/vendor/html5shiv.min.js"></script>
					<script src="./js/vendor/respond.min.js"></script><![endif]-->
					</head>
					<body>
					<div id="mind-test">
						<!-- Banner ==================================================================================-->
						<div class="banner">
						<div class="container">
							<!-- 性格類型標題 ==================================================================================-->
							<div class="heading tac">
							<div class="title-md fw-b">您的性格類型是:</div>
							<!-- $性格類型-->
							<ul class="tab-menu">
							';
							
							//顯示選項
							for($i=0;$i<count($V4);$i++)
							{
								if($i==0)
									echo '<li class="active"><a class="fw-b" href="#test_result_01" data-toggle="tab">['.$NAME.']'.$V4[$i].' '.$Type[$V4[$i]].'</a></li>';
								else
									echo '<li><a class="fw-b" href="#test_result_0'.($i+1).'" data-toggle="tab">['.$NAME.']'.$V4[$i].' '.$Type[$V4[$i]].'</a></li>';
								
							} 
								
						echo '
							</ul>
							</div>
							<!--/ =============================================================================================-->
						</div>
						</div>
						<!--/ =============================================================================================-->
						<!-- **切換頁籤後顯示的內容 =============================================================================================-->
						<div class="tab-content">
						';
						
						
						for($i=0;$i<count($V4);$i++)
						{
							$H1=0;	//精力支配高分
							$L1=0;	//精力支配低分
							$H2=0;	//認識世界高份
							$L2=0;	//認識世界低分
							$H3=0;	//判斷事物高分
							$L3=0;	//判斷事物低分
							$H4=0;	//生活態度高分
							$L4=0;	//生活態度低分
							
							$H1=$Value[mb_substr( $V4[$i],0,1)];
							$H2=$Value[mb_substr( $V4[$i],1,1)];
							$H3=$Value[mb_substr( $V4[$i],2,1)];
							$H4=$Value[mb_substr( $V4[$i],3,1)];
							
							(mb_substr( $V4[$i],0,1)=="E"?$L1=$Value["I"]:$L1=$Value["E"]);
							(mb_substr( $V4[$i],1,1)=="S"?$L2=$Value["N"]:$L2=$Value["S"]);
							(mb_substr( $V4[$i],2,1)=="T"?$L3=$Value["F"]:$L3=$Value["T"]);
							(mb_substr( $V4[$i],3,1)=="J"?$L4=$Value["P"]:$L4=$Value["J"]);
							
							// echo $V4[$i] . "<br>";
							
							if($i==0)
							{
								// echo $Value[mb_substr( $V4[$i],0,1)];
								echo '
								<!-- 結果 01 =============================================================================================-->
								<div class="container tab-pane fade in active" id="test_result_0'. ($i+1) .'" role="tabpanel">
									<!-- 得分結果 ======================================================================================-->
									<div class="row" id="mt-score-result">
									<div class="col-sm-3">
										<div class="mt-score-result-item color-blue">
										<h4 class="heading-title">精力支配</h4>
										<div class="data-group"><span class="data-title">'.(mb_substr( $V4[$i],0,1)=="E"?"外向":"內向").'</span><span class="data-content">'.(mb_substr( $V4[$i],0,1)=="E"?"E":"I").'</span>
											<!-- $分數--><span class="data-content score">('.$H1.')</span>
											<!--/ $分數-->
										</div>
										<!-- 較低分的數據，須在<div class="data-group"></div> 中，加上class="data-group-disabled"-->
										<div class="data-group data-group-disabled"><span class="data-title">'.(mb_substr( $V4[$i],0,1)!="E"?"外向":"內向").'</span><span class="data-content">'.(mb_substr( $V4[$i],0,1)!="E"?"E":"I").'</span>
											<!-- $分數--><span class="data-content score">('.$L1.')</span>
											<!--/ $分數-->
										</div>
										</div>
									</div>
									<div class="col-sm-3">
										<div class="mt-score-result-item color-blue-green">
										<h4 class="heading-title">認識世界</h4>
										<div class="data-group"><span class="data-title">'.(mb_substr( $V4[$i],1,1)=="S"?"實感":"直覺").'</span><span class="data-content">'.(mb_substr( $V4[$i],1,1)=="S"?"S":"N").'</span>
											<!-- $分數--><span class="data-content score">('.$H2.')</span>
											<!--/ $分數-->
										</div>
										<div class="data-group data-group-disabled"><span class="data-title">'.(mb_substr( $V4[$i],1,1)!="S"?"實感":"直覺").'</span><span class="data-content">'.(mb_substr( $V4[$i],1,1)!="S"?"S":"N").'</span>
											<!-- $分數--><span class="data-content score">('.$L2.')</span>
											<!--/ $分數-->
										</div>
										</div>
									</div>
									<div class="col-sm-3">
										<div class="mt-score-result-item color-green">
										<h4 class="heading-title">判斷事物</h4>
										<div class="data-group"><span class="data-title">'.(mb_substr( $V4[$i],2,1)=="T"?"思維":"情感").'</span><span class="data-content">'.(mb_substr( $V4[$i],2,1)=="T"?"T":"F").'</span>
											<!-- $分數--><span class="data-content score">('.$H3.')</span>
											<!--/ $分數-->
										</div>
										<div class="data-group data-group-disabled"><span class="data-title">'.(mb_substr( $V4[$i],2,1)!="T"?"思維":"情感").'</span><span class="data-content">'.(mb_substr( $V4[$i],2,1)!="T"?"T":"F").'</span>
											<!-- $分數--><span class="data-content score">('.$L3.')</span>
											<!--/ $分數-->
										</div>
										</div>
									</div>
									<div class="col-sm-3">
										<div class="mt-score-result-item color-yellow">
										<h4 class="heading-title">生活態度</h4>
										<div class="data-group"><span class="data-title">'.(mb_substr( $V4[$i],3,1)=="J"?"判斷":"知覺").'</span><span class="data-content">'.(mb_substr( $V4[$i],3,1)=="J"?"J":"P").'</span>
											<!-- $分數--><span class="data-content score">('.$H4.')</span>
											<!--/ $分數-->
										</div>
										<div class="data-group data-group-disabled"><span class="data-title">'.(mb_substr( $V4[$i],3,1)!="J"?"判斷":"知覺").'</span><span class="data-content">'.(mb_substr( $V4[$i],3,1)!="J"?"J":"P").'</span>
											<!-- $分數--><span class="data-content score">('.$L4.')</span>
											<!--/ $分數-->
										</div>
										</div>
									</div>
									</div>
									<!--/ =============================================================================================-->
									<!-- 說明表格 ======================================================================================-->
									<table class="table" id="mt-intro-table">
									<thead>
										<tr>
										<th>特質說明</th>
										<td>
											<!-- $特質說明文字-->
											<p>'.$P1[$i].'</p>
											<!--/ $特質說明文字-->
										</td>
										</tr>
										<tr>
										<th>強項與優點</th>
										<td>
											<!-- $強項與優點文字-->
											'.$P2[$i].'
											<!--/ $強項與優點文字-->
										</td>
										</tr>
										<tr>
										<th>人格建議</th>
										<td>
											<!-- $人格建議文字-->
											'.$P3[$i].'
											<!--/ $人格建議文字-->
										</td>
										</tr>
									</thead>
									</table>
									<!--/ =============================================================================================-->
									<!-- 詳細說明 ======================================================================================-->
									<div class="content-box" id="mt-description">
									<div class="heading mg-bottom-24">
										<h3 class="title color-main fw-b">詳細說明</h3>
									</div>
									<div class="content">
										<!-- $說明文字-->
										'.$P4[$i].'
										<!--/ $說明文字-->
									</div>
									</div>
									<!--/ =============================================================================================-->
									<!-- 適合領域 ======================================================================================-->
									<div class="content-box content-box-blue" id="mt-career">
									<ul>
										<li> <span class="mg-right-8">您適合的領域有:</span>
										<!-- $領域特徵--><span class="result color-main">'.$P5[$i].'</span>
										<!--/ $領域特徵-->
										</li>
										<li class="clearfix mg-top-8"><span class="pull-left mg-right-16">TOP 5 CAREERS:</span>
										<div class="career-list color-main">
											<!-- $適合職業-->'.$P6[$i].'
										</div>
										</li>
									</ul>
									</div>
									<!--/ =============================================================================================-->
								</div>
								<!--/ 結果 01 =============================================================================================-->
								';
							}
							else
							{
								
								echo '
								<!-- 結果 0'.($i+1).' ======================================================================================================-->
										<div class="container tab-pane fade print-beak-page" id="test_result_0'. ($i+1)  .'" role="tabpanel">
											<div class="heading-for-print tac">
											<h3 class="title-md fw-b">['.$NAME.']'.$V4[$i].' '.$Type[$V4[$i]].'</h3>
											</div>
											<!-- 得分結果 ======================================================================================-->
											<div class="row" id="mt-score-result">
											<div class="col-sm-3">
												<div class="mt-score-result-item color-blue">
												<h4 class="heading-title">精力支配</h4>
												<div class="data-group"><span class="data-title">'.(mb_substr( $V4[$i],0,1)=="E"?"外向":"內向").'</span><span class="data-content">'.(mb_substr( $V4[$i],0,1)=="E"?"E":"I").'</span>
													<!-- $分數--><span class="data-content score">('.$H1.')</span>
													<!--/ $分數-->
												</div>
												<div class="data-group data-group-disabled"><span class="data-title">'.(mb_substr( $V4[$i],0,1)!="E"?"外向":"內向").'</span><span class="data-content">'.(mb_substr( $V4[$i],0,1)!="E"?"E":"I").'</span>
													<!-- $分數--><span class="data-content score">('.$L1.')</span>
													<!--/ $分數-->
												</div>
												<!-- 較低分的數據，須在<div class="data-group"></div> 中，加上class="data-group-disabled"-->
												</div>
											</div>
											<div class="col-sm-3">
												<div class="mt-score-result-item color-blue-green">
												<h4 class="heading-title">認識世界</h4>
												<div class="data-group"><span class="data-title">'.(mb_substr( $V4[$i],1,1)=="S"?"實感":"直覺").'</span><span class="data-content">'.(mb_substr( $V4[$i],1,1)=="S"?"S":"N").'</span>
													<!-- $分數--><span class="data-content score">('.$H2.')</span>
													<!--/ $分數-->
												</div>
												<div class="data-group data-group-disabled"><span class="data-title">'.(mb_substr( $V4[$i],1,1)!="S"?"實感":"直覺").'</span><span class="data-content">'.(mb_substr( $V4[$i],1,1)!="S"?"S":"N").'</span>
													<!-- $分數--><span class="data-content score">('.$L2.')</span>
													<!--/ $分數-->
												</div>
												</div>
											</div>
											<div class="col-sm-3">
												<div class="mt-score-result-item color-green">
												<h4 class="heading-title">判斷事物</h4>
												<div class="data-group"><span class="data-title">'.(mb_substr( $V4[$i],2,1)=="T"?"思維":"情感").'</span><span class="data-content">'.(mb_substr( $V4[$i],2,1)=="T"?"T":"F").'</span>
													<!-- $分數--><span class="data-content score">('.$H3.')</span>
													<!--/ $分數-->
												</div>
												<div class="data-group data-group-disabled"><span class="data-title">'.(mb_substr( $V4[$i],2,1)!="T"?"思維":"情感").'</span><span class="data-content">'.(mb_substr( $V4[$i],2,1)!="T"?"T":"F").'</span>
													<!-- $分數--><span class="data-content score">('.$L3.')</span>
													<!--/ $分數-->
												</div>
												</div>
											</div>
											<div class="col-sm-3">
												<div class="mt-score-result-item color-yellow">
												<h4 class="heading-title">生活態度</h4>
												<div class="data-group"><span class="data-title">'.(mb_substr( $V4[$i],3,1)=="J"?"判斷":"知覺").'</span><span class="data-content">'.(mb_substr( $V4[$i],3,1)=="J"?"J":"P").'</span>
													<!-- $分數--><span class="data-content score">('.$H4.')</span>
													<!--/ $分數-->
												</div>
												<div class="data-group data-group-disabled"><span class="data-title">'.(mb_substr( $V4[$i],3,1)!="J"?"判斷":"知覺").'</span><span class="data-content">'.(mb_substr( $V4[$i],3,1)!="J"?"J":"P").'</span>
													<!-- $分數--><span class="data-content score">('.$L4.')</span>
													<!--/ $分數-->
												</div>
												</div>
											</div>
											</div>
											<!--/ =============================================================================================-->
											<!-- 說明表格 ======================================================================================-->
											<table class="table" id="mt-intro-table">
											<thead>
												<tr>
												<th>特質說明</th>
												<td>
													<!-- $特質說明文字-->
													<p>'.$P1[$i].'</p>
													<!--/ $特質說明文字-->
												</td>
												</tr>
												<tr>
												<th>強項與優點</th>
												<td>
													<!-- $強項與優點文字-->
													'.$P2[$i].'
													<!--/ $強項與優點文字-->
												</td>
												</tr>
												<tr>
												<th>人格建議</th>
												<td>
													<!-- $人格建議文字-->
													'.$P3[$i].'
													<!--/ $人格建議文字-->
												</td>
												</tr>
											</thead>
											</table>
											<!--/ =============================================================================================-->
											<!-- 詳細說明 ======================================================================================-->
											<div class="content-box" id="mt-description">
											<div class="heading mg-bottom-24">
												<h3 class="title color-main fw-b">詳細說明</h3>
											</div>
											<div class="content">
												<!-- $說明文字-->
												'.$P4[$i].'
												<!--/ $說明文字-->
											</div>
											</div>
											<!--/ =============================================================================================-->
											<!-- 適合領域 ======================================================================================-->
											<div class="content-box content-box-blue" id="mt-career">
											<ul>
												<li> <span class="mg-right-8">您適合的領域有:</span>
												<!-- $領域特徵--><span class="result color-main">'.$P5[$i].'</span>
												<!--/ $領域特徵-->
												</li>
												<li class="clearfix mg-top-8"><span class="pull-left mg-right-16">TOP 5 CAREERS:</span>
												<div class="career-list color-main">
													<!-- $適合職業-->'.$P6[$i].'
												</div>
												</li>
											</ul>
											</div>
											<!--/ =============================================================================================-->
										</div>
								<!--/ 結果 0'.($i+1).' ======================================================================================================-->
								';
							}
						}
						echo '
							</div>
							<!--/ **切換頁籤後顯示的內容 =============================================================================================-->
						</div>
						<script src="./js/vendor/jquery-1.11.3.min.js"></script>
						<script src="./js/vendor/bootstrap.min.js"></script>
						</body>
					</html>';
			}
			else
			{
				// echo "<script>alert('".$query."'); location.href = 'query.php';</script>";
				echo "<script>alert('查無此筆資料，請重新輸入'); location.href = 'query.php';</script>";
			}
		}
		else
		{
			echo '<meta http-equiv=REFRESH CONTENT=0;url=query.php>';
		}
	}
	else
		echo '<meta http-equiv=REFRESH CONTENT=0;url=query.php>';
?>
