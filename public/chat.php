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
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body class="page-chat p-4">
  <div class="container bg-white rounded p-4 shadow">
    <h1 class="border-bottom mt-4">Hilfe bei <?= htmlspecialchars($task['title']) ?></h1>
    <p class="text-end"><a href="tasks.php">Zurück</a></p>

    <div class="mb-4">
      <div id="chat-list" class="chat-list d-flex flex-column gap-3">
        <?php foreach ($messages as $msg): ?>
          <?php $isUser = ($msg['role'] ?? '') === 'user'; ?>
          <?php
            $filePath = $msg['file_path'] ?? '';
            $fileName = $msg['file_name'] ?? '';
            $isImage = false;
            if ($filePath !== '') {
                $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
            }
          ?>
          <div class="chat-message d-flex <?= $isUser ? 'justify-content-end' : 'justify-content-start' ?>">
            <div class="chat-bubble <?= $isUser ? 'chat-user' : 'chat-assistant' ?>">
              <div class="chat-content"><?= nl2br(htmlspecialchars($msg['content'])) ?></div>
              <?php if ($filePath !== '' && $isImage): ?>
                <div class="chat-attachment mt-2">
                  <a href="<?= htmlspecialchars($filePath) ?>" target="_blank" rel="noopener">
                    <img class="chat-attachment-image" src="<?= htmlspecialchars($filePath) ?>" alt="<?= htmlspecialchars($fileName ?: 'Anhang') ?>">
                  </a>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div id="file-selected" class="chat-file-selected small text-muted mt-2" aria-live="polite"></div>
    <div id="file-preview" class="chat-file-preview mt-2" aria-live="polite"></div>
    <form id="chat-form" class="mb-3">
      <div class="chat-input">
        <button id="attach-btn" class="chat-icon-btn" type="button" aria-label="Datei hinzufügen">
          <?php include 'assets/images/icons/plus.svg' ?>
        </button>
        <input id="attach-input" type="file" name="file" accept="image/*" capture hidden>
        <textarea id="message" name="message" class="chat-textarea" rows="1" placeholder="Stelle irgendeine Frage"></textarea>
        <button class="chat-send-btn" type="submit" aria-label="Senden">
          <?php include 'assets/images/icons/arrow_right.svg' ?>
        </button>
      </div>
    </form>
  </div>

  <script>
    window.__TASK_ID__ = <?= (int)$taskId ?>;
  </script>
  <script src="assets/libs/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/chat.js"></script>
</body>
</html>
