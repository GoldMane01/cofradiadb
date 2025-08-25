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
        $stmt = $pdo->prepare("SELECT year, section, tunic, cape, role, esclavina FROM viernes_dolores WHERE user_id = ?");
        $stmt->execute([$user['id']]);
        $user['viernes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($user);

} else {
    $stmt = $pdo->prepare("SELECT id, name, surname, dni, inscription_n, birthdate, phone_number, email, address, postal_code, signed FROM user WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt = $pdo->prepare("SELECT inscription_number, date_incorporated FROM inscriptions WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user['inscription'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $pdo->prepare("SELECT year, observation, role FROM san_anton WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $user['anton'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $pdo->prepare("SELECT year, section, tunic, cape, role, esclavina FROM viernes_dolores WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $user['viernes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        $stmt = $pdo->prepare("SELECT inscription_number, date_incorporated FROM inscriptions WHERE user_id = ?");
        $stmt->execute([$user['id']]);
        $user['inscription'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = $pdo->prepare("SELECT year, observation, role FROM san_anton WHERE user_id = ?");
        $stmt->execute([$user['id']]);
        $user['anton'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = $pdo->prepare("SELECT year, section, tunic, cape, role, esclavina FROM viernes_dolores WHERE user_id = ?");
        $stmt->execute([$user['id']]);
        $user['viernes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        $stmt = $pdo->prepare("SELECT inscription_number, date_incorporated FROM inscriptions WHERE dni = ?");
        $stmt->execute([$user['dni']]);
        $user['inscription'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = $pdo->prepare("SELECT inscription_number, date_incorporated FROM inscriptions WHERE dni = ?");
        $stmt->execute([$user['dni']]);
        $user['inscription'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = $pdo->prepare("SELECT year, observation, role FROM san_anton WHERE user_id = ?");
        $stmt->execute([$user['id']]);
        $user['anton'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = $pdo->prepare("SELECT year, section, tunic, cape, role, esclavina FROM viernes_dolores WHERE user_id = ?");
        $stmt->execute([$user['id']]);
        $user['viernes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Homepage</title>
    <link rel="stylesheet" href="styles/home.css">
</head>
<body>
<header>
  <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 20px;">
  <a href="index.php">
    <img src="img/logo.png" alt="Logo" style="height: 50px;">
  </a>
  
  
  </div>
    
    <?php if ($pass_msg): ?>
      <p class="error" style="color: white; margin: 0 10px;"><?= htmlspecialchars($pass_msg) ?></p>
    <?php endif; ?>
  
    <?php if ($user_id == 0): ?>
    <button style="margin-bottom:0px; margin-top:0px; margin-left: 5px" class="sm-btn add-user">Añadir Usuario</button>
    <form method="post" action="" class="login-form-pass">
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
    <form method="post" action="" class="login-form-pass">
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
                <button class="sm-btn edit-user" data-user-id="<?= $u['id'] ?>">Editar datos</button>
                <button class="sm-btn delete-user" data-user-id="<?= $u['id'] ?>">Eliminar usuario</button>
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
                <div class="btn-container">
                    <button class="sm-btn sananton-role" data-user-id="<?= $u['id'] ?>">Añadir año</button>
                    <button class="sm-btn sananton-delete" data-user-id="<?= $u['id'] ?>">Eliminar año</button>
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
                <div class="btn-container">
                    <button class="sm-btn sananton-observe" data-user-id="<?= $u['id'] ?>">Añadir observación</button>
                    <button class="sm-btn sananton-delete-observe" data-user-id="<?= $u['id'] ?>">Eliminar observación</button>
                </div>
            </div>

            <div class="column">
                <div class="item">
                    <div class="label">Viernes Dolores (Roles)</div>
                    <?php 
                    $count = 0;
                    foreach ($u['viernes'] as $viernes): 
                        $count++;?>
                            <div class="year">
                                <?= $viernes['year'] ?>
                            </div>
                            <div class="two-columns">
                                <div class="value-two-nobordertop" style="flex: 1">
                                    <?= $viernes['role'] ?>
                                </div>
                                <div class="value-two-viernes" style="flex: 1">
                                    <?= $viernes['section'] ?>
                                </div>
                                <div class="value-two-viernes" style="flex: 1">
                                    Túnica: <?= $viernes['tunic'] ?>
                                </div>
                                <div class="value-two-viernes" style="flex: 1">
                                    Capa: <?= $viernes['cape'] ?>
                                </div>
                                <div class="value-two-right-nobordertop" style="flex: 1">
                                    <?= $viernes['esclavina'] ?>
                                </div>
                            </div>
                    <?php endforeach; if ($count == 0) { ?>
                        <div class="value">Ningún año registrado</div>
                    <?php } ?>
                </div>
                <div class="btn-container">
                    <button class="sm-btn viernes-year" data-user-id="<?= $u['id'] ?>">Añadir año</button>
                    <button class="sm-btn viernes-delete" data-user-id="<?= $u['id'] ?>">Eliminar año</button>
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
                <button class="sm-btn edit-user-reduced" data-user-id="<?= $user['id'] ?>">Editar datos</button>
            </div>

            <div class="column">
                <div class="item">
                    <div class="label">San Antón (Roles)</div>
                    <?php 
                    $count = 0;
                    foreach ($user['anton'] as $anton): 
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
                        foreach ($user['anton'] as $anton): 
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
                    <div class="label">Viernes Dolores (Roles)</div>
                    <?php 
                    $count = 0;
                    foreach ($user['viernes'] as $viernes): 
                        $count++;?>
                            <div class="year">
                                <?= $viernes['year'] ?>
                            </div>
                            <div class="two-columns">
                                <div class="value-two-nobordertop" style="flex: 1">
                                    <?= $viernes['role'] ?>
                                </div>
                                <div class="value-two-viernes" style="flex: 1">
                                    <?= $viernes['section'] ?>
                                </div>
                                <div class="value-two-viernes" style="flex: 1">
                                    Túnica: <?= $viernes['tunic'] ?>
                                </div>
                                <div class="value-two-viernes" style="flex: 1">
                                    Capa: <?= $viernes['cape'] ?>
                                </div>
                                <div class="value-two-right-nobordertop" style="flex: 1">
                                    <?= $viernes['esclavina'] ?>
                                </div>
                            </div>
                    <?php endforeach; if ($count == 0) { ?>
                        <div class="value">Ningún año registrado</div>
                    <?php } ?>
                </div>
            </div>
<?php } ?>


<script>
$(document).ready(function () {
  initHandlers();

  function initHandlers() {
    // San Antón Role
    $('.sananton-role').click(async function () {
      const userId = $(this).data('user-id');

      const { value: year } = await Swal.fire({
        title: 'Introduce el año para San Antón:',
        input: 'text',
        inputPlaceholder: 'Año...',
        showCancelButton: true
      });
      if (!year) return Swal.fire('Error', 'El año es obligatorio.', 'error');

      const { value: role } = await Swal.fire({
        title: 'Introduce el rol:',
        input: 'text',
        inputPlaceholder: 'Rol...',
        showCancelButton: true
      });
      if (!role) return Swal.fire('Error', 'El rol es obligatorio.', 'error');

      $.post('update_data.php', {
        action: 'add_sananton_role', user_id: userId, year, role
      }, function (response) {
        Swal.fire(response.success ? 'Éxito' : 'Error', response.message, response.success ? 'success' : 'error');
        if (response.success) location.reload();
      }, 'json');
    });

    // San Antón Delete
    $('.sananton-delete').click(async function () {
      const userId = $(this).data('user-id');

      const { value: year } = await Swal.fire({
        title: 'Introduce el año que deseas eliminar:',
        input: 'text',
        inputPlaceholder: 'Año...',
        showCancelButton: true
      });
      if (!year) return Swal.fire('Cancelado', 'Operación cancelada o año inválido.', 'info');

      $.post('update_data.php', {
        user_id: userId,
        action: 'delete_sananton_year',
        year: year.trim()
      }, function (response) {
        Swal.fire(response.success ? 'Éxito' : 'Error', response.message, response.success ? 'success' : 'error');
        if (response.success) location.reload();
      }, 'json');
    });

    // San Antón Observation
    $('.sananton-observe').click(async function () {
      const userId = $(this).data('user-id');

      const { value: year } = await Swal.fire({
        title: 'Introduce el año para la observación:',
        input: 'text',
        inputPlaceholder: 'Año...',
        showCancelButton: true
      });
      if (!year) return Swal.fire('Error', 'El año es obligatorio.', 'error');

      const { value: observation } = await Swal.fire({
        title: 'Introduce la observación:',
        input: 'textarea',
        inputPlaceholder: 'Observación...',
        showCancelButton: true
      });
      if (!observation) return Swal.fire('Error', 'Observación es obligatoria.', 'error');

      $.post('update_data.php', {
        action: 'add_sananton_observation', user_id: userId, year, observation
      }, function (response) {
        Swal.fire(response.success ? 'Éxito' : 'Error', response.message, response.success ? 'success' : 'error');
        if (response.success) location.reload();
      }, 'json');
    });

    // San Antón Delete Observation
    $('.sananton-delete-observe').click(async function () {
      const userId = $(this).data('user-id');

    const { value: year } = await Swal.fire({
      title: 'Introduce el año de la observación que deseas eliminar:',
      input: 'text',
      inputPlaceholder: 'Año...',
      showCancelButton: true,
      customClass: {
        title: 'swal-custom-title',
        input: 'swal-custom-input'
      }
    });

      if (!year) return Swal.fire('Cancelado', 'Operación cancelada o año inválido.', 'info');

      $.post('update_data.php', {
        user_id: userId,
        action: 'delete_sananton_observe',
        year: year.trim()
      }, function (response) {
        Swal.fire(response.success ? 'Éxito' : 'Error', response.message, response.success ? 'success' : 'error');
        if (response.success) location.reload();
      }, 'json');
    });

    //Add User
    $('.add-user').click(async function () {
      const { value: values } = await Swal.fire({
        title: 'Añadir Usuario',
        html:
          '<label style="margin-top: 5px; display:block; color:black; font-weight:bold; font-size:18px">Nombre</label>' +
          '<input style="margin-top: 0px;" id="swal-name" class="swal2-input" placeholder="Nombre">' +
          '<label style="margin-top: 5px; display:block; color:black; font-weight:bold; font-size:18px">Apellidos</label>' +
          '<input style="margin-top: 0px;" id="swal-surname" class="swal2-input" placeholder="Apellidos">' +
          '<label style="margin-top: 5px; display:block; color:black; font-weight:bold; font-size:18px">DNI</label>' +
          '<input style="margin-top: 0px;" id="swal-dni" class="swal2-input" placeholder="DNI">' +
          '<label style="margin-top: 5px; display:block; color:black; font-weight:bold; font-size:18px">Nº Inscripción</label>' +
          '<input style="margin-top: 0px;" id="swal-inumber" class="swal2-input" placeholder="Nº Inscripción">' +
          '<label style="margin-top: 5px; display:block; color:black; font-weight:bold; font-size:18px">Nacimiento</label>' +
          '<input style="margin-top: 0px;" type="date" id="swal-bdate" class="swal2-input">' +
          '<label style="margin-top: 5px; display:block; color:black; font-weight:bold; font-size:18px">Teléfono</label>' +
          '<input style="margin-top: 0px;" id="swal-phone" class="swal2-input" placeholder="Teléfono">' +
          '<label style="margin-top: 5px; display:block; color:black; font-weight:bold; font-size:18px">Email</label>' +
          '<input style="margin-top: 0px;" type="email" id="swal-email" class="swal2-input" placeholder="Email">' +
          '<label style="margin-top: 5px; display:block; color:black; font-weight:bold; font-size:18px">Dirección</label>' +
          '<input style="margin-top: 0px;" id="swal-address" class="swal2-input" placeholder="Dirección">' +
          '<label style="margin-top: 5px; display:block; color:black; font-weight:bold; font-size:18px">Código Postal</label>' +
          '<input style="margin-top: 0px;" id="swal-pcode" class="swal2-input" placeholder="Código Postal">',

        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
          return {
            name: document.getElementById('swal-name').value,
            surname: document.getElementById('swal-surname').value,
            dni: document.getElementById('swal-dni').value,
            inumber: document.getElementById('swal-inumber').value,
            bdate: document.getElementById('swal-bdate').value,
            phone: document.getElementById('swal-phone').value,
            email: document.getElementById('swal-email').value,
            address: document.getElementById('swal-address').value,
            pcode: document.getElementById('swal-pcode').value
          };
        }
      });

      if (!values) return;
      if (Object.values(values).some(v => !v)) {
        Swal.fire('Error', 'Todos los campos son obligatorios.', 'error');
        return;
      }

      $.post('update_data.php', {
        action: 'add_user',
        ...values
      }, function (response) {
        Swal.fire(response.success ? 'Éxito' : 'Error', response.message, response.success ? 'success' : 'error');
        if (response.success) location.reload();
      }, 'json');
    });

    $('.delete-user').click(async function () {
      const userId = $(this).data('user-id');

      const result = await Swal.fire({
        title: '¿Eliminar usuario?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Eliminar',
        cancelButtonText: 'Cancelar'
      });

      if (!result.isConfirmed) return;

      $.post('update_data.php', {
        action: 'delete_user',
        user_id: userId
      }, function (response) {
        Swal.fire(
          response.success ? 'Éxito' : 'Error',
          response.message,
          response.success ? 'success' : 'error'
        );
        if (response.success) location.reload();
      }, 'json');
    });


    $('.edit-user').click(async function () {
      const userId = $(this).data('user-id');

      const response = await $.post('update_data.php', {
        action: 'get_user',
        user_id: userId
      }, null, 'json');

      if (!response.success) {
        Swal.fire('Error', 'No se pudo obtener la información del usuario.', 'error');
        return;
      }

      const user = response.user;

      const { value: values } = await Swal.fire({
        title: 'Editar Usuario',
        html:
          '<label style="margin-top: 5px; display:block; color:black; font-weight:bold; font-size:18px">Nombre</label>' +
          `<input style="margin-top: 0px;" id="swal-name" class="swal2-input" value="${user.name}" placeholder="Nombre">` +
          '<label style="margin-top: 5px; display:block; color:black; font-weight:bold; font-size:18px">Apellidos</label>' +
          `<input style="margin-top: 0px;" id="swal-surname" class="swal2-input" value="${user.surname}" placeholder="Apellidos">` +
          '<label style="margin-top: 5px; display:block; color:black; font-weight:bold; font-size:18px">DNI</label>' +
          `<input style="margin-top: 0px;" id="swal-dni" class="swal2-input" value="${user.dni}" placeholder="DNI">` +
          '<label style="margin-top: 5px; display:block; color:black; font-weight:bold; font-size:18px">Nacimiento</label>' +
          `<input style="margin-top: 0px;" type="date" id="swal-bdate" class="swal2-input" value="${user.birthdate}">` +
          '<label style="margin-top: 5px; display:block; color:black; font-weight:bold; font-size:18px">Teléfono</label>' +
          `<input style="margin-top: 0px;" id="swal-phone" class="swal2-input" value="${user.phone_number}" placeholder="Teléfono">` +
          '<label style="margin-top: 5px; display:block; color:black; font-weight:bold; font-size:18px">Email</label>' +
          `<input style="margin-top: 0px;" type="email" id="swal-email" class="swal2-input" value="${user.email}" placeholder="Email">` +
          '<label style="margin-top: 5px; display:block; color:black; font-weight:bold; font-size:18px">Dirección</label>' +
          `<input style="margin-top: 0px;" id="swal-address" class="swal2-input" value="${user.address}" placeholder="Dirección">` +
          '<label style="margin-top: 5px; display:block; color:black; font-weight:bold; font-size:18px">Código Postal</label>' +
          `<input style="margin-top: 0px;" id="swal-pcode" class="swal2-input" value="${user.postal_code}" placeholder="Código Postal">`,

        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
          return {
            user_id: userId,
            name: document.getElementById('swal-name').value,
            surname: document.getElementById('swal-surname').value,
            dni: document.getElementById('swal-dni').value,
            bdate: document.getElementById('swal-bdate').value,
            phone: document.getElementById('swal-phone').value,
            email: document.getElementById('swal-email').value,
            address: document.getElementById('swal-address').value,
            pcode: document.getElementById('swal-pcode').value
          };
        }
      });

      if (!values) return;
      if (Object.values(values).some(v => !v)) {
        Swal.fire('Error', 'Todos los campos son obligatorios.', 'error');
        return;
      }

      $.post('update_data.php', {
        action: 'edit_user',
        ...values
      }, function (res) {
        Swal.fire(res.success ? 'Éxito' : 'Error', res.message, res.success ? 'success' : 'error');
        if (res.success) location.reload();
      }, 'json');
    });

    $('.edit-user-reduced').click(async function () {
      const userId = $(this).data('user-id');

      const response = await $.post('update_data.php', {
        action: 'get_user',
        user_id: userId
      }, null, 'json');

      if (!response.success) {
        Swal.fire('Error', 'No se pudo obtener la información del usuario.', 'error');
        return;
      }

      const user = response.user;

      const { value: values } = await Swal.fire({
        title: 'Editar Usuario',
        html:
          '<label style="margin-top: 5px; display:block; color:black; font-weight:bold; font-size:18px">Teléfono</label>' +
          `<input style="margin-top: 0px;" id="swal-phone" class="swal2-input" value="${user.phone_number}" placeholder="Teléfono">` +
          '<label style="margin-top: 5px; display:block; color:black; font-weight:bold; font-size:18px">Email</label>' +
          `<input style="margin-top: 0px;" type="email" id="swal-email" class="swal2-input" value="${user.email}" placeholder="Email">` +
          '<label style="margin-top: 5px; display:block; color:black; font-weight:bold; font-size:18px">Dirección</label>' +
          `<input style="margin-top: 0px;" id="swal-address" class="swal2-input" value="${user.address}" placeholder="Dirección">` +
          '<label style="margin-top: 5px; display:block; color:black; font-weight:bold; font-size:18px">Código Postal</label>' +
          `<input style="margin-top: 0px;" id="swal-pcode" class="swal2-input" value="${user.postal_code}" placeholder="Código Postal">`,

        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
          return {
            user_id: userId,
            phone: document.getElementById('swal-phone').value,
            email: document.getElementById('swal-email').value,
            address: document.getElementById('swal-address').value,
            pcode: document.getElementById('swal-pcode').value
          };
        }
      });

      if (!values) return;
      if (Object.values(values).some(v => !v)) {
        Swal.fire('Error', 'Todos los campos son obligatorios.', 'error');
        return;
      }

      $.post('update_data.php', {
        action: 'edit_user_reduced',
        ...values
      }, function (res) {
        Swal.fire(res.success ? 'Éxito' : 'Error', res.message, res.success ? 'success' : 'error');
        if (res.success) location.reload();
      }, 'json');
    });


    // Viernes Dolores Add
    $('.viernes-year').click(async function () {
      const userId = $(this).data('user-id');

      const { value: values } = await Swal.fire({
        title: 'Añadir Viernes Dolores',
        html:
          '<label style="margin-top: 5px; display:block; color:black; font-weight:bold; font-size:18px">Año</label>' +
          '<input style="margin-top: 0px;" id="swal-year" class="swal2-input" placeholder="Año">' +
          '<label style="margin-top: 5px; display:block; color:black; font-weight:bold; font-size:18px">Rol</label>' +
          '<input style="margin-top: 0px;" id="swal-role" class="swal2-input" placeholder="Rol">' +
          '<label style="margin-top: 5px; display:block; color:black; font-weight:bold; font-size:18px">Sección</label>' +
          '<input style="margin-top: 0px;" id="swal-section" class="swal2-input" placeholder="Sección">' +
          '<label style="margin-top: 5px; display:block; color:black; font-weight:bold; font-size:18px">Túnica</label>' +
          '<input style="margin-top: 0px;" id="swal-tunic" class="swal2-input" placeholder="Túnica">' +
          '<label style="margin-top: 5px; display:block; color:black; font-weight:bold; font-size:18px">Capa</label>' +
          '<input style="margin-top: 0px;" id="swal-cape" class="swal2-input" placeholder="Capa">' +
          '<label style="margin-top: 5px; display:block; color:black; font-weight:bold; font-size:18px">Esclavina</label>' +
          '<input style="margin-top: 0px;" id="swal-esclavina" class="swal2-input" placeholder="Esclavina">',

        focusConfirm: false,
        showCancelButton: true,
        preConfirm: () => {
          return {
            year: document.getElementById('swal-year').value,
            role: document.getElementById('swal-role').value,
            section: document.getElementById('swal-section').value,
            tunic: document.getElementById('swal-tunic').value,
            cape: document.getElementById('swal-cape').value,
            esclavina: document.getElementById('swal-esclavina').value
          };
        }
      });

      if (!values) return;
      if (Object.values(values).some(v => !v)) {
        Swal.fire('Error', 'Todos los campos son obligatorios.', 'error');
        return;
      }

      $.post('update_data.php', {
        action: 'add_viernes_year',
        user_id: userId,
        ...values
      }, function (response) {
        Swal.fire(response.success ? 'Éxito' : 'Error', response.message, response.success ? 'success' : 'error');
        if (response.success) location.reload();
      }, 'json');
    });

    // Viernes Dolores Delete
    $('.viernes-delete').click(async function () {
      const userId = $(this).data('user-id');

      const { value: year } = await Swal.fire({
        title: 'Introduce el año que deseas eliminar:',
        input: 'text',
        inputPlaceholder: 'Año...',
        showCancelButton: true
      });
      if (!year) return Swal.fire('Cancelado', 'Operación cancelada o año inválido.', 'info');

      $.post('update_data.php', {
        user_id: userId,
        action: 'delete_viernes',
        year: year.trim()
      }, function (response) {
        Swal.fire(response.success ? 'Éxito' : 'Error', response.message, response.success ? 'success' : 'error');
        if (response.success) location.reload();
      }, 'json');
    });
  }
});

</script>

</body>
</html>
