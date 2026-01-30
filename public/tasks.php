<?php
require __DIR__ . '/../init.php';
require __DIR__ . '/../src/tasks.php';

session_start();

$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
if ($userId < 1) {
    header('Location: login.php');
    exit;
}

$tasks = tasks($userId);
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <title>Tasks</title>
  <link rel="stylesheet" href="assets/libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/app.css">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body class="page-tasks">
  <h1>Alle Tasks</h1>

  <?php if (empty($tasks)): ?>
    <p>Keine Tasks gefunden.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Titel</th>
          <th>gelöst</th>
          <th>korrigiert</th>
          <th>Hilfe?</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($tasks as $task): ?>
        <tr>
          <td><?= htmlspecialchars($task['title']) ?></td>
          <td>
            <button
              type="button"
              class="set-state"
              data-task-id="<?= (int)$task['id'] ?>"
              data-state="<?= htmlspecialchars($task['state']) ?>"
            >
              <?= htmlspecialchars($task['state']) ?>
            </button>
          </td>
          <td>
            <button type="button" class="set-corrected" data-task-id="<?= (int)$task['id'] ?>" data-corrected="<?= $task['corrected'] ? '1' : '0' ?>">
              <?= $task['corrected'] ? 'Ja' : 'Nein' ?>
            </button>
          </td>
          <td><a href="chat.php?id=<?= (int)$task['id'] ?>">Link zum Chat</a></td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>

  <script src="assets/libs/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/tasks.js"></script>
</body>
</html>
