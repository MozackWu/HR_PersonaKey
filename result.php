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

// 取得Session資料
$date = $_SESSION['Date'];
$id = $_SESSION['ID'];
$queryType = $_SESSION['QueryType'];

// 清除Session
$handler->clearSession();

// 取得人格分析資料
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
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin: 40px auto;
            max-width: 900px;
            padding: 0 20px;
        }
        .score-item {
            text-align: center;
            padding: 20px 10px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            transition: all 0.3s ease;
            position: relative;
            min-height: 140px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .score-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }
        .score-item h4 {
            font-size: 1.1rem;
            margin-bottom: 8px;
            font-weight: bold;
            line-height: 1.2;
        }
        .score-item .score {
            font-size: 2.2rem;
            font-weight: bold;
            margin-top: 8px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
            line-height: 1;
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
                grid-template-columns: repeat(4, 1fr);
                gap: 10px;
                margin: 30px auto;
                padding: 0 10px;
            }
            .score-item {
                padding: 15px 8px;
                min-height: 120px;
            }
            .score-item h4 {
                font-size: 0.9rem;
                margin-bottom: 5px;
            }
            .score-item .score {
                font-size: 1.8rem;
            }
            .tab-btn { padding: 8px 15px; font-size: 0.9rem; }
        }
        @media (max-width: 480px) {
            .score-grid { 
                gap: 8px;
                padding: 0 5px;
            }
            .score-item {
                padding: 12px 5px;
                min-height: 100px;
            }
            .score-item h4 {
                font-size: 0.8rem;
                line-height: 1.1;
            }
            .score-item .score {
                font-size: 1.6rem;
                margin-top: 5px;
            }
        }
        /* PDF 儲存按鈕 */
        .pdf-save-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 15px 25px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 8px 25px rgba(231, 76, 60, 0.3);
            transition: all 0.3s ease;
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .pdf-save-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(231, 76, 60, 0.4);
        }
        .pdf-save-btn:active {
            transform: translateY(0);
        }
        .pdf-icon {
            font-size: 18px;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 0.5in 0.4in;
                @top-left { content: none; }
                @top-center { content: none; }
                @top-right { content: none; }
                @bottom-left { content: none; }
                @bottom-center { content: none; }
                @bottom-right { content: none; }
            }
            
            html, body {
                height: auto !important;
                overflow: visible !important;
                font-size: 13px !important;
                line-height: 1.3 !important;
                margin: 0 !important;
                padding: 0 !important;
                font-family: 'Microsoft JhengHei', '微軟正黑體', Arial, sans-serif !important;
            }
            
            /* 確保色彩準確顯示 */
            * {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
                box-shadow: none !important; /* 移除所有陰影效果 */
                outline: none !important;
                text-decoration: none !important;
                float: none !important;
                clear: both !important;
            }
            
            /* 移除可能產生線條的元素 */
            input, button, select, textarea {
                border: none !important;
                outline: none !important;
                box-shadow: none !important;
            }
            
            /* 確保沒有頁首頁尾內容 */
            @page :first {
                margin-top: 0.5in;
            }
            
            .report-body { 
                background: white !important; /* 改為純白背景 */
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
                padding: 20px !important;
                margin: 0 !important;
                min-height: auto !important;
            }
            
            .report-container {
                max-width: none !important;
                margin: 0 auto !important;
                padding: 30px !important;
                box-shadow: none !important; /* 完全移除陰影 */
                border-radius: 0 !important; /* 移除圓角 */
                background: white !important;
                border: none !important;
            }
            
            /* 隱藏不必要元素 */
            .pdf-save-btn { 
                display: none !important; 
            }
            
            /* 只顯示活動的 tab 內容 */
            .tab-content { 
                display: none !important;
            }
            .tab-content.active { 
                display: block !important;
            }
            
            /* 適合直向的標題格式 */
            .report-header {
                text-align: center !important;
                margin-bottom: 20px !important;
                padding-bottom: 15px !important;
                border-bottom: 2px solid #e1e5e9 !important;
            }
            .report-title {
                font-size: 1.5rem !important;
                color: #2c3e50 !important;
                margin-bottom: 8px !important;
                font-weight: bold !important;
            }
            
            /* 在 PDF 中顯示性格類型 */
            .personality-tabs {
                display: block !important;
                text-align: center !important;
                margin-bottom: 15px !important;
            }
            .tab-btn {
                display: inline-block !important;
                padding: 10px 20px !important;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
                color: white !important;
                border: none !important;
                border-radius: 25px !important;
                font-weight: bold !important;
                font-size: 1rem !important;
                margin: 0 !important;
                box-shadow: none !important; /* 移除陰影 */
            }
            .tab-btn:not(.active) {
                display: none !important;
            }
            
            /* 重點修正：分數卡片格式 */
            .score-grid {
                display: grid !important;
                grid-template-columns: repeat(4, 1fr) !important;
                gap: 12px !important;
                margin: 20px auto !important;
                max-width: 100% !important;
                padding: 0 !important;
                background: transparent !important; /* 確保背景透明 */
            }
            .score-item {
                text-align: center !important;
                padding: 20px 12px !important;
                border-radius: 10px !important; /* 減少圓角 */
                box-shadow: none !important; /* 完全移除陰影 */
                border: 2px solid rgba(255,255,255,0.3) !important; /* 添加邊框替代陰影 */
                transition: none !important;
                position: relative !important;
                min-height: 120px !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: center !important;
                overflow: hidden !important; /* 防止內容溢出 */
            }
            .score-item h4 {
                font-size: 1rem !important;
                margin-bottom: 8px !important;
                font-weight: bold !important;
                line-height: 1.2 !important;
                z-index: 10 !important; /* 確保文字在最上層 */
                position: relative !important;
            }
            .score-item .score {
                font-size: 2.2rem !important;
                font-weight: bold !important;
                margin-top: 8px !important;
                text-shadow: none !important; /* 移除文字陰影 */
                line-height: 1 !important;
                z-index: 10 !important; /* 確保文字在最上層 */
                position: relative !important;
            }
            
            /* 維持原有顏色漸層 */
            .score-item.color-blue { 
                background: linear-gradient(135deg, #3498db, #2980b9) !important; 
                color: white !important;
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            .score-item.color-green { 
                background: linear-gradient(135deg, #2ecc71, #27ae60) !important; 
                color: white !important;
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            .score-item.color-orange { 
                background: linear-gradient(135deg, #f39c12, #e67e22) !important; 
                color: white !important;
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            .score-item.color-purple { 
                background: linear-gradient(135deg, #9b59b6, #8e44ad) !important; 
                color: white !important;
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            
            /* 修正分隔線格式 - Chrome 專用 */
            .score-divider {
                width: 80% !important;
                height: 2px !important;
                background: #bdc3c7 !important; /* 簡化為純色 */
                background-image: none !important;
                margin: 20px auto !important;
                position: relative !important;
                border: none !important;
                box-shadow: none !important;
                -webkit-box-shadow: none !important;
                filter: none !important;
                -webkit-filter: none !important;
            }
            .score-divider::before {
                content: '' !important;
                position: absolute !important;
                left: 50% !important;
                top: 50% !important;
                transform: translate(-50%, -50%) !important;
                -webkit-transform: translate(-50%, -50%) !important;
                width: 8px !important;
                height: 8px !important;
                background: #667eea !important;
                background-color: #667eea !important;
                background-image: none !important;
                border-radius: 50% !important;
                -webkit-border-radius: 50% !important;
                border: none !important;
                box-shadow: none !important;
                -webkit-box-shadow: none !important;
            }
            
            /* 表格格式優化 - Chrome 專用 */
            .info-table {
                width: 100% !important;
                border-collapse: collapse !important;
                margin: 15px 0 !important;
                font-size: 0.8rem !important;
                line-height: 1.3 !important;
                box-shadow: none !important;
                -webkit-box-shadow: none !important;
                filter: none !important;
                -webkit-filter: none !important;
            }
            .info-table th {
                background: #34495e !important;
                background-color: #34495e !important;
                background-image: none !important;
                color: white !important;
                padding: 8px !important;
                text-align: left !important;
                width: 100px !important;
                font-size: 0.85rem !important;
                border: none !important;
                box-shadow: none !important;
                -webkit-box-shadow: none !important;
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .info-table td {
                padding: 10px !important;
                border-bottom: 1px solid #ecf0f1 !important;
                font-size: 0.75rem !important;
                line-height: 1.4 !important;
                vertical-align: top !important;
                border-left: none !important;
                border-right: none !important;
                border-top: none !important;
                background: white !important;
                background-color: white !important;
                background-image: none !important;
            }
            
            /* 職業建議區塊格式 - Chrome 專用 */
            .career-section {
                background: #e8f4fd !important;
                background-color: #e8f4fd !important;
                background-image: none !important;
                padding: 12px !important;
                border-radius: 6px !important;
                -webkit-border-radius: 6px !important;
                border-left: 4px solid #3498db !important;
                margin: 15px 0 !important;
                font-size: 0.8rem !important;
                line-height: 1.4 !important;
                box-shadow: none !important;
                -webkit-box-shadow: none !important;
                border-top: none !important;
                border-right: none !important;
                border-bottom: none !important;
                filter: none !important;
                -webkit-filter: none !important;
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .career-list {
                color: #2980b9 !important;
                font-weight: bold !important;
                font-size: 0.8rem !important;
                line-height: 1.4 !important;
            }
            
            /* 詳細說明區塊格式 - Chrome 專用 */
            div[style*="margin: 30px 0"] {
                margin: 15px 0 !important;
            }
            div[style*="padding: 20px"] {
                padding: 12px !important;
            }
            div[style*="background: #f8f9fa"] {
                background: #f8f9fa !important;
                background-color: #f8f9fa !important;
                background-image: none !important;
                padding: 12px !important;
                border-radius: 6px !important;
                -webkit-border-radius: 6px !important;
                font-size: 0.75rem !important;
                line-height: 1.4 !important;
                box-shadow: none !important;
                -webkit-box-shadow: none !important;
                border: none !important;
                filter: none !important;
                -webkit-filter: none !important;
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            h3 {
                color: #2c3e50 !important;
                margin-bottom: 8px !important;
                font-size: 1rem !important;
                font-weight: bold !important;
                text-shadow: none !important;
                -webkit-text-shadow: none !important;
            }
            
            /* 防止分頁 */
            .score-grid,
            .info-table,
            .career-section {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            
            /* 確保直向內容適合頁面 */
            .tab-content.active {
                transform: none !important;
                -webkit-transform: none !important;
                width: 100% !important;
                margin-left: 0 !important;
            }
            
            /* Chrome 額外修正：移除所有可能的偽元素效果 */
            *::before, *::after {
                box-shadow: none !important;
                -webkit-box-shadow: none !important;
                text-shadow: none !important;
                -webkit-text-shadow: none !important;
                filter: none !important;
                -webkit-filter: none !important;
                backdrop-filter: none !important;
                -webkit-backdrop-filter: none !important;
            }
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

    <!-- PDF 儲存按鈕 -->
    <button class="pdf-save-btn" onclick="savePDF()" title="儲存為PDF">
        <span class="pdf-icon">📄</span>
        儲存PDF
    </button>

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

        // PDF 儲存功能
        let isPrintingInProgress = false;
        
        function savePDF() {
            // 防止重複點擊
            if (isPrintingInProgress) {
                return;
            }
            
            isPrintingInProgress = true;
            
            // 取得當前活動的 tab
            const activeTab = document.querySelector('.tab-content.active');
            const allTabs = document.querySelectorAll('.tab-content');
            const personalityTabs = document.querySelector('.personality-tabs');
            const pdfBtn = document.querySelector('.pdf-save-btn');
            
            if (!activeTab) {
                isPrintingInProgress = false;
                return;
            }
            
            // 備份原始顯示狀態
            const originalStates = {
                tabs: [],
                personalityTabsDisplay: personalityTabs ? personalityTabs.style.display : '',
                btnText: pdfBtn ? pdfBtn.innerHTML : ''
            };
            
            allTabs.forEach((tab, index) => {
                originalStates.tabs[index] = {
                    display: tab.style.display,
                    visibility: tab.style.visibility
                };
            });
            
            // 更新按鈕狀態
            if (pdfBtn) {
                pdfBtn.innerHTML = '<span class="pdf-icon">⏳</span>準備中...';
                pdfBtn.style.pointerEvents = 'none';
            }
            
            // 隱藏所有非活動的 tab
            allTabs.forEach(tab => {
                if (tab !== activeTab) {
                    tab.style.display = 'none';
                    tab.style.visibility = 'hidden';
                }
            });
            
            // 在 PDF 中保留性格類型標籤顯示
            if (personalityTabs) {
                personalityTabs.style.display = 'block';
            }
            
            // 設定頁面標題
            const activeTabBtn = document.querySelector('.tab-btn.active');
            let fileName = '人格分析報告';
            if (activeTabBtn) {
                const userName = activeTabBtn.textContent.match(/\[(.*?)\]/);
                if (userName) {
                    fileName = `${userName[1]}_人格分析報告`;
                }
            }
            
            // 儲存原始標題並設定新標題
            const originalTitle = document.title;
            document.title = fileName;
            
            // 恢復狀態的函數
            function restoreOriginalState() {
                // 恢復所有 tab 狀態
                allTabs.forEach((tab, index) => {
                    if (originalStates.tabs[index]) {
                        tab.style.display = originalStates.tabs[index].display;
                        tab.style.visibility = originalStates.tabs[index].visibility;
                    }
                });
                
                // 恢復 tab 切換按鈕
                if (personalityTabs) {
                    personalityTabs.style.display = originalStates.personalityTabsDisplay;
                }
                
                // 恢復按鈕狀態
                if (pdfBtn) {
                    pdfBtn.innerHTML = originalStates.btnText;
                    pdfBtn.style.pointerEvents = '';
                }
                
                // 恢復頁面標題
                document.title = originalTitle;
                
                // 重置狀態
                isPrintingInProgress = false;
            }
            
            // 延遲執行打印，確保布局完成
            setTimeout(() => {
                try {
                    window.print();
                } catch (error) {
                    console.log('Print dialog error:', error);
                }
                
                // 無論打印成功或失敗，都要恢復狀態
                setTimeout(restoreOriginalState, 1000);
            }, 200);
            
            // 監聽打印對話框關閉事件（備用恢復機制）
            const mediaQueryList = window.matchMedia('print');
            const printHandler = (mql) => {
                if (!mql.matches) {
                    // 打印對話框已關閉
                    setTimeout(() => {
                        if (isPrintingInProgress) {
                            restoreOriginalState();
                        }
                    }, 500);
                    mediaQueryList.removeListener(printHandler);
                }
            };
            
            if (mediaQueryList.addListener) {
                mediaQueryList.addListener(printHandler);
            } else if (mediaQueryList.addEventListener) {
                mediaQueryList.addEventListener('change', printHandler);
            }
        }

        // 打印功能（保留原有功能）
        function printReport() {
            savePDF();
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