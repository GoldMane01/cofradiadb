<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //$user_id = $_POST['user_id'];
    $user_id = 1;
	$email = htmlspecialchars($_POST['email']);
    $phone_number = htmlspecialchars($_POST['phone_number']);
    $address = htmlspecialchars($_POST['address']);

    $sql = "UPDATE user SET email = ?, phone_number = ?, address = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute([$email, $phone_number, $address, $user_id]);
        echo "Perfil actualizado correctamente!";
    } catch (PDOException $e) {
        error_log(date('[Y-m-d H:i:s]') . " User edit error: " . $e->getMessage() . PHP_EOL, 3, $log_file);
    }
}
?>