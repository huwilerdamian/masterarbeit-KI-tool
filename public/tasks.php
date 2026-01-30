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
  <div class="container bg-white mt-5 rounded p-4 shadow">
    <h1>Matheplan «7a Gleichungen und Ungleichungen»</h1>

    <?php if (empty($tasks)): ?>
      <p>Keine Tasks gefunden.</p>
    <?php else: ?>
      <div class="container">
        <div class="row">
            <div class="col-md-9">Titel</div>
            <div class="col-md-1">gelöst</div>
            <div class="col-md-1">korrigiert</div>
            <div class="col-md-1">Hilfe?</div>
        </div>
        <?php foreach ($tasks as $task): ?>
          <div class="row">
            <div class="col-md-9"><?= htmlspecialchars($task['title']) ?></div>
            <div class="col-md-1">
              <span type="button"  class="set-corrected <?= $task['corrected'] ? 'true' : 'false' ?>" data-task-id="<?= (int)$task['id'] ?>" data-corrected="<?= $task['corrected'] ? '1' : '0' ?>">
                <?php include 'assets/images/icons/check.svg' ?>
              </span>
            </div>
            <div class="col-md-1">
              <span type="button" class="set-state <?= $task['state'] ? 'true' : 'false' ?>" data-task-id="<?= (int)$task['id'] ?>" data-state="<?= $task['state'] ? '1' : '0' ?>">
                <?php include 'assets/images/icons/check.svg' ?>
              </span>
            </div>
            <div class="col-md-1"><a href="chat.php?id=<?= (int)$task['id'] ?>"><?php include 'assets/images/icons/robot.svg' ?></a></div>
          </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
  </div>
  <script src="assets/libs/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/tasks.js"></script>
</body>
</html>
