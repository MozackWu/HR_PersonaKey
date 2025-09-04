<?php
session_start();

// 安全性提升：添加 CSRF 防護和輸入驗證
class AuthenticationHandler {
    private static $validTokens = ['00127691', '95430016', '00023817']; // 建議：移到配置檔案或資料庫
    
    public static function validateInput($data) {
        return trim(htmlspecialchars($data, ENT_QUOTES, 'UTF-8'));
    }
    
    public static function isValidToken($token) {
        return in_array($token, self::$validTokens);
    }
    
    public static function generateSessionToken($token, $time) {
        return base64_encode(md5(base64_encode($token) . "HR System" . $time));
    }
    
    public static function clearSession() {
        $cookiesToClear = ['Token', 'ID', 'Name', 'Date', 'Time', 'QueryType'];
        foreach ($cookiesToClear as $cookie) {
            if (isset($_SESSION[$cookie])) {
                setcookie($cookie, "", time()-3600, "/", "", false, true); // 添加 httpOnly
            }
        }
        session_destroy();
    }
    
    public static function redirectTo($url) {
        header("Location: " . $url);
        exit();
    }
}

// 驗證 HTTP 方法
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    AuthenticationHandler::redirectTo('index.php');
}

// 獲取並驗證輸入
$token = isset($_POST['Token']) ? AuthenticationHandler::validateInput($_POST['Token']) : '';
$id = isset($_POST['ID']) ? AuthenticationHandler::validateInput($_POST['ID']) : '';
$name = isset($_POST['Name']) ? AuthenticationHandler::validateInput($_POST['Name']) : '';
$date = isset($_POST['Date']) ? AuthenticationHandler::validateInput($_POST['Date']) : '';
$type = isset($_POST['Type']) ? AuthenticationHandler::validateInput($_POST['Type']) : '';
$queryType = isset($_POST['QueryType']) ? AuthenticationHandler::validateInput($_POST['QueryType']) : '';

// 驗證必要欄位
if (empty($token) || empty($type)) {
    AuthenticationHandler::redirectTo('index.php');
}

// 登入處理
if (AuthenticationHandler::isValidToken($token) && $type === 'Login') {
    // 驗證登入資料
    if (empty($id) || empty($name)) {
        AuthenticationHandler::redirectTo('index.php');
    }
    
    $time = time();
    $sessionToken = AuthenticationHandler::generateSessionToken($token, $time);
    
    // 設定 Session
    $_SESSION['Token'] = $sessionToken;
    $_SESSION['ID'] = $id;
    $_SESSION['Name'] = $name;
    $_SESSION['Time'] = $time;
    
    // 初始化測驗問題Session
    require_once 'config/function_rand_questions.php';
    
    // 重定向到第一個測驗頁面
    AuthenticationHandler::redirectTo('personality_test_01.php');
}
// 查詢處理
elseif (AuthenticationHandler::isValidToken($token) && $type === 'Query') {
    // 驗證查詢資料
    if (empty($id) || empty($date)) {
        AuthenticationHandler::redirectTo('query.php');
    }
    
    // 驗證日期格式
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        AuthenticationHandler::redirectTo('query.php');
    }
    
    $time = time();
    $sessionToken = AuthenticationHandler::generateSessionToken($token, $time);
    
    // 設定查詢 Session
    $_SESSION['Token'] = $sessionToken;
    $_SESSION['ID'] = $id;
    $_SESSION['Date'] = $date;
    $_SESSION['Time'] = $time;
    $_SESSION['QueryType'] = $queryType ?: 'ID';
    
    // 重定向到結果頁面
    AuthenticationHandler::redirectTo('result.php');
}
else {
    // 無效的認證，清除 Session 並重定向
    AuthenticationHandler::clearSession();
    AuthenticationHandler::redirectTo('index.php');
}
?>