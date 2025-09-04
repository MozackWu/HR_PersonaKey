<?php
// config/database.php - 資料庫連線設定
function getDatabaseConnection() {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "hr_database";
    
    $conn = new mysqli($servername, $username, $password, $dbname);
    $conn->set_charset("utf8");
    
    if ($conn->connect_error) {
        die("連線失敗: " . $conn->connect_error);
    }
    
    return $conn;
}

// functions/data_filter.php - 數據過濾函數
function getValidPersonalityData($conn) {
    $sql = "
    SELECT 
        CASE 
            WHEN NOTE LIKE 'E-%' THEN CONCAT('E', SUBSTRING(NOTE, 3, 3))
            WHEN NOTE LIKE 'I-%' THEN CONCAT('I', SUBSTRING(NOTE, 3, 3))
            ELSE 'UNKNOWN'
        END as personality_type,
        COUNT(*) as count,
        pa.COMP, pa.TIME
    FROM personalit_analysis pa
    WHERE pa.ID NOT LIKE 'A123456789'
        AND pa.ID NOT LIKE 'A%123%' 
        AND pa.NAME NOT LIKE 'TEST%'
        AND pa.NAME NOT LIKE '%測試%'
        AND pa.NAME NOT LIKE 'Admin%'
        AND pa.NAME NOT LIKE '%test%'
        AND LENGTH(pa.ID) = 10
        AND pa.ID REGEXP '^[A-Z][0-9]{9}$'
        AND pa.NOTE != ''
    GROUP BY personality_type
    HAVING personality_type != 'UNKNOWN'
    ORDER BY count DESC
    ";
    
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getDataValidityStats($conn) {
    // 總記錄數
    $totalSql = "SELECT COUNT(*) as total FROM personalit_analysis";
    $totalResult = $conn->query($totalSql);
    $total = $totalResult->fetch_assoc()['total'];
    
    // 有效記錄數
    $validSql = "
    SELECT COUNT(*) as valid_count 
    FROM personalit_analysis 
    WHERE ID NOT LIKE 'A123456789'
        AND ID NOT LIKE 'A%123%'
        AND NAME NOT LIKE 'TEST%'
        AND NAME NOT LIKE '%測試%'
        AND NAME NOT LIKE 'Admin%'
        AND NAME NOT LIKE '%test%'
        AND LENGTH(ID) = 10
        AND ID REGEXP '^[A-Z][0-9]{9}$'
        AND NOTE != ''
    ";
    
    $validResult = $conn->query($validSql);
    $valid = $validResult->fetch_assoc()['valid_count'];
    
    return [
        'total' => $total,
        'valid' => $valid,
        'excluded' => $total - $valid,
        'valid_rate' => $total > 0 ? round(($valid / $total) * 100, 1) : 0
    ];
}

function getMonthlyTrendData($conn) {
    $sql = "
    SELECT 
        DATE_FORMAT(TIME, '%Y-%m') as month,
        COUNT(*) as count
    FROM personalit_analysis 
    WHERE ID NOT LIKE 'A123456789'
        AND ID NOT LIKE 'A%123%'
        AND NAME NOT LIKE 'TEST%'
        AND NAME NOT LIKE '%測試%'
        AND NAME NOT LIKE 'Admin%'
        AND NAME NOT LIKE '%test%'
        AND LENGTH(ID) = 10
        AND ID REGEXP '^[A-Z][0-9]{9}$'
        AND TIME >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY month
    ORDER BY month
    ";
    
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getDimensionAnalysis($conn) {
    $sql = "
    SELECT 
        SUM(E) as total_e, SUM(I) as total_i,
        SUM(S) as total_s, SUM(N) as total_n,
        SUM(T) as total_t, SUM(F) as total_f,
        SUM(J) as total_j, SUM(P) as total_p,
        COUNT(*) as total_people
    FROM personalit_analysis 
    WHERE ID NOT LIKE 'A123456789'
        AND ID NOT LIKE 'A%123%'
        AND NAME NOT LIKE 'TEST%'
        AND NAME NOT LIKE '%測試%'
        AND NAME NOT LIKE 'Admin%'
        AND NAME NOT LIKE '%test%'
        AND LENGTH(ID) = 10
        AND ID REGEXP '^[A-Z][0-9]{9}$'
    ";
    
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

// auth.php - 驗證碼驗證 (使用現有Session管理)
function generateCaptcha() {
    $characters = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
    $captcha = '';
    for ($i = 0; $i < 5; $i++) {
        $captcha .= $characters[rand(0, strlen($characters) - 1)];
    }
    $_SESSION['captcha'] = $captcha;
    return $captcha;
}

function verifyCaptcha($input) {
    return isset($_SESSION['captcha']) && strtoupper($input) === $_SESSION['captcha'];
}

// 處理登入驗證
$showDashboard = false;
$error = '';

if (($_POST['action'] ?? '') === 'login') {
    if (verifyCaptcha($_POST['captcha'] ?? '')) {
        $_SESSION['authenticated'] = true;
        $showDashboard = true;
    } else {
        $error = '驗證碼錯誤，請重新輸入';
    }
} elseif ($_SESSION['authenticated'] ?? false) {
    $showDashboard = true;
}

// 如果已驗證，獲取數據 (使用現有資料庫連線)
$personalityData = [];
$validityStats = [];
$monthlyTrend = [];
$dimensionData = [];

if ($showDashboard) {
    try {
        // 使用現有的資料庫連線
        $personalityData = getValidPersonalityData($link);
        $validityStats = getDataValidityStats($link);
        $monthlyTrend = getMonthlyTrendData($link);
        $dimensionData = getDimensionAnalysis($link);
        
        // 關閉資料庫連線 (使用現有方法)
        DatabaseConfig::closeConnection($link);
    } catch (Exception $e) {
        error_log("數據獲取失敗: " . $e->getMessage());
        $error = "數據載入失敗，請稍後再試";
    }
}

$captcha = generateCaptcha();
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR人員分類分析系統</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Microsoft JhengHei', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        /* 登入頁面樣式 */
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .login-box {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }

        .login-title {
            font-size: 2rem;
            color: #2c3e50;
            margin-bottom: 30px;
            font-weight: bold;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ecf0f1;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #3498db;
        }

        .captcha-container {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .captcha-display {
            background: #2c3e50;
            color: white;
            padding: 12px 20px;
            border-radius: 10px;
            font-size: 1.5rem;
            font-weight: bold;
            letter-spacing: 3px;
            font-family: 'Courier New', monospace;
            min-width: 120px;
            text-align: center;
        }

        .captcha-input {
            flex: 1;
        }

        .login-btn {
            width: 100%;
            background: linear-gradient(45deg, #3498db, #2c3e50);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 10px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: bold;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(52, 152, 219, 0.3);
        }

        .error-message {
            background: #e74c3c;
            color: white;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        .refresh-captcha {
            background: none;
            border: none;
            color: #3498db;
            cursor: pointer;
            font-size: 0.9rem;
            text-decoration: underline;
            margin-top: 5px;
        }

        /* 原有的儀表板樣式保持不變 */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(45deg, #2c3e50, #3498db);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: repeating-conic-gradient(from 0deg at 50% 50%, transparent 0deg, rgba(255,255,255,0.1) 10deg, transparent 20deg);
            animation: rotate 20s linear infinite;
        }
        
        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }
        
        .header p {
            font-size: 1.2rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }
        
        .dashboard {
            padding: 30px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 2px solid transparent;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            border-color: #3498db;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 1rem;
            color: #7f8c8d;
            margin-bottom: 10px;
        }
        
        .stat-trend {
            font-size: 0.9rem;
            color: #27ae60;
        }
        
        .charts-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .chart-container {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            position: relative;
        }
        
        .chart-title {
            font-size: 1.3rem;
            color: #2c3e50;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 600;
        }
        
        .full-width {
            grid-column: 1 / -1;
        }

        @media (max-width: 768px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
            
            .header h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <?php if (!$showDashboard): ?>
    <!-- 登入頁面 -->
    <div class="login-container">
        <form method="POST" class="login-box">
            <div class="login-title">HR分析系統</div>
            
            <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <div class="form-group">
                <label>請輸入驗證碼以進入系統</label>
                <div class="captcha-container">
                    <div class="captcha-display"><?php echo $captcha; ?></div>
                    <input type="text" name="captcha" class="captcha-input" placeholder="輸入驗證碼" required>
                </div>
                <button type="button" class="refresh-captcha" onclick="location.reload()">重新產生驗證碼</button>
            </div>
            
            <input type="hidden" name="action" value="login">
            <button type="submit" class="login-btn">進入分析系統</button>
        </form>
    </div>
    
    <?php else: ?>
    <!-- 儀表板頁面 -->
    <div class="container">
        <div class="header">
            <button class="logout-btn" onclick="logout()">登出</button>
            <h1>HR人員分類分析系統</h1>
            <p>基於MBTI人格測驗的員工特質分析平台 (已排除測試數據)</p>
        </div>
        
        <div class="dashboard">
            <!-- 統計卡片 -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $validityStats['valid']; ?></div>
                    <div class="stat-label">有效測驗人數</div>
                    <div class="stat-trend">已排除 <?php echo $validityStats['excluded']; ?> 筆測試數據</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo count($personalityData); ?></div>
                    <div class="stat-label">人格類型數量</div>
                    <div class="stat-trend">多元化人才結構</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $validityStats['valid_rate']; ?>%</div>
                    <div class="stat-label">數據有效率</div>
                    <div class="stat-trend"><?php echo $validityStats['valid']; ?>/<?php echo $validityStats['total']; ?> 有效記錄</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">
                        <?php 
                        $topType = !empty($personalityData) ? $personalityData[0]['personality_type'] : 'N/A';
                        echo $topType;
                        ?>
                    </div>
                    <div class="stat-label">最多人格類型</div>
                    <div class="stat-trend">
                        <?php echo !empty($personalityData) ? $personalityData[0]['count'] . ' 人' : '無數據'; ?>
                    </div>
                </div>
            </div>

            <!-- 圖表區域 -->
            <div class="charts-grid">
                <div class="chart-container">
                    <div class="chart-title">人格類型分佈</div>
                    <canvas id="typeChart"></canvas>
                </div>
                <div class="chart-container">
                    <div class="chart-title">四維度分析</div>
                    <canvas id="dimensionChart"></canvas>
                </div>
                <div class="chart-container full-width">
                    <div class="chart-title">月度測驗趨勢</div>
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        // PHP數據轉JavaScript
        const personalityData = <?php echo json_encode($personalityData); ?>;
        const monthlyTrend = <?php echo json_encode($monthlyTrend); ?>;
        const dimensionData = <?php echo json_encode($dimensionData); ?>;

        // 初始化圖表
        function initCharts() {
            // 人格類型分佈圖
            if (personalityData.length > 0) {
                const ctx1 = document.getElementById('typeChart').getContext('2d');
                new Chart(ctx1, {
                    type: 'doughnut',
                    data: {
                        labels: personalityData.map(item => item.personality_type),
                        datasets: [{
                            data: personalityData.map(item => item.count),
                            backgroundColor: [
                                '#3498db', '#e74c3c', '#2ecc71', '#f39c12',
                                '#9b59b6', '#1abc9c', '#34495e', '#e67e22'
                            ],
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = ((context.parsed * 100) / total).toFixed(1);
                                        return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // 維度分析雷達圖
            if (dimensionData && dimensionData.total_people > 0) {
                const ctx2 = document.getElementById('dimensionChart').getContext('2d');
                new Chart(ctx2, {
                    type: 'radar',
                    data: {
                        labels: ['外向(E)', '內向(I)', '感覺(S)', '直覺(N)', '思考(T)', '情感(F)', '判斷(J)', '感知(P)'],
                        datasets: [{
                            label: '人員分佈',
                            data: [
                                parseInt(dimensionData.total_e),
                                parseInt(dimensionData.total_i),
                                parseInt(dimensionData.total_s),
                                parseInt(dimensionData.total_n),
                                parseInt(dimensionData.total_t),
                                parseInt(dimensionData.total_f),
                                parseInt(dimensionData.total_j),
                                parseInt(dimensionData.total_p)
                            ],
                            backgroundColor: 'rgba(52, 152, 219, 0.2)',
                            borderColor: 'rgba(52, 152, 219, 1)',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            r: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }

            // 月度趨勢圖
            if (monthlyTrend.length > 0) {
                const ctx3 = document.getElementById('trendChart').getContext('2d');
                new Chart(ctx3, {
                    type: 'line',
                    data: {
                        labels: monthlyTrend.map(item => item.month),
                        datasets: [{
                            label: '測驗人數',
                            data: monthlyTrend.map(item => item.count),
                            borderColor: 'rgba(52, 152, 219, 1)',
                            backgroundColor: 'rgba(52, 152, 219, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        }

        function logout() {
            if (confirm('確定要登出系統嗎？')) {
                window.location.href = '?logout=1';
            }
        }

        // 頁面加載完成後初始化圖表
        document.addEventListener('DOMContentLoaded', function() {
            initCharts();
        });
    </script>
    <?php endif; ?>
</body>
</html>

<?php
// 處理登出
if (isset($_POST['action']) && $_POST['action'] === 'logout') {
    // 清除Session
    $_SESSION = array();
    session_destroy();
    
    // 清除相關Cookie (模仿index.php的做法)
    if (isset($_COOKIE['Token'])) setcookie("Token", "", time()-3600);
    if (isset($_COOKIE['ID'])) setcookie("ID", "", time()-3600);
    if (isset($_COOKIE['Name'])) setcookie("Name", "", time()-3600);
    if (isset($_COOKIE['Time'])) setcookie("Time", "", time()-3600);
    
    // 返回JSON響應
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success']);
    exit;
}
?>