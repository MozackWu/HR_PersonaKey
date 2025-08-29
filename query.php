<?php
	session_start();
	
	// 清除Session
	session_destroy();
	
	$cookiesToClear = ['Token', 'ID', 'Date', 'Time'];
	foreach ($cookiesToClear as $cookie) {
		if (isset($_SESSION[$cookie])) {
			setcookie($cookie, "", time()-3600);
		}
	}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>人格分析問卷 - 查詢結果</title>
	<link rel="stylesheet" href="assets/main.css">
	<style>
		.query-body {
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			min-height: 100vh;
			display: flex;
			align-items: center;
			justify-content: center;
			padding: 20px;
		}
		.query-container {
			background: rgba(255, 255, 255, 0.95);
			border-radius: 20px;
			padding: 40px;
			box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
			backdrop-filter: blur(10px);
			max-width: 500px;
			width: 100%;
			text-align: center;
		}
		.query-title {
			font-size: 2rem;
			color: #333;
			margin-bottom: 10px;
			font-weight: bold;
		}
		.query-subtitle {
			color: #666;
			margin-bottom: 30px;
			font-size: 0.9rem;
		}
		.radio-group {
			display: flex;
			justify-content: center;
			gap: 30px;
			margin-bottom: 20px;
		}
		.radio-option {
			display: flex;
			align-items: center;
			gap: 8px;
			cursor: pointer;
		}
		.radio-option input[type="radio"] {
			width: 18px;
			height: 18px;
		}
		#queryLabel {
			font-weight: 600;
			color: #333;
		}
	</style>
</head>

<body class="query-body">
	<div class="query-container">
		<div class="query-title">人格分析查詢</div>
		<div class="query-subtitle">請選擇查詢方式並輸入相關資料</div>
		
		<form id="queryForm" name="form1" method="post" action="connect.php">
			<div class="form-group">
				<div class="radio-group">
					<label class="radio-option">
						<input type="radio" name="QueryType" value="ID" onclick="updateQueryLabel('身份證字號')" checked>
						<span>身份證字號</span>
					</label>
					<label class="radio-option">
						<input type="radio" name="QueryType" value="NAME" onclick="updateQueryLabel('姓名')">
						<span>姓名</span>
					</label>
				</div>
			</div>
			
			<div class="form-group">
				<label class="form-label" id="queryLabel">身份證字號：</label>
				<input type="text" name="ID" class="form-input" placeholder="請輸入查詢資料" autocomplete="off" required>
			</div>
			
			<div class="form-group">
				<label class="form-label" for="datepicker">查詢日期：</label>
				<input type="date" id="datepicker" name="Date" class="form-input" required>
			</div>
			
			<div class="form-group">
				<label class="form-label" for="token">列印碼：</label>
				<input type="password" id="token" name="Token" class="form-input" placeholder="請輸入列印碼" required>
			</div>
			
			<input type="hidden" name="Type" value="Query">
			<button type="button" class="btn" onclick="validateQuery()">查詢結果</button>
		</form>
		
		<div class="version-info">查詢系統</div>
	</div>

	<script src="assets/main.js"></script>
	<script>
		// 設定今日日期
		document.addEventListener('DOMContentLoaded', function() {
			var today = new Date();
			var dateStr = today.getFullYear() + '-' + 
				(today.getMonth() + 1).toString().padStart(2, '0') + '-' + 
				today.getDate().toString().padStart(2, '0');
			document.getElementById('datepicker').value = dateStr;
		});
		
		// 更新查詢標籤
		function updateQueryLabel(labelText) {
			document.getElementById('queryLabel').textContent = labelText + '：';
			var input = document.querySelector('input[name="ID"]');
			input.placeholder = '請輸入' + labelText;
		}
		
		// 驗證查詢表單
		function validateQuery() {
			var queryType = document.querySelector('input[name="QueryType"]:checked').value;
			var queryValue = document.querySelector('input[name="ID"]').value.trim();
			var token = document.getElementById('token').value.trim();
			
			if (!queryValue) {
				alert('請輸入查詢資料');
				return;
			}
			
			if (queryType === 'ID') {
				queryValue = queryValue.toUpperCase();
				document.querySelector('input[name="ID"]').value = queryValue;
				if (!checkID(queryValue)) {
					alert('請輸入正確的身份證字號');
					return;
				}
			} else {
				if (!checkLength(queryValue, 2)) {
					alert('請輸入正確的姓名（至少2個字符）');
					return;
				}
			}
			
			if (!token) {
				alert('請輸入列印碼');
				return;
			}
			
			document.getElementById('queryForm').submit();
		}
		
		// Enter鍵提交
		document.addEventListener('keyup', function(event) {
			if (event.keyCode === 13) {
				event.preventDefault();
				validateQuery();
			}
		});
	</script>
</body>
</html>