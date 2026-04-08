<?php
require __DIR__ . '/../init.php';
require __DIR__ . '/../src/tasks.php';
require __DIR__ . '/../src/auth.php';

session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Bitte Username und Passwort eingeben.';
    } else {
        $userId = authenticate_user($username, $password);
        if ($userId === null) {
            $error = 'Ungültige Zugangsdaten.';
        } else {
            $_SESSION['user_id'] = $userId;

            ensure_task_progress_for_user($userId);

            header('Location: tasks.php');
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login</title>
  <link rel="stylesheet" href="assets/libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/app.css">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body class="page-login p-4">
  <div class="container bg-white rounded p-4 shadow">
    <div class="row justify-content-center">
      <div class="col-12 col-md-6 col-lg-4">
        <h1 class="border-bottom mb-4 mt-2">Login</h1>

        <?php if (isset($_GET['registered'])): ?>
          <div class="alert alert-success" role="alert">Registrierung erfolgreich. Bitte jetzt anmelden.</div>
        <?php endif; ?>
        <?php if ($error): ?>
          <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" action="login.php">
          <div class="mb-3">
            <label class="form-label" for="username">Username</label>
            <input class="form-control" id="username" name="username" type="text" required autocomplete="username">
          </div>
          <div class="mb-4">
            <label class="form-label" for="password">Passwort</label>
            <input class="form-control" id="password" name="password" type="password" required autocomplete="current-password">
          </div>
          <button class="btn-login w-100" type="submit">Anmelden</button>
        </form>
        <div class="mt-3 text-center">
          <a href="register.php">Registrieren</a>
        </div>
      </div>
    </div>
  </div>
  <script src="assets/libs/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
