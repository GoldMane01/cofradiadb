<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <title>Homepage</title>
    <link rel="stylesheet" href="styles/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>

<?php
require 'config.php';
session_start();
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
$login_error = "";

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare("SELECT id, password FROM user WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
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

<header>
  <a href="index.php">
    <img src="img/logo.png" alt="Logo" style="height: 50px;">
  </a>

  <?php if ($login_error): ?>
    <p class="error" style="color: white; margin: 0 10px;"><?= htmlspecialchars($login_error) ?></p>
  <?php endif; ?>

  <form method="post" action="" class="login-form">
    <input type="email" id="email" name="email" class="form-control rounded-pill" style="width: 200px;" placeholder="Email" required>
    <input type="password" id="password" name="password" class="form-control rounded-pill" style="width: 200px;" placeholder="Contraseña" required>
    <input type="submit" value="Iniciar Sesión" class="login-btn">
  </form>
</header>

<main>

  <div class="image-text-left" style="font-family: 'Playfair Display', serif; font-size: 45px;">
    <div class="text-content-left">
      <p>Hermandad de Nuestro Padre Jesús Nazareno del Paso,</p>
      <p>Nuestra Señora de los Dolores</p>
      <p>y San Antonio Abad</p>
    </div>
  </div>
  <div class="image-text-right">
    <div class="text-content-right">
      
    </div>
  </div>

    <div class="carousel-wrapper">
    <div id="myCarousel" class="carousel slide carrusel" data-ride="carousel">
      <ol class="carousel-indicators">
        <li data-target="#myCarousel" data-slide-to="0" class="active"></li>
        <li data-target="#myCarousel" data-slide-to="1"></li>
        <li data-target="#myCarousel" data-slide-to="2"></li>
        <li data-target="#myCarousel" data-slide-to="3"></li>
        <li data-target="#myCarousel" data-slide-to="4"></li>
        <li data-target="#myCarousel" data-slide-to="5"></li>
        <li data-target="#myCarousel" data-slide-to="6"></li>
      </ol>
    
      <div class="carousel-inner">
        <div class="item active">
          <img src="img/foto1.webp" alt="">
        </div>
    
        <div class="item">
          <img src="img/foto2.webp" alt="">
        </div>
    
        <div class="item">
          <img src="img/foto3.webp" alt="">
        </div>
        <div class="item">
          <img src="img/foto4.webp" alt="">
        </div>
    
        <div class="item">
          <img src="img/foto5.webp" alt="">
        </div>
    
        <div class="item">
          <img src="img/foto6.webp" alt="">
        </div>

        <div class="item">
          <img src="img/foto7.webp" alt="">
        </div>
      </div>
    
      <a class="left carousel-control" href="#myCarousel" data-slide="prev">
        <span class="glyphicon glyphicon-chevron-left"></span>
        <span class="sr-only">Previous</span>
      </a>
      <a class="right carousel-control" href="#myCarousel" data-slide="next">
        <span class="glyphicon glyphicon-chevron-right"></span>
        <span class="sr-only">Next</span>
      </a>
    </div>
    </div>

</main>
        
<footer>
  <div style="padding-right: 40px;">
    <p><strong>Teléfono de contacto:</strong> +34 744 609 275</p>
  </div>
  <div style="padding-right: 40px;">
    <p><strong>Correo:</strong> hdadpasodoloressananton@gmail.com</p>
  </div>
  <div>
    <a href="https://x.com/PasoyDolores?t=320Bf_w9IPWL2mDWgh8Pfg">Twitter</a>
    <a href="https://www.facebook.com/share/15h2g3tcdF/">Facebook</a>
    <a href="https://www.instagram.com/pasoydolores?igsh=czBka2J1eTJiZ2di">Instagram</a>
  </div>
</footer>

</body>
</html>
