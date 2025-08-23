<?php

require_once 'config/function_rand_questions.php';
require_once 'config/mysql_connect.inc.php';

$page = 'personality_test_01';
$page_questions = $_SESSION['page_questions'][$page] ?? [];
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>Personality Test Page 1</title>
</head>
<body>
    <form action="personality_test_02.php" method="post">
        <?php foreach ($page_questions as $question): ?>
            <div>
                <p><?= $question['QUESTION'] ?></p>
                <label>
                    <input type="radio" name="question_<?= $question['UID'] ?>" value="A" required> <?= $question['ANSWER_A'] ?>
                </label>
                <label>
                    <input type="radio" name="question_<?= $question['UID'] ?>" value="B"> <?= $question['ANSWER_B'] ?>
                </label>
            </div>
        <?php endforeach; ?>
        <button type="submit">Next</button>
    </form>
</body>
</html>