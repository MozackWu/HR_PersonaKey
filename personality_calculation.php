<?php
	session_start();
	error_reporting(E_ALL);
	ini_set('display_errors','On');

	// 保存使用者的回答
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		foreach ($_POST as $question_key => $answer) {
			if (strpos($question_key, 'radiobutton1_') === 0) {
				$_SESSION['answers'][$question_key] = $answer;
			}
		}
	}

	// 處理測驗結果計算
	date_default_timezone_set('Asia/Taipei');
	include("config/function_class.inc.php");
	require("config/mysql_connect.inc.php");
	
	$Name = '';
	$ID = '';
	$AnswerE = $AnswerI = $AnswerS = $AnswerN = $AnswerT = $AnswerF = $AnswerJ = $AnswerP = 0;
	$Answer1 = $Answer2 = $Answer3 = $Answer4 = '';
	$Note = '';
	
	if(!empty($_SESSION['Token']) && !empty($_SESSION['Time']))
	{
		if(Verify_LoginToken($_SESSION['Token'],$_SESSION['Time']))
		{
			$SecurityCode = GetSecurCode($_SESSION['Token'],$_SESSION['Time']);
			
			// 初始化結果計數
			$results = [
				'E' => 0, 'I' => 0, 'S' => 0, 'N' => 0,
				'F' => 0, 'T' => 0, 'J' => 0, 'P' => 0
			];
			
			$AnswerStr = "";
			
			// 計算結果
			foreach ($_SESSION['answers'] as $question_key => $answer) {
				// 從 radiobutton1_xxx 取得實際的問題ID
				$question_id = str_replace('radiobutton1_', '', $question_key);
				$sql = "SELECT * FROM `analysis_question` WHERE `UID` = '" . mysqli_real_escape_string($link,$question_id) . "' LIMIT 1;";
				$result = mysqli_query($link,$sql);
				
				if ($result->num_rows > 0) {
					$question = $result->fetch_assoc();
					if ($answer == 'A') {
						$results[$question['RESULT_A']]++;
						$AnswerStr .= $question_id . ":" . $question['RESULT_A'] . ",";
					} else {
						$results[$question['RESULT_B']]++;
						$AnswerStr .= $question_id . ":" . $question['RESULT_B'] . ",";
					}
				}
			}
			
			$AnswerStr = rtrim($AnswerStr, ',');
			
			// 提取各數值
			$AnswerE = $results['E']; $AnswerI = $results['I'];
			$AnswerS = $results['S']; $AnswerN = $results['N'];
			$AnswerT = $results['T']; $AnswerF = $results['F'];
			$AnswerJ = $results['J']; $AnswerP = $results['P'];
			
			// 產生人格類型說明
			$Answer1 = ($AnswerE > $AnswerI) ? '<font color="#E74C3C">E</font>' : 
					  (($AnswerE == $AnswerI) ? '<font color="#E74C3C">E</font><br><font color="#9B59B6">I</font>' : '<font color="#9B59B6">I</font>');
			
			$Answer2 = ($AnswerS > $AnswerN) ? '<font color="#3498DB">S</font>' : 
					  (($AnswerS == $AnswerN) ? '<font color="#3498DB">S</font><br><font color="#48C9B0">N</font>' : '<font color="#48C9B0">N</font>');
			
			$Answer3 = ($AnswerT > $AnswerF) ? '<font color="#D4AC0D">T</font>' : 
					  (($AnswerT == $AnswerF) ? '<font color="#D4AC0D">T</font><br><font color="#DC7633">F</font>' : '<font color="#DC7633">F</font>');
			
			$Answer4 = ($AnswerJ > $AnswerP) ? '<font color="#566573">J</font>' : 
					  (($AnswerJ == $AnswerP) ? '<font color="#566573">J</font><br><font color="#154360">P</font>' : '<font color="#154360">P</font>');
			
			// 產生註記
			$Note = "";
			$Note .= ($AnswerE > $AnswerI) ? "E" : (($AnswerE == $AnswerI) ? "(E,I)" : "I");
			$Note .= ($AnswerS > $AnswerN) ? "-S" : (($AnswerS == $AnswerN) ? "-(S,N)" : "-N");
			$Note .= ($AnswerT > $AnswerF) ? "-T" : (($AnswerT == $AnswerF) ? "-(T,F)" : "-F");
			$Note .= ($AnswerJ > $AnswerP) ? "-J" : (($AnswerJ == $AnswerP) ? "-(J,P)" : "-P");
			
			// 儲存資料
			$ID = $_SESSION['ID'] ?? '';
			$Name = $_SESSION['Name'] ?? '';
			
			// 寫入資料庫
			$query = "INSERT INTO `personalit_analysis` (`UID`,`COMP`, `ID`, `NAME`, `E`, `I`, `S`, `N`, `T`, `F`, `J`, `P`, `RESULT`, `NOTE`) VALUES (NULL, '" . mysqli_real_escape_string($link,$SecurityCode) . "', '" . mysqli_real_escape_string($link,$ID) . "', '" . mysqli_real_escape_string($link,$Name) . "', '" . mysqli_real_escape_string($link,$AnswerE) . "', '" . mysqli_real_escape_string($link,$AnswerI) . "', '" . mysqli_real_escape_string($link,$AnswerS) . "', '" . mysqli_real_escape_string($link,$AnswerN) . "', '" . mysqli_real_escape_string($link,$AnswerT) . "', '" . mysqli_real_escape_string($link,$AnswerF) . "', '" . mysqli_real_escape_string($link,$AnswerJ) . "', '" . mysqli_real_escape_string($link,$AnswerP) . "', '" . mysqli_real_escape_string($link,$AnswerStr) . "', '" . mysqli_real_escape_string($link,$Note) . "');";

			if (mysqli_query($link, $query) === false) {
				printf("Error: %s\n", mysqli_sqlstate($link));
			}
			mysqli_close($link);		
			
			// 清除Session cookies
			$cookiesToClear = ['Token', 'ID', 'Name', 'Time'];
			foreach ($cookiesToClear as $cookie) {
				if (isset($_SESSION[$cookie])) {
					setcookie($cookie, "", time()-3600);
				}
			}
			session_destroy();
		}
	}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>人格分析結果</title>
	<link rel="stylesheet" type="text/css" href="chart/css/style.css">
	<script src="chart/js/Chart.min.js"></script>
	<script src="chart/js/utils.js"></script>
	<style>
		body {
			margin: 0;
			padding: 20px;
			font-family: 'Microsoft JhengHei', '微軟正黑體', Arial, sans-serif;
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			min-height: 100vh;
			line-height: 1.6;
		}
		.result-container {
			max-width: 800px;
			margin: 0 auto;
			background: rgba(255, 255, 255, 0.95);
			border-radius: 20px;
			padding: 40px;
			box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
			backdrop-filter: blur(10px);
			text-align: center;
		}
		.result-title {
			font-size: 2.5rem;
			color: #154360;
			margin-bottom: 30px;
			font-weight: bold;
		}
		.user-info {
			display: flex;
			justify-content: center;
			gap: 30px;
			margin-bottom: 30px;
			flex-wrap: wrap;
		}
		.user-info-item {
			font-size: 1.2rem;
			color: #1A5276;
			font-weight: bold;
		}
		.chart-container {
			background: #fff;
			border-radius: 15px;
			padding: 30px;
			margin: 30px 0;
			box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
		}
		.chart-wrapper {
			position: relative;
			width: 100%;
			max-width: 500px;
			margin: 0 auto;
		}
		.query-form {
			background: rgba(255, 255, 255, 0.9);
			border-radius: 15px;
			padding: 30px;
			margin-top: 30px;
			box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
		}
		.form-group {
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 15px;
			flex-wrap: wrap;
			margin-bottom: 20px;
		}
		.form-label {
			font-size: 1.1rem;
			color: #333;
			font-weight: 600;
		}
		.form-input {
			padding: 10px 15px;
			border: 2px solid #e1e5e9;
			border-radius: 8px;
			font-size: 16px;
			transition: all 0.3s ease;
			min-width: 200px;
		}
		.form-input:focus {
			outline: none;
			border-color: #667eea;
			box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
		}
		.submit-btn {
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			color: white;
			border: none;
			padding: 12px 30px;
			border-radius: 8px;
			font-size: 16px;
			font-weight: 600;
			cursor: pointer;
			transition: all 0.3s ease;
		}
		.submit-btn:hover {
			transform: translateY(-2px);
			box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
		}
		.submit-btn:active {
			transform: translateY(0);
		}
		
		/* 分數展示區域 */
		.score-display {
			margin: 40px 0;
		}
		.score-grid {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
			gap: 20px;
			max-width: 800px;
			margin: 0 auto;
		}
		.score-card {
			background: white;
			border-radius: 15px;
			padding: 25px;
			text-align: center;
			box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
			transition: all 0.3s ease;
			border: 3px solid;
		}
		.score-card:hover {
			transform: translateY(-5px);
			box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
		}
		.score-card.blue {
			border-color: #3498db;
		}
		.score-card.green {
			border-color: #2ecc71;
		}
		.score-card.orange {
			border-color: #f39c12;
		}
		.score-card.purple {
			border-color: #9b59b6;
		}
		.score-title {
			font-size: 1.1rem;
			font-weight: bold;
			color: #2c3e50;
			margin-bottom: 8px;
		}
		.score-type {
			font-size: 0.95rem;
			color: #7f8c8d;
			margin-bottom: 10px;
		}
		.score-value {
			font-size: 2.5rem;
			font-weight: bold;
			color: #154360;
		}
		.score-card.blue .score-value {
			color: #3498db;
		}
		.score-card.green .score-value {
			color: #2ecc71;
		}
		.score-card.orange .score-value {
			color: #f39c12;
		}
		.score-card.purple .score-value {
			color: #9b59b6;
		}
		
		/* 分隔線 */
		.divider {
			width: 80%;
			height: 2px;
			background: linear-gradient(to right, transparent, #bdc3c7, transparent);
			margin: 30px auto;
			position: relative;
		}
		.divider::before {
			content: '';
			position: absolute;
			left: 50%;
			top: 50%;
			transform: translate(-50%, -50%);
			width: 8px;
			height: 8px;
			background: #667eea;
			border-radius: 50%;
		}
		
		@media (max-width: 768px) {
			body {
				padding: 10px;
			}
			.result-container {
				padding: 20px;
			}
			.user-info {
				flex-direction: column;
				gap: 15px;
			}
			.form-group {
				flex-direction: column;
				align-items: stretch;
			}
			.result-title {
				font-size: 2rem;
			}
			.score-grid {
				grid-template-columns: repeat(2, 1fr);
				gap: 15px;
			}
			.score-card {
				padding: 20px;
			}
			.score-value {
				font-size: 2rem;
			}
		}
	</style>
</head>

<body class="result-body">

	<script src="assets/main.js"></script>
			
			<div class="result-container">
				<div class="result-title">測驗結果</div>
				
				<div class="user-info">
					<div class="user-info-item">姓名：<?= $Name ?></div>
					<div class="user-info-item">身份證：<?= replace_symbol_text($ID,'＊',3,2) ?></div>
				</div>
				
				<div class="chart-container">
					<div class="chart-wrapper">
						<canvas id="chart-0" width="450" height="400"></canvas>
					</div>
				</div>
				
				<!-- 人格維度分數展示 -->
				<div class="score-display">
					<div class="score-grid">
						<div class="score-card blue">
							<div class="score-title">精力支配</div>
							<div class="score-type"><?= ($AnswerE > $AnswerI) ? '外向 (E)' : (($AnswerE == $AnswerI) ? '外向/內向 (E/I)' : '內向 (I)') ?></div>
							<div class="score-value"><?= ($AnswerE > $AnswerI) ? $AnswerE : (($AnswerE == $AnswerI) ? $AnswerE.'/'.$AnswerI : $AnswerI) ?></div>
						</div>
						<div class="score-card green">
							<div class="score-title">認識世界</div>
							<div class="score-type"><?= ($AnswerS > $AnswerN) ? '實感 (S)' : (($AnswerS == $AnswerN) ? '實感/直覺 (S/N)' : '直覺 (N)') ?></div>
							<div class="score-value"><?= ($AnswerS > $AnswerN) ? $AnswerS : (($AnswerS == $AnswerN) ? $AnswerS.'/'.$AnswerN : $AnswerN) ?></div>
						</div>
						<div class="score-card orange">
							<div class="score-title">判斷事物</div>
							<div class="score-type"><?= ($AnswerT > $AnswerF) ? '思維 (T)' : (($AnswerT == $AnswerF) ? '思維/情感 (T/F)' : '情感 (F)') ?></div>
							<div class="score-value"><?= ($AnswerT > $AnswerF) ? $AnswerT : (($AnswerT == $AnswerF) ? $AnswerT.'/'.$AnswerF : $AnswerF) ?></div>
						</div>
						<div class="score-card purple">
							<div class="score-title">生活態度</div>
							<div class="score-type"><?= ($AnswerJ > $AnswerP) ? '判斷 (J)' : (($AnswerJ == $AnswerP) ? '判斷/知覺 (J/P)' : '知覺 (P)') ?></div>
							<div class="score-value"><?= ($AnswerJ > $AnswerP) ? $AnswerJ : (($AnswerJ == $AnswerP) ? $AnswerJ.'/'.$AnswerP : $AnswerP) ?></div>
						</div>
					</div>
				</div>
				
				<!-- 分隔線 -->
				<div class="divider"></div>
				
				<div class="query-form">
					<h3 style="color: #154360; margin-bottom: 20px;">查看詳細報告</h3>
					<form id="queryForm" method="post" action="connect.php">
						<div class="form-group">
							<label class="form-label" for="token">列印碼：</label>
							<input type="password" id="token" name="Token" class="form-input" placeholder="請輸入列印碼" required>
							<button type="button" class="submit-btn" onclick="submitForm()">查看報告</button>
						</div>
						<input type="hidden" name="Type" value="Query">
						<input type="hidden" name="ID" value="<?= $ID ?>">
						<input type="hidden" name="Date" value="<?= date('Y-m-d') ?>">
						<input type="hidden" name="QueryType" value="ID">
					</form>
				</div>
			</div>
			
			<script>
				Chart.defaults.global.defaultFontSize = 14;

				var utils = Samples.utils;

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
					return v < 5 ? 4 : v < 10 ? 5 : v < 15 ? 6 : v < 20 ? 7 : 8;
				}
				
				var data = {
					labels: ["E（<?= $AnswerE ?>）","I（<?= $AnswerI ?>）","S（<?= $AnswerS ?>）","N（<?= $AnswerN ?>）","T（<?= $AnswerT ?>）","F（<?= $AnswerF ?>）","J（<?= $AnswerJ ?>）","P（<?= $AnswerP ?>）"],
					datasets: [{
						data: [<?= $AnswerE ?>,<?= $AnswerI ?>,<?= $AnswerS ?>,<?= $AnswerN ?>,<?= $AnswerT ?>,<?= $AnswerF ?>,<?= $AnswerJ ?>,<?= $AnswerP ?>],
						backgroundColor: Chart.helpers.color("#4dc9f6").alpha(0.2).rgbString(),
						borderColor: "#4dc9f6",
					}]
				};
				
				<?php
				$max = max($AnswerE, $AnswerI, $AnswerS, $AnswerN, $AnswerT, $AnswerF, $AnswerJ, $AnswerP);
				$stepSize = ceil($max / 4);
				$max = $stepSize * 4;
				?>

				var options = {
					legend: false,
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
							fontColor: [
								Chart.helpers.color("#800000").alpha(1).rgbString(),
								Chart.helpers.color("#800000").alpha(1).rgbString(),
								Chart.helpers.color("#FF4500").alpha(1).rgbString(),
								Chart.helpers.color("#FF4500").alpha(1).rgbString(),
								Chart.helpers.color("#228B22").alpha(1).rgbString(),
								Chart.helpers.color("#228B22").alpha(1).rgbString(),
								Chart.helpers.color("#000000").alpha(1).rgbString(),
								Chart.helpers.color("#000000").alpha(1).rgbString()
							], 
						},
						ticks: {
							stepSize: <?= $stepSize ?>,
							min: 0,
							max: <?= $max ?>,
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
				});
			</script>

</body>
</html>