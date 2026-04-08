<?php
require __DIR__ . '/../init.php';
require __DIR__ . '/../src/tasks.php';

session_start();

$error = '';
$registrationDate = getenv('REGISTRATION_DATE');
$registrationTz = getenv('REGISTRATION_TZ') ?: 'Europe/Zurich';
$registrationOpen = false;
if (is_string($registrationDate) && $registrationDate !== '') {
    try {
        $tz = new DateTimeZone($registrationTz);
        $today = (new DateTime('now', $tz))->format('Y-m-d');
        $registrationOpen = ($today === $registrationDate);
    } catch (Throwable $e) {
        $registrationOpen = false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$registrationOpen) {
        $error = 'Registrierung ist heute nicht möglich.';
    } else {
    $username = trim($_POST['username'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    $passwordConfirm = (string)($_POST['password_confirm'] ?? '');

    if ($username === '' || $password === '' || $passwordConfirm === '') {
        $error = 'Bitte alle Felder ausfüllen.';
    } elseif (mb_strlen($password) < 6) {
        $error = 'Passwort muss mindestens 6 Zeichen haben.';
    } elseif ($password !== $passwordConfirm) {
        $error = 'Passwörter stimmen nicht überein.';
    } else {
        $existing = db_query(
            'SELECT id FROM users WHERE username = :username LIMIT 1',
            ['username' => $username]
        );
        if (!empty($existing)) {
            $error = 'Username ist bereits vergeben.';
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            db_execute(
                'INSERT INTO users (username, password_hash) VALUES (:username, :password_hash)',
                [
                    'username' => $username,
                    'password_hash' => $passwordHash,
                ]
            );

            $userId = (int)db_last_insert_id();

            ensure_task_progress_for_user($userId);

            header('Location: login.php?registered=1');
            exit;
        }
    }
    }
}
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registrierung</title>
  <link rel="stylesheet" href="assets/libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/app.css">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body class="page-login p-4">
  <div class="container bg-white rounded p-4 shadow">
    <div class="row justify-content-center">
      <div class="col-12 col-md-6 col-lg-4">
        <h1 class="border-bottom mb-4 mt-2">Registrierung</h1>

        <?php if ($error): ?>
          <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($registrationOpen): ?>
        <form method="post" action="register.php">
          <div class="mb-3">
            <label class="form-label" for="username">Username</label>
            <input class="form-control" id="username" name="username" type="text" required autocomplete="username">
          </div>
          <div class="mb-3">
            <label class="form-label" for="password">Passwort</label>
            <input class="form-control" id="password" name="password" type="password" required autocomplete="new-password">
          </div>
          <div class="mb-4">
            <label class="form-label" for="password_confirm">Passwort wiederholen</label>
            <input class="form-control" id="password_confirm" name="password_confirm" type="password" required autocomplete="new-password">
          </div>
          <button class="btn-login w-100" type="submit">Registrieren</button>
        </form>
        <?php else: ?>
          <div class="alert alert-warning" role="alert">Registrierung ist heute nicht möglich.</div>
        <?php endif; ?>
        <div class="mt-3 text-center">
          <a href="login.php">Zum Login</a>
        </div>
      </div>
    </div>
  </div>
  <script src="assets/libs/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
