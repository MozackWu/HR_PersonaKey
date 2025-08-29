<?php
session_start();

require_once "config/mysql_connect.inc.php";
require_once "config/function_class.inc.php";

class PersonalityResultHandler {
    private $link;
    private $personalityTypes = [
        "ESTJ" => "督察型", "ESTP" => "挑戰型", "ESFJ" => "主人型", "ESFP" => "表演家型",
        "ENTJ" => "將領型", "ENTP" => "發明家型", "ENFJ" => "教育家型", "ENFP" => "記者型",
        "ISTJ" => "會計型", "ISTP" => "工匠型", "ISFJ" => "保護者型", "ISFP" => "藝術家型",
        "INTJ" => "軍師型", "INTP" => "學者型", "INFJ" => "諮商師型", "INFP" => "哲學家型"
    ];
    
    public function __construct($databaseLink) {
        $this->link = $databaseLink;
    }
    
    public function getPersonalityData($id, $date, $queryType) {
        $field = ($queryType === "ID") ? "ID" : "NAME";
        $query = "SELECT * FROM `personalit_analysis` 
                  WHERE `{$field}` = '" . mysqli_real_escape_string($this->link, $id) . "' 
                  AND `TIME` >= '" . mysqli_real_escape_string($this->link, $date) . " 00:00:00' 
                  AND `TIME` <= '" . mysqli_real_escape_string($this->link, $date) . " 23:59:59' 
                  ORDER BY `TIME` DESC LIMIT 1";
        
        $result = mysqli_query($this->link, $query);
        
        if (!$result || mysqli_num_rows($result) === 0) {
            return null;
        }
        
        return mysqli_fetch_array($result, MYSQLI_ASSOC);
    }
    
    public function calculatePersonalityTypes($values) {
        $combinations = [];
        
        // 計算所有可能的人格類型組合
        $dim1 = ($values["E"] > $values["I"]) ? ["E"] : 
               (($values["E"] == $values["I"]) ? ["E", "I"] : ["I"]);
        
        $dim2 = ($values["S"] > $values["N"]) ? ["S"] : 
               (($values["S"] == $values["N"]) ? ["S", "N"] : ["N"]);
        
        $dim3 = ($values["T"] > $values["F"]) ? ["T"] : 
               (($values["T"] == $values["F"]) ? ["T", "F"] : ["F"]);
        
        $dim4 = ($values["J"] > $values["P"]) ? ["J"] : 
               (($values["J"] == $values["P"]) ? ["J", "P"] : ["P"]);
        
        foreach ($dim1 as $d1) {
            foreach ($dim2 as $d2) {
                foreach ($dim3 as $d3) {
                    foreach ($dim4 as $d4) {
                        $combinations[] = $d1 . $d2 . $d3 . $d4;
                    }
                }
            }
        }
        
        return $combinations;
    }
    
    public function getAnalysisResults($personalityTypes) {
        $results = [];
        
        foreach ($personalityTypes as $type) {
            $query = "SELECT * FROM `analysis_result` WHERE `KEY_NAME` = '" . 
                     mysqli_real_escape_string($this->link, $type) . "'";
            $result = mysqli_query($this->link, $query);
            
            if ($result && mysqli_num_rows($result) > 0) {
                $results[] = mysqli_fetch_array($result, MYSQLI_ASSOC);
            }
        }
        
        return $results;
    }
    
    public function maskName($name) {
        if (empty($name)) return '';
        
        $length = mb_strlen($name, "UTF-8");
        if ($length == 2) {
            return mb_substr($name, 0, 1, "UTF-8") . "O";
        } else {
            $pattern = '/^(\X)(\X+)(\X)/u';
            preg_match($pattern, $name, $matches);
            return $matches[1] . str_repeat("O", $length - 2) . $matches[3];
        }
    }
    
    public function clearSession() {
        $cookiesToClear = ['Token', 'ID', 'Date', 'Time', 'QueryType'];
        foreach ($cookiesToClear as $cookie) {
            if (isset($_SESSION[$cookie])) {
                setcookie($cookie, "", time()-3600);
            }
        }
        session_destroy();
    }
}

