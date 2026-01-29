<?php
require __DIR__ . '/../init.php';
require __DIR__ . '/../src/tasks.php';

session_start();

$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
if ($userId < 1) {
    header('Location: login.php');
    exit;
}

$taskId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($taskId < 1) {
    header('Location: tasks.php');
    exit;
}

$task = task_by_id($taskId);
if (!$task) {
    header('Location: tasks.php');
    exit;
}
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <title>Task</title>
</head>
<body>
  <h1><?= htmlspecialchars($task['title']) ?></h1>
  <p>Chat kommt hier hin.</p>
  <p><a href="tasks.php">Zurück</a></p>
</body>
</html>
