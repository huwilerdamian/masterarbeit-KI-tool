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

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script>
    $(function () {
      $(document).on('click', '.set-corrected', async function () {
        const $btn = $(this);
        const taskId = $btn.data('task-id');
        const current = $btn.data('corrected') === 1;
        const next = !current;

        const res = await fetch('update_corrected.php', {
          method: 'POST',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify({ task_id: taskId, corrected: next }),
        });

        if (!res.ok) {
          alert('Fehler beim Speichern.');
          return;
        }

        const data = await res.json();
        if (!data.ok) {
          alert(data.error || 'Fehler beim Speichern.');
          return;
        }

        $btn.data('corrected', data.corrected ? 1 : 0);
        $btn.text(data.corrected ? 'Ja' : 'Nein');
      });

      $(document).on('click', '.set-state', async function () {
        const $btn = $(this);
        const taskId = $btn.data('task-id');
        const current = $btn.data('state');
        const next = current === 'open' ? 'in_progress' : current === 'in_progress' ? 'done': 'open';

        const res = await fetch('update_state.php', {
          method: 'POST',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify({ task_id: taskId, state: next }),
        });

        if (!res.ok) {
          alert('Fehler beim Speichern.');
          return;
        }

        const data = await res.json();
        if (!data.ok) {
          alert(data.error || 'Fehler beim Speichern.');
          return;
        }

        $btn.data('state', data.state);
        $btn.text(data.state);
      });
    });
  </script>
</body>
</html>
