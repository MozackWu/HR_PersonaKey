<?php
session_start();
$page = 'personality_test_04';
$page_questions = $_SESSION['page_questions'][$page] ?? [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach ($_POST as $question_id => $answer) {
        $_SESSION['answers'][$question_id] = $answer;
    }
}
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>Personality Test Page 4</title>
</head>
<body>
    <form action="personality_test_05.php" method="post">
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