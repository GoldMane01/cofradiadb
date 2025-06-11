<?php
session_start();
require 'config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$user_id = $_SESSION['user_id'];

if ($user_id == 0) {
    $stmt = $pdo->prepare("SELECT id, name, surname, dni, inscription_n, birthdate, phone_number, email, address, postal_code, signed FROM user");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($users as &$user) {
        $stmt = $pdo->prepare("SELECT inscription_number, date_incorporated FROM inscriptions WHERE user_id = ?");
        $stmt->execute([$user['id']]);
        $user['inscription'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = $pdo->prepare("SELECT year, observation, role FROM san_anton WHERE user_id = ?");
        $stmt->execute([$user['id']]);
        $user['anton'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($user);

} else {
    $stmt = $pdo->prepare("SELECT name, surname, dni, inscription_n, birthdate, phone_number, email, address, postal_code, signed FROM user WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt = $pdo->prepare("SELECT inscription_number, date_incorporated FROM inscriptions WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user['inscription'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/*$stmt = $pdo->prepare("SELECT year, observation, role FROM san_anton WHERE user_id = ?");
$stmt->execute([$user_id]);
$anton = $stmt->fetch(PDO::FETCH_ASSOC);
$stmt = $pdo->prepare("SELECT section, tunic, cape, role FROM viernes_dolores WHERE user_id = ?");
$stmt->execute([$user_id]);
$viernes = $stmt->fetch(PDO::FETCH_ASSOC);*/

$pass_msg = "";

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $opass = $_POST['opassword'] ?? '';
        $npass = $_POST['npassword'] ?? '';

        $stmt = $pdo->prepare("SELECT password FROM user WHERE id = ?");
        $stmt->execute([$user_id]);
        $new_pass = $stmt->fetch();

        if (!password_verify($opass, $new_pass['password'])) {
            $pass_msg = "Error: La contraseña antigua es incorrecta";
        }
        else {
            $hashed_new_pass = password_hash($npass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE user SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_new_pass, $user_id]);
            $pass_msg = "La contraseña se ha actualizado";
        }
    }

} catch (PDOException $e) {
    echo 'Database error: ' . $e->getMessage();
    exit;
}

$search_i = $_GET['search_i'] ?? '';
$search_n = $_GET['search_n'] ?? '';

if (!empty($search_i)) {
    $stmt = $pdo->prepare("SELECT id, name, surname, dni, inscription_n, birthdate, phone_number, email, address, postal_code, signed FROM user");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($users as &$user) {
        $stmt = $pdo->prepare("SELECT inscription_number, date_incorporated FROM inscriptions WHERE user_id = ?");
        $stmt->execute([$user['id']]);
        $user['inscription'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($user);
    
    if (!empty($search_i)) {
        $users = array_filter($users, function ($user) use ($search_i) {
            return !empty(array_filter($user['inscription'], function ($ins) use ($search_i) {
                    return strpos((string)$ins['inscription_number'], $search_i) !== false;
                }));
        });
    }
}

if (!empty($search_n)) {
    $stmt = $pdo->prepare("SELECT id, name, surname, dni, inscription_n, birthdate, phone_number, email, address, postal_code, signed FROM user");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($users as &$user) {
        $stmt = $pdo->prepare("SELECT inscription_number, date_incorporated FROM inscriptions WHERE user_id = ?");
        $stmt->execute([$user['id']]);
        $user['inscription'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($user);
    
    if (!empty($search_n)) {
        $users = array_filter($users, function ($user) use ($search_n) {
            $full_name = $user['name'] . ' ' . $user['surname'];
            return stripos($full_name, $search_n) !== false;
        });
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <title>Homepage</title>
    <link rel="stylesheet" href="styles/home.css">
</head>
<body>
<header>
  <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 20px;">
  <a href="index.php">
    <img src="img/logo.png" alt="Logo" style="height: 50px;">
  </a>
  
  <a href="add_user.php" class="login-btn">Añadir Usuario</a>
  </div>
    
    <?php if ($pass_msg): ?>
      <p class="error" style="color: white; margin: 0 10px;"><?= htmlspecialchars($pass_msg) ?></p>
    <?php endif; ?>
  
    <?php if ($user_id == 0): ?>
    <form method="post" action="" class="login-form">
      <input type="password" id="opassword" name="opassword" class="form-control rounded-pill" style="width: 200px;" placeholder="Contraseña actual" required>
      <input type="password" id="npassword" name="npassword" class="form-control rounded-pill" style="width: 200px;" placeholder="Contraseña nueva" required>
      <input type="submit" value="Cambiar Contraseña" class="login-btn">
    </form>
  
    <form method="get" action="" class="login-form">
          <input type="text" name="search_i" class="form-control rounded-pill" placeholder="Buscar número..." style="padding: 6px;">
          <button type="submit" class="login-btn">Buscar</button>
          <input type="text" name="search_n" class="form-control rounded-pill" placeholder="Buscar nombre..." style="padding: 6px;">
          <button type="submit" class="login-btn">Buscar</button>
    </form>
  <?php endif; ?>
  <?php if ($user_id != 0): ?>
    <form method="post" action="" class="login-form">
      <input type="password" id="opassword" name="opassword" class="form-control rounded-pill" style="width: 200px;" placeholder="Contraseña actual" required>
      <input type="password" id="npassword" name="npassword" class="form-control rounded-pill" style="width: 200px;" placeholder="Contraseña nueva" required>
      <input type="submit" value="Cambiar Contraseña" class="login-btn">
    </form>
  <?php endif; ?>

</header>

<?php
$count = 0;
if ($user_id == 0) {
    foreach ($users as $u):
        if ($u['id'] == 0) {
            continue;
        }
    $count++;
?>
<div style="display: flex; justify-content: center; align-items: center; padding: 40px;">
    <div class="box">
        <div class="inscription">Número de inscripción:
            <?php foreach ($u['inscription'] as $inscription): ?>
                 <?= $inscription['inscription_number'] ?> (<?= $inscription['date_incorporated'] ?>);
            <?php endforeach; ?></div>
        <div class="three-columns">
            <div class="column">
                <div class="item">
                    <div class="label">Nombre</div>
                    <div class="value"><?= $u['name'] ?></div>
                </div>
                <div class="item">
                    <div class="label">Apellidos</div>
                    <div class="value"><?= $u['surname'] ?></div>
                </div>
                <div class="item">
                    <div class="label">Correo</div>
                    <div class="value"><?= $u['email'] ?></div>
                </div>
                <div class="item">
                    <div class="label">Dirección</div>
                    <div class="value"><?= $u['address'] ?></div>
                </div>
                <div class="item">
                    <div class="label">Código Postal</div>
                    <div class="value"><?= $u['postal_code'] ?></div>
                </div>
                <div class="item">
                    <div class="label">Fecha de Nacimiento</div>
                    <div class="value"><?= $u['birthdate'] ?></div>
                </div>
                <div class="item">
                    <div class="label">Teléfono</div>
                    <div class="value"><?= $u['phone_number'] ?></div>
                </div>
                <div class="item">
                    <div class="label">DNI</div>
                    <div class="value"><?= $u['dni'] ?></div>
                </div>
            </div>

            <div class="column">
                <div class="item">
                    <div class="label">San Antón (Roles)</div>
                    <?php 
                    $count = 0;
                    foreach ($u['anton'] as $anton): 
                        $count++;?>
                        <div class="two-columns">
                            <div class="value-two">
                                    <?= $anton['year'] ?>
                                </div>
                                <div class="value-two-right">
                                    <?= $anton['role'] ?>
                                </div>
                            </div>
                    <?php endforeach; if ($count == 0) { ?>
                        <div class="value">Ningún año registrado</div>
                    <?php } ?>
                </div>
                <div class="item">
                    <div class="label">San Antón (Observaciones)</div>
                    <?php 
                        $count = 0;
                        foreach ($u['anton'] as $anton): 
                        if (!empty($anton['observation'])) {
                            $count++; ?>
                        <div class="two-columns">
                            <div class="value-two">
                                    <?= $anton['year'] ?>
                                </div>
                                <div class="value-two-right">
                                    <?= $anton['observation'] ?>
                                </div>
                            </div>
                    <?php } endforeach; if ($count == 0) { ?>
                            <div class="value">Sin observaciones</div>
                    <?php   } ?>
                </div>
            </div>

            <div class="column">
                <div class="item">
                    <div class="label">Rol</div>
                    <div class="value">Administrador</div>
                </div>
                <div class="item">
                    <div class="label">Rol</div>
                    <div class="value">Usuario</div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endforeach; } else { ?>

<div style="display: flex; justify-content: center; align-items: center; padding: 40px;">
    <div class="box">
        <div class="inscription">Número de inscripción:
            <?php foreach ($user['inscription'] as $inscription): ?>
                 <?= $inscription['inscription_number'] ?> (<?= $inscription['date_incorporated'] ?>);
            <?php endforeach; ?></div>
    <div class="three-columns">
        <div class="column">
        <div class="item">
            <div class="label">Nombre</div>
            <div class="value"><?= $user['name'] ?></div>
        </div>
        <div class="item">
            <div class="label">Apellidos</div>
            <div class="value"><?= $user['surname'] ?></div>
        </div>
        <div class="item">
            <div class="label">Correo</div>
            <div class="value"><?= $user['email'] ?></div>
        </div>
        <div class="item">
            <div class="label">Dirección</div>
            <div class="value"><?= $user['address'] ?></div>
        </div>
        <div class="item">
            <div class="label">Código Postal</div>
            <div class="value"><?= $user['postal_code'] ?></div>
        </div>
        <div class="item">
            <div class="label">Fecha de Nacimiento</div>
            <div class="value"><?= $user['birthdate'] ?></div>
        </div>
        <div class="item">
            <div class="label">Teléfono</div>
            <div class="value"><?= $user['phone_number'] ?></div>
        </div>
        <div class="item">
            <div class="label">DNI</div>
            <div class="value"><?= $user['dni'] ?></div>
        </div>
        </div>

        <div class="column">
        <div class="item">
            <div class="label">Correo</div>
            <div class="value"><?= $user['email'] ?></div>
        </div>
        <div class="item">
            <div class="label">Correo</div>
            <div class="value">carlos@example.com</div>
        </div>
        </div>

        <div class="column">
        <div class="item">
            <div class="label">Rol</div>
            <div class="value">Administrador</div>
        </div>
        <div class="item">
            <div class="label">Rol</div>
            <div class="value">Usuario</div>
        </div>
        </div>
    </div>
    </div>
</div>
<?php } ?>

</body>
</html>
