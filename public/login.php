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
  <title>Login</title>
  <link rel="stylesheet" href="assets/libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/app.css">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body class="page-login">
  <h1>Login</h1>

  <?php if ($error): ?>
    <p style="color: red;"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>

  <form method="post" action="login.php">
    <div>
      <label for="username">Username</label>
      <input id="username" name="username" type="text" required>
    </div>
    <div>
      <label for="password">Passwort</label>
      <input id="password" name="password" type="password" required>
    </div>
    <button type="submit">Anmelden</button>
  </form>
  <script src="assets/libs/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
