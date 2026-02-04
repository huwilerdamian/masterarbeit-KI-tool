<?php
require __DIR__ . '/../init.php';
require __DIR__ . '/../src/tasks.php';
require __DIR__ . '/../src/task_files.php';

session_start();

$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
if ($userId < 1) {
    header('Location: login.php');
    exit;
}

$tasksByGroup = tasks_by_group($userId);

$progressTotal = 0;
$progressDone = 0;
foreach ($tasksByGroup as $groupTasks) {
    foreach ($groupTasks as $task) {
        $type = (int)($task['type'] ?? 0);
        if ($type !== 1 && $type !== 2) {
            continue;
        }
        $progressTotal++;
        if (!empty($task['corrected'])) {
            $progressDone++;
        }
    }
}
$progressPercent = $progressTotal > 0 ? (int)round(($progressDone / $progressTotal) * 100) : 0;
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <title>Tasks</title>
  <link rel="stylesheet" href="assets/libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/app.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/featherlight@1.7.14/release/featherlight.min.css">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/featherlight@1.7.14/release/featherlight.min.js"></script>
</head>
<body class="page-tasks p-4">
  <div class="container bg-white rounded p-4 shadow">
    <div class="tasks-header border-bottom mb-4 mt-4 pb-4 d-flex align-items-center justify-content-between gap-4">
      <div>
        <h1 class="tasks-title">Matheplan 7a</h1>
        <div class="tasks-subtitle">Gleichungen &amp; Ungleichungen</div>
      </div>
      <div class="tasks-progress-circle" style="--progress: <?= $progressPercent ?>;">
        <div class="tasks-progress-inner">
          <div>Du hast <strong id="tasks-progress-done"><?= (int)$progressDone ?></strong></div>
          <div>von <span id="tasks-progress-total"><?= (int)$progressTotal ?></span> Pflichtaufgaben</div>
          <div>erledigt (<span id="tasks-progress-percent"><?= (int)$progressPercent ?></span>%)</div>
        </div>
      </div>
    </div>

    <?php if (!empty($tasksByGroup)): ?>
      <div class="tasks-filters d-flex gap-2 mb-4">
        <button type="button" class="tasks-filter-btn is-active" data-group="all">Alle</button>
        <?php foreach (array_keys($tasksByGroup) as $group): ?>
          <button type="button" class="tasks-filter-btn" data-group="<?= htmlspecialchars((string)$group) ?>">Teil <?= htmlspecialchars((string)$group) ?></button>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="container tasks mb-5">
      <div class="row bg-blue col-12 col-md-4 ps-4 pe-4 border">
        <div class="d-flex align-items-center border-end"><div class="lh-1"><strong>Nicht selbständig lösen</strong><br><small>(wird in Klassen/Gruppen- oder Partnerarbeit gelöst)</small></div></div>
      </div>
      <div class="row bg-green col-12 col-md-4 ps-4 pe-4 border">
        <div class="d-flex align-items-center border-end"><div class="lh-1"><strong>Selbständig lösen</strong><br><small>(Mindestanforderung)</small></div></div>
      </div>
      <div class="row bg-white col-12 col-md-4 ps-4 pe-4 border">
        <div class="d-flex align-items-center border-end"><div class="lh-1"><strong>Selbständig lösen</strong><br><small>(Zusatzmaterial)</small></div></div>
      </div>
      <div class="row bg-grey col-12 col-md-4 ps-4 pe-4 border">
        <div class="d-flex align-items-center border-end"><div class="lh-1"><strong>Nicht Prüfungsrelevant</strong><br><small>(Zusatzmaterial mit erhöhtem Niveau)</small></div></div>
      </div>
    </div>

    <?php if (empty($tasksByGroup)): ?>
      <p>Keine Tasks gefunden.</p>
    <?php else: ?>
      <?php foreach ($tasksByGroup as $group => $groupTasks): ?>
        <div class="container border tasks mb-5" data-group="<?= htmlspecialchars((string)$group) ?>">
          <div class="row bg-orange fw-bold">
              <div class="d-flex align-items-center col-md-9 border-end ps-4 pe-4">Teil <?= htmlspecialchars($group) ?></div>
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
              $exerciseFilePath = '';
              $exerciseIsImage = false;
              $exerciseFile = task_file_for_task_by_type((int)$task['id'], 'exercise');
              if ($exerciseFile) {
                  $exerciseFilePath = (string)($exerciseFile['file_path'] ?? '');
                  if ($exerciseFilePath !== '') {
                      $ext = strtolower(pathinfo($exerciseFilePath, PATHINFO_EXTENSION));
                      $exerciseIsImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
                  }
              }
            ?>
            <div class="row <?= htmlspecialchars(trim($typeClass . ' ' . $bgClass)) ?>" data-task-type="<?= (int)$type ?>" data-task-corrected="<?= !empty($task['corrected']) ? '1' : '0' ?>" data-task-state="<?= !empty($task['state']) ? '1' : '0' ?>">
              <div class="d-flex align-items-center col-md-9 border-end border-top p-2 ps-4 pe-4 gap-2">
                <span><?= htmlspecialchars($task['title']) ?></span>
                <?php if ($exerciseFilePath !== ''): ?>
                  <a class="text-decoration-none" href="<?= htmlspecialchars($exerciseFilePath) ?>" data-featherlightopen="<?= htmlspecialchars($exerciseFilePath) ?>" <?= $exerciseIsImage ? 'data-featherlight="image"' : 'data-featherlight="iframe"' ?> <?= $exerciseIsImage ? '' : 'data-featherlight-iframe-width="100%" data-featherlight-iframe-height="80vh"' ?> aria-label="Aufgabendokument anzeigen">
                    <?php include 'assets/images/icons/eye.svg' ?>
                  </a>
                <?php endif; ?>
              </div>
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
