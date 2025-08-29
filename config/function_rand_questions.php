<?php
session_start();

require("mysql_connect.inc.php");

// 函數：從資料庫中隨機選取題目
function getRandomQuestions($link, $type, $limit) {
    $questions = [];
    $sql = "SELECT * FROM analysis_question WHERE QUESTION_TYPE = '$type' ORDER BY RAND() LIMIT $limit";
    $result = $link->query($sql);

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $questions[] = $row;
        }
    }
    return $questions;
}

// 如果會話中沒有問題列表，則獲取並存儲問題
if (!isset($_SESSION['questions'])) {
    $_SESSION['questions'] = [
        'EI' => getRandomQuestions($link, 'EI', 25),
        'SN' => getRandomQuestions($link, 'SN', 26),
        'FT' => getRandomQuestions($link, 'FT', 26),
        'JP' => getRandomQuestions($link, 'JP', 26)
    ];

    $all_questions = array_merge($_SESSION['questions']['EI'], $_SESSION['questions']['SN'], $_SESSION['questions']['FT'], $_SESSION['questions']['JP']);
    shuffle($all_questions);

    // 分配題目到各個頁面
    $_SESSION['page_questions'] = [
        'personality_test_01' => array_slice($all_questions, 0, 21),
        'personality_test_02' => array_slice($all_questions, 21, 21),
        'personality_test_03' => array_slice($all_questions, 42, 21),
        'personality_test_04' => array_slice($all_questions, 63, 20),
        'personality_test_05' => array_slice($all_questions, 83, 20)
    ];
}

$link->close();
?>
