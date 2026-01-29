<?php
require __DIR__ . '/../init.php';
require __DIR__ . '/../src/tasks.php';

$userId = 1;
$tasks = tasks($userId);
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <title>Tasks</title>
</head>
<body>
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
          <td><?= $task['state'] ?></td>
          <td><?= $task['corrected'] ? 'Ja' : 'Nein' ?></td>
          <td><a href="/chat.php?id=<?= (int)$task['id'] ?>">Link zum Chat</a></td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>
</body>
</html>
