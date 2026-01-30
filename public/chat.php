<?php
require __DIR__ . '/../init.php';
require __DIR__ . '/../src/tasks.php';
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

$messages = chat_messages_for_task($userId, $taskId);
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <title>Task</title>
  <link rel="stylesheet" href="assets/libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="page-chat">
  <h1><?= htmlspecialchars($task['title']) ?></h1>
  <div>
    <h2>Chat-Verlauf</h2>
    <div id="chat-list">
      <?php foreach ($messages as $msg): ?>
        <p>
          <strong><?= htmlspecialchars($msg['role']) ?>:</strong>
          <?= nl2br(htmlspecialchars($msg['content'])) ?>
        </p>
      <?php endforeach; ?>
    </div>
  </div>

  <form id="chat-form">
    <label for="message">Nachricht</label><br>
    <textarea id="message" name="message" rows="4" cols="50" placeholder="Deine Nachricht..."></textarea><br>
    <button type="submit">Senden</button>
  </form>
  <p><a href="tasks.php">Zurück</a></p>

  <script src="assets/libs/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script>
    $(function () {
      const $list = $('#chat-list');
      const $form = $('#chat-form');
      const $message = $('#message');
      const taskId = <?= (int)$taskId ?>;

      $form.on('submit', async function (e) {
        e.preventDefault();
        const text = $message.val().trim();
        if (!text) {
          alert('Bitte eine Nachricht eingeben.');
          return;
        }

        $list.append(`<p><strong>user:</strong> ${$('<div>').text(text).html()}</p>`);
        $message.val('');

        const res = await fetch('chat_message.php', {
          method: 'POST',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify({ task_id: taskId, message: text }),
        });

        const data = await res.json();
        if (!res.ok || !data.ok) {
          alert(data.error || 'Fehler beim Senden.');
          return;
        }

        if (data.reply) {
          $list.append(`<p><strong>assistant:</strong> ${$('<div>').text(data.reply).html()}</p>`);
        }
      });
    });
  </script>
</body>
</html>