// 驗證登入狀態
if (empty($_SESSION['Token']) || empty($_SESSION['Time'])) {
    header('Location: query.php');
    exit();
}

if (!Verify_LoginToken($_SESSION['Token'], $_SESSION['Time'])) {
    header('Location: query.php');
    exit();
}

$handler = new PersonalityResultHandler($link);

// 獲取Session資料
$date = $_SESSION['Date'];
$id = $_SESSION['ID'];
$queryType = $_SESSION['QueryType'];

// 清除Session
$handler->clearSession();

// 獲取人格分析資料
$data = $handler->getPersonalityData($id, $date, $queryType);

if (!$data) {
    echo "<script>alert('查無此筆資料，請重新輸入'); location.href = 'query.php';</script>";
    exit();
}

// 準備資料
$values = [
    "E" => $data["E"], "I" => $data["I"], "S" => $data["S"], "N" => $data["N"],
    "T" => $data["T"], "F" => $data["F"], "J" => $data["J"], "P" => $data["P"]
];

$name = $handler->maskName($data["NAME"]);
$personalityTypes = $handler->calculatePersonalityTypes($values);
$analysisResults = $handler->getAnalysisResults($personalityTypes);
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,Chrome=1">
    <title>人格分析報告</title>
    <link rel="stylesheet" href="assets/main.css">
    <style>
        .report-body {
            font-family: 'Microsoft JhengHei', '微軟正黑體', Arial, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            margin: 0;
            padding: 20px;
            line-height: 1.6;
        }
        .report-container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .report-header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e1e5e9;
        }
        .report-title {
            font-size: 2.5rem;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .personality-tabs {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
            margin-bottom: 30px;
        }
        .tab-btn {
            padding: 10px 20px;
            background: #ecf0f1;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: bold;
        }
        .tab-btn.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .score-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 25px;
            margin: 40px auto;
            max-width: 900px;
            padding: 0 20px;
        }
        .score-item {
            text-align: center;
            padding: 30px 20px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            transition: all 0.3s ease;
            position: relative;
        }
        .score-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }
        .score-item h4 {
            font-size: 1.3rem;
            margin-bottom: 10px;
            font-weight: bold;
        }
        .score-item .score {
            font-size: 2.8rem;
            font-weight: bold;
            margin-top: 10px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .score-item.color-blue { background: linear-gradient(135deg, #3498db, #2980b9); color: white; }
        .score-item.color-green { background: linear-gradient(135deg, #2ecc71, #27ae60); color: white; }
        .score-item.color-orange { background: linear-gradient(135deg, #f39c12, #e67e22); color: white; }
        .score-item.color-purple { background: linear-gradient(135deg, #9b59b6, #8e44ad); color: white; }
        
        /* 分隔線樣式 */
        .score-divider {
            width: 80%;
            height: 2px;
            background: linear-gradient(to right, transparent, #bdc3c7, transparent);
            margin: 35px auto;
            position: relative;
        }
        .score-divider::before {
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
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }
        .info-table th {
            background: #34495e;
            color: white;
            padding: 15px;
            text-align: left;
            width: 150px;
        }
        .info-table td {
            padding: 15px;
            border-bottom: 1px solid #ecf0f1;
        }
        .career-section {
            background: #e8f4fd;
            padding: 25px;
            border-radius: 10px;
            border-left: 5px solid #3498db;
            margin: 30px 0;
        }
        .career-list {
            color: #2980b9;
            font-weight: bold;
        }
        @media (max-width: 768px) {
            .report-container { padding: 20px; }
            .score-grid { 
                grid-template-columns: repeat(2, 1fr); 
                gap: 15px;
                margin: 30px auto;
                padding: 0 10px;
            }
            .score-item {
                padding: 20px 15px;
            }
            .score-item h4 {
                font-size: 1.1rem;
            }
            .score-item .score {
                font-size: 2.2rem;
            }
            .tab-btn { padding: 8px 15px; font-size: 0.9rem; }
        }
        @media print {
            .report-body { background: white; }
            .tab-btn { display: none; }
            .tab-content { display: block !important; page-break-before: always; }
            .tab-content:first-child { page-break-before: avoid; }
        }
    </style>
</head>

<body class="report-body">
    <div class="report-container">
        <div class="report-header">
            <div class="report-title">您的性格類型是:</div>
            
            <div class="personality-tabs">
                <?php foreach ($personalityTypes as $index => $type): ?>
                    <button class="tab-btn <?= $index === 0 ? 'active' : '' ?>" 
                            onclick="showTab(<?= $index ?>)">
                        [<?= $name ?>] <?= $type ?> <?= $handler->personalityTypes[$type] ?? '' ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <?php foreach ($personalityTypes as $index => $type): ?>
            <?php if (isset($analysisResults[$index])): ?>
                <?php $result = $analysisResults[$index]; ?>
                <div class="tab-content <?= $index === 0 ? 'active' : '' ?>" id="tab-<?= $index ?>">
                    
                    <div class="score-grid">
                        <div class="score-item color-blue">
                            <h4>精力支配</h4>
                            <div><?= substr($type, 0, 1) === 'E' ? '外向' : '內向' ?> (<?= substr($type, 0, 1) ?>)</div>
                            <div class="score"><?= $values[substr($type, 0, 1)] ?></div>
                        </div>
                        <div class="score-item color-green">
                            <h4>認識世界</h4>
                            <div><?= substr($type, 1, 1) === 'S' ? '實感' : '直覺' ?> (<?= substr($type, 1, 1) ?>)</div>
                            <div class="score"><?= $values[substr($type, 1, 1)] ?></div>
                        </div>
                        <div class="score-item color-orange">
                            <h4>判斷事物</h4>
                            <div><?= substr($type, 2, 1) === 'T' ? '思維' : '情感' ?> (<?= substr($type, 2, 1) ?>)</div>
                            <div class="score"><?= $values[substr($type, 2, 1)] ?></div>
                        </div>
                        <div class="score-item color-purple">
                            <h4>生活態度</h4>
                            <div><?= substr($type, 3, 1) === 'J' ? '判斷' : '知覺' ?> (<?= substr($type, 3, 1) ?>)</div>
                            <div class="score"><?= $values[substr($type, 3, 1)] ?></div>
                        </div>
                    </div>

                    <!-- 分隔線 -->
                    <div class="score-divider"></div>

                    <table class="info-table">
                        <tr>
                            <th>特質說明</th>
                            <td><?= $result['P1'] ?? '' ?></td>
                        </tr>
                        <tr>
                            <th>強項與優點</th>
                            <td><?= $result['P2'] ?? '' ?></td>
                        </tr>
                        <tr>
                            <th>人格建議</th>
                            <td><?= $result['P3'] ?? '' ?></td>
                        </tr>
                    </table>

                    <div style="margin: 30px 0;">
                        <h3 style="color: #2c3e50; margin-bottom: 15px;">詳細說明</h3>
                        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
                            <?= $result['P4'] ?? '' ?>
                        </div>
                    </div>

                    <div class="career-section">
                        <div style="margin-bottom: 15px;">
                            <strong>您適合的領域有：</strong>
                            <span class="career-list"><?= $result['P5'] ?? '' ?></span>
                        </div>
                        <div>
                            <strong>TOP 5 CAREERS：</strong>
                            <div class="career-list" style="margin-top: 10px;">
                                <?= $result['P6'] ?? '' ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <script>
        function showTab(index) {
            // 隱藏所有tab內容
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // 移除所有按鈕的active狀態
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // 顯示選中的tab
            document.getElementById('tab-' + index).classList.add('active');
            document.querySelectorAll('.tab-btn')[index].classList.add('active');
        }

        // 打印功能
        function printReport() {
            window.print();
        }

        // 添加鍵盤快捷鍵
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                printReport();
            }
        });
    </script>
</body>
</html>