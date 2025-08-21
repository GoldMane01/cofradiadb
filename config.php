<?php
$host = '127.0.0.1';
$dbname = 'cofradia';
$username = 'root';
$password = 'root';

$log_file = __DIR__ . '/error_log.txt';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
	error_log(date('[Y-m-d H:i:s]') . " Database connection error: " . $e->getMessage() . PHP_EOL, 3, $log_file);
    die("Error de conexión con la base de datos.");
}
?>