<?php
session_start();

// 保存使用者的回答
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach ($_POST as $question_id => $answer) {
        $_SESSION['answers'][$question_id] = $answer;
    }
}

// 資料庫連接信息
require_once 'config/mysql_connect.inc.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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

// 計算結果
foreach ($_SESSION['answers'] as $question_id => $answer) {
    $sql = "SELECT * FROM analysis_question WHERE UID = $question_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $question = $result->fetch_assoc();
        if ($answer == 'A') {
            $results[$question['RESULT_A']]++;
        } else {
            $results[$question['RESULT_B']]++;
        }
    }
}

// 假設有 user_id 可用
$user_id = $_SESSION['user_id'];

// 寫入結果到資料庫
$sql = "INSERT INTO results (user_id, E, I, S, N, F, T, J, P) VALUES ($user_id, {$results['E']}, {$results['I']}, {$results['S']}, {$results['N']}, {$results['F']}, {$results['T']}, {$results['J']}, {$results['P']})";
$conn->query($sql);

// 關閉資料庫連接
$conn->close();

// 顯示結果
echo '<h1>測驗結果</h1>';
foreach ($results as $type => $count) {
    echo '<p>' . $type . ': ' . $count . '</p>';
}

// 清除會話數據
session_destroy();
?>