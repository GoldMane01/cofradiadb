<?php
require 'config.php';

//error_reporting(E_ALL);
//ini_set('display_errors', 1);
$login_error = "";

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare("SELECT password FROM user WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            header('Location: home.php');
        } else {
			$login_error = "Error: contraseña o correo equivocados";
        }
    }

} catch (PDOException $e) {
    echo 'Database error: ' . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>

<header>
    <a href="index.html" style="margin-right: 5px;">
      <img src="path/to/logo.png" alt="Logo" style="height: 40px;">
    </a>
    <a href="login.php">Inicio de Sesión</a>
</header>
<h2 style="text-align: center;">Login</h2>

<?php if ($login_error): ?>
    <p class="error"><?= htmlspecialchars($login_error) ?></p>
<?php endif; ?>

<form method="post" action="">
    <label for="email">Email:</label><br>
    <input type="email" id="email" name="email" required><br>

    <label for="password">Password:</label><br>
    <input type="password" id="password" name="password" required><br>

    <input type="submit" value="Inicio Sesión">
</form>

</body>
</html>