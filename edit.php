<?php
session_start();
require 'config.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;


error_reporting(E_ALL);
ini_set('display_errors', 1);

/*$pdox = new PDO("mysql:host=localhost;dbname=cofradia", "root", "root");
$pdox->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);*/

$file = "data2.xlsx"; // Change this to your actual file
$spreadsheet = IOFactory::load($file);
$sheet = $spreadsheet->getActiveSheet();
$data = $sheet->toArray(); // Converts the entire sheet to a PHP array


// Print the data to check


// HTML Table Output



if (isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
}
/*$stmt = $pdo->prepare("SELECT name, surname, dni, birthdate, phone_number, email, address, signed FROM user WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);*/
/*
ORDEN:
0- NOMBRE
1- APELLIDOS
2- NACIMIENTO
3- INSCRIPCIÓN
4- DNI
5--- MIERDA
6--- MIERDA
7- EMAIL
8- TLF
9--- MIERDA
10- DIRECCION
11- CODIGO POSTAL
12- POBLACION
13- PROVINCIA
14--- MIERDA
15- ALTA

*/





echo "<table border='1' cellspacing='0' cellpadding='5'>";
foreach ($newdata as $rowIndex => $row) {
    echo "<tr>";
    $column = 0;
    foreach ($row as $cell) {
            if ($rowIndex === 0) {
                echo "<th>$cell</th>";
            } else {
                echo "<td>$cell</td>";
            }
            $column++;
    }
    echo "</tr>";
}
echo "</table>";

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
	<h1>Editar Perfil</h1>
    <div class="container">
        <div class="column">
		<form action="edit_user.php" method="post">
            <h1>Perfil de Usuario</h1>
            <p><strong>Email:</strong> <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>"></p>
            <p><strong>Teléfono:</strong> <input type="text" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number']); ?>"></p>
            <p><strong>Dirección:</strong> <input type="text" name="address" value="<?php echo htmlspecialchars($user['address']); ?>"></p>
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
            <button type="submit">Actualizar Perfil</button>
		</form>
        </div>
        <div class="column">
            <h1>San Antón</h1>
                <p><strong>Año:</strong> <?php echo htmlspecialchars($anton['year']); ?></p>
                <p><strong>Puesto:</strong> <?php echo htmlspecialchars($anton['role']); ?></p>
                <p><strong>Observaciones:</strong> <?php echo htmlspecialchars($anton['observation']); ?></p>
        </div>
    </div>
</body>
</html>