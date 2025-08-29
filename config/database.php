<?php
// 資料庫配置文件
// 建議：將此檔案移到網站根目錄之外，或使用環境變數

class DatabaseConfig {
    // 資料庫連線設定
    private static $config = [
        'host' => 'localhost',
        'database' => 'hr_database',
        'username' => 'admin',
        'password' => '1234@5678@90', // 建議：使用環境變數或配置檔案
        'charset' => 'utf8'
    ];
    
    public static function getConnection() {
        $config = self::$config;
        
        // 建立資料庫連線
        $link = @mysqli_connect(
            $config['host'], 
            $config['username'], 
            $config['password'], 
            $config['database']
        );
        
        // 檢查連線
        if (mysqli_connect_errno()) {
            error_log("Database connection failed: " . mysqli_connect_error());
            throw new Exception("資料庫連線失敗，請聯絡系統管理員");
        }
        
        // 設定字符集
        if (!mysqli_set_charset($link, $config['charset'])) {
            error_log("Error setting charset: " . mysqli_error($link));
            mysqli_close($link);
            throw new Exception("資料庫字符集設定失敗");
        }
        
        return $link;
    }
    
    public static function closeConnection($link) {
        if ($link && mysqli_ping($link)) {
            mysqli_close($link);
        }
    }
}

// 為向後相容，保持原有的連線方式
try {
    $link = DatabaseConfig::getConnection();
} catch (Exception $e) {
    // 在生產環境中不應顯示詳細錯誤訊息
    die("系統維護中，請稍後再試");
}
?>