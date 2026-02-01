<?php
require __DIR__ . '/../init.php';
require __DIR__ . '/../src/tasks.php';

session_start();

$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
if ($userId < 1) {
    header('Location: login.php');
    exit;
}

$tasksByGroup = tasks_by_group($userId);
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
<body class="page-tasks p-4">
  <div class="container bg-white rounded p-4 shadow">
    <h1 class="border-bottom mb-5 mt-4">Matheplan «7a Gleichungen und Ungleichungen»</h1>

    <div class="container tasks mb-5">
      <div class="row bg-blue col-12 col-md-4 border">
        <div class="d-flex align-items-center border-end"><div class="lh-1"><strong>Nicht selbständig lösen</strong><br><small>(wird in Klassen/Gruppen- oder Partnerarbeit gelöst)</small></div></div>
      </div>
      <div class="row bg-green col-12 col-md-4 border">
        <div class="d-flex align-items-center border-end"><div class="lh-1"><strong>Selbständig lösen</strong><br><small>(Mindestanforderung)</small></div></div>
      </div>
      <div class="row bg-white col-12 col-md-4 border">
        <div class="d-flex align-items-center border-end"><div class="lh-1"><strong>Selbständig lösen</strong><br><small>(Zusatzmaterial)</small></div></div>
      </div>
      <div class="row bg-grey col-12 col-md-4 border">
        <div class="d-flex align-items-center border-end"><div class="lh-1"><strong>Nicht Prüfungsrelevant</strong><br><small>(Zusatzmaterial mit erhöhtem Niveau)</small></div></div>
      </div>
    </div>

    <?php if (empty($tasksByGroup)): ?>
      <p>Keine Tasks gefunden.</p>
    <?php else: ?>
      <?php foreach ($tasksByGroup as $group => $groupTasks): ?>
        <div class="container border tasks mb-5">
          <div class="row bg-orange fw-bold">
              <div class="d-flex align-items-center col-md-9 border-end">Teil <?= htmlspecialchars($group) ?></div>
              <div class="d-flex align-items-center col-md-1 border-end justify-content-center">Gelöst?</div>
              <div class="d-flex align-items-center col-md-1 border-end justify-content-center">Korrigiert?</div>
              <div class="d-flex align-items-center col-md-1 justify-content-center">Hilfe?</div>
          </div>
          <?php foreach ($groupTasks as $task): ?>
            <?php
              $type = (int)($task['type'] ?? 1);
              $typeClass = 'type-' . $type;
              $bgClass = '';
              if ($type === 1) {
                  $bgClass = 'bg-blue';
              } elseif ($type === 2) {
                  $bgClass = 'bg-green';
              } elseif ($type === 3) {
                  $bgClass = 'bg-white';
              } elseif ($type === 4) {
                  $bgClass = 'bg-grey';
              }
            ?>
            <div class="row <?= htmlspecialchars(trim($typeClass . ' ' . $bgClass)) ?>">
              <div class="d-flex align-items-center col-md-9 border-end border-top p-2"><?= htmlspecialchars($task['title']) ?></div>
              <div class="d-flex align-items-center justify-content-center col-md-1 border-end border-top">
                <span type="button"  class="set-corrected <?= $task['corrected'] ? 'true' : 'false' ?>" data-task-id="<?= (int)$task['id'] ?>" data-corrected="<?= $task['corrected'] ? '1' : '0' ?>">
                  <?php include 'assets/images/icons/check.svg' ?>
                </span>
              </div>
              <div class="d-flex align-items-center justify-content-center col-md-1 border-end border-top">
                <span type="button" class="set-state <?= $task['state'] ? 'true' : 'false' ?>" data-task-id="<?= (int)$task['id'] ?>" data-state="<?= $task['state'] ? '1' : '0' ?>">
                  <?php include 'assets/images/icons/check.svg' ?>
                </span>
              </div>
              <div class="d-flex align-items-center justify-content-center col-md-1 border-top"><a href="chat.php?id=<?= (int)$task['id'] ?>"><?php include 'assets/images/icons/robot.svg' ?></a></div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
  <script src="assets/libs/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/tasks.js"></script>
</body>
</html>
