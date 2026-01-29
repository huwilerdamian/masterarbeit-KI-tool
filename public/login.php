<?php
require __DIR__ . '/../init.php';
require __DIR__ . '/../src/tasks.php';

ensure_task_progress_for_user(1);

header('Location: tasks.php');
exit;
