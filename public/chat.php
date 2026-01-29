<?php
require __DIR__ . '/../init.php';
require __DIR__ . '/../src/tasks.php';
require __DIR__ . '/../src/ai_service.php';
require __DIR__ . '/../src/chat.php';

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

$reply = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message'] ?? '');
    if ($message !== '') {
        try {
            save_chat_message($userId, $taskId, 'user', $message);
            $reply = ai_chat_reply($message);
            if ($reply !== '') {
                save_chat_message($userId, $taskId, 'assistant', $reply);
            }
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    } else {
        $error = 'Bitte eine Nachricht eingeben.';
    }
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
  <?php if ($error): ?>
    <p style="color: red;"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>

  <?php if ($reply): ?>
    <div>
      <strong>Antwort:</strong>
      <pre><?= htmlspecialchars($reply) ?></pre>
    </div>
  <?php endif; ?>

  <form method="post" action="chat.php?id=<?= (int)$taskId ?>">
    <label for="message">Nachricht</label><br>
    <textarea id="message" name="message" rows="4" cols="50" placeholder="Deine Nachricht..."></textarea><br>
    <button type="submit">Senden</button>
  </form>
  <p><a href="tasks.php">Zurück</a></p>
</body>
</html>
