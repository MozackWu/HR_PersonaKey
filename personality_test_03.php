<?php 
session_start();
require_once 'config/function_rand_questions.php';
require_once 'config/mysql_connect.inc.php';

error_reporting(E_ALL);
ini_set('display_errors','On');

// 處理前一頁的POST資料
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	foreach ($_POST as $key => $value) {
		if (strpos($key, 'radiobutton1_') === 0) {
			$_SESSION['answers'][$key] = $value;
		}
	}
}

$page = 'personality_test_03';
$page_questions = $_SESSION['page_questions'][$page] ?? [];
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>人格分析問卷 - 第三部份</title>
	<link rel="stylesheet" href="assets/main.css">
	<style>
		body {
			margin: 0;
			padding: 20px;
			font-family: 'Microsoft JhengHei', '微軟正黑體', Arial, sans-serif;
			background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
			min-height: 100vh;
			line-height: 1.6;
		}
		.test-container {
			max-width: 900px;
			margin: 0 auto;
			background: rgba(255, 255, 255, 0.95);
			border-radius: 20px;
			padding: 40px;
			box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
			backdrop-filter: blur(10px);
		}
		.test-header {
			text-align: center;
			margin-bottom: 40px;
		}
		.test-title {
			font-size: 2.5rem;
			color: #154360;
			margin-bottom: 10px;
			font-weight: bold;
		}
		.test-subtitle {
			font-size: 1.1rem;
			color: #9B59B6;
			margin-bottom: 0;
		}
		.question-container {
			background: #fff;
			border-radius: 15px;
			padding: 30px;
			margin-bottom: 25px;
			border: 2px solid #e8ecf3;
			transition: all 0.3s ease;
		}
		.question-container:hover {
			border-color: #667eea;
			box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15);
		}
		.question-text {
			font-size: 1.2rem;
			color: #996600;
			font-weight: bold;
			margin-bottom: 20px;
			text-align: center;
		}
		.answer-options {
			display: grid;
			grid-template-columns: 1fr 1fr;
			gap: 15px;
			margin-top: 15px;
		}
		.answer-option {
			position: relative;
			padding: 20px;
			border: 2px solid #e1e5e9;
			border-radius: 12px;
			transition: all 0.3s ease;
			cursor: pointer;
			background: #fafbfc;
		}
		.answer-option:hover {
			border-color: #667eea;
			background: #f0f4ff;
			transform: translateY(-2px);
		}
		.answer-option.selected {
			border-color: #667eea;
			background: #e8f0fe;
			box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
		}
		.answer-option input[type="radio"] {
			position: absolute;
			opacity: 0;
			width: 0;
			height: 0;
		}
		.answer-text {
			font-size: 1rem;
			color: #333;
			display: block;
			padding-left: 30px;
			position: relative;
		}
		.answer-text::before {
			content: '';
			position: absolute;
			left: 0;
			top: 2px;
			width: 18px;
			height: 18px;
			border: 2px solid #ddd;
			border-radius: 50%;
			background: #fff;
			transition: all 0.3s ease;
		}
		.answer-option.selected .answer-text::before {
			border-color: #667eea;
			background: #667eea;
			box-shadow: inset 0 0 0 3px #fff;
		}
		.next-btn {
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			color: white;
			border: none;
			padding: 15px 40px;
			border-radius: 50px;
			font-size: 1.1rem;
			font-weight: 600;
			cursor: pointer;
			transition: all 0.3s ease;
			margin: 40px auto 0;
			display: block;
			min-width: 200px;
		}
		.next-btn:hover {
			transform: translateY(-2px);
			box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
		}
		.next-btn:active {
			transform: translateY(0);
		}
		@media (max-width: 768px) {
			body {
				padding: 10px;
			}
			.test-container {
				padding: 20px;
			}
			.answer-options {
				grid-template-columns: 1fr;
			}
			.test-title {
				font-size: 2rem;
			}
		}
	</style>
</head>

<body class="test-body">

<?php
	include("config/function_class.inc.php");
	
	if(!empty($_SESSION['Token']) && !empty($_SESSION['Time']))
	{
		if(Verify_LoginToken($_SESSION['Token'],$_SESSION['Time']))
		{
			?>
			<div class="test-container">
				<div class="test-header">
					<div class="test-title">第三部份</div>
					<div class="test-subtitle">
						下列答案的描述中，哪一個最貼近您平時的思考和行為模式？<br>
						<small>（請著重在詞句的「意思」上，而不是它們好聽或好看！）</small>
					</div>
				</div>
				
				<form id="testForm" name="form1" method="post" action="personality_test_04.php">
					<?php $Number_Set = 1; ?>
					<?php foreach ($page_questions as $question): ?>
						<div class="question-container">
							<div class="question-text">
								3-<?= $Number_Set ?>：<?= $question['QUESTION'] ?>
							</div>
							<div class="answer-options">
								<label class="answer-option" for="q<?= $question['UID'] ?>_a">
									<input type="radio" id="q<?= $question['UID'] ?>_a" name="radiobutton1_<?= $question['UID'] ?>" value="A" required>
									<span class="answer-text"><?= $question['ANSWER_A'] ?></span>
								</label>
								<label class="answer-option" for="q<?= $question['UID'] ?>_b">
									<input type="radio" id="q<?= $question['UID'] ?>_b" name="radiobutton1_<?= $question['UID'] ?>" value="B">
									<span class="answer-text"><?= $question['ANSWER_B'] ?></span>
								</label>
							</div>
						</div>
					<?php $Number_Set++; ?>
					<?php endforeach; ?>
					
					<button type="button" class="next-btn" onclick="validateAndNext()">下一頁</button>
				</form>
			</div>
			
			<script src="assets/main.js"></script>

<?php
		}
		else 
			echo '<meta http-equiv=REFRESH CONTENT=0;url=index.php>';
	}
	else
		echo '<meta http-equiv=REFRESH CONTENT=0;url=index.php>';
?>

</body>

</html>