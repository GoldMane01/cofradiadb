<?php
session_start();
require 'config.php';

header('Content-Type: application/json');

error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 0);

$user_id = $_POST['user_id'] ?? null;
$action = $_POST['action'] ?? null;

if (!$action) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos.']);
    exit;
}

try {

    if ($action === 'get_user') {
        $user_id = $_POST['user_id'] ?? 0;
        $stmt = $pdo->prepare("SELECT * FROM user WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            echo json_encode(['success' => true, 'user' => $user]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado.']);
        }
        exit;
    }

    if ($action === 'edit_user') {
        $user_id = $_POST['user_id'] ?? 0;
        $stmt = $pdo->prepare("UPDATE user SET name=?, surname=?, dni=?, birthdate=?, phone_number=?, email=?, address=?, postal_code=? WHERE id=?");
        $stmt->execute([
            $_POST['name'], $_POST['surname'], $_POST['dni'],
            $_POST['bdate'], $_POST['phone'], $_POST['email'], $_POST['address'],
            $_POST['pcode'], $user_id
        ]);
        echo json_encode(['success' => true, 'message' => 'Usuario actualizado correctamente.']);
        exit;
    }


    if ($action === 'add_user') {
        $name      = $_POST['name'] ?? '';
        $surname   = $_POST['surname'] ?? '';
        $dni       = $_POST['dni'] ?? '';
        $inumber   = $_POST['inumber'] ?? '';
        $bdate     = $_POST['bdate'] ?? '';
        $phone     = $_POST['phone'] ?? '';
        $email     = $_POST['email'] ?? '';
        $address   = $_POST['address'] ?? '';
        $pcode     = $_POST['pcode'] ?? '';
        $hashed_pass = password_hash($phone, PASSWORD_DEFAULT);

        if (!$name || !$surname || !$dni || !$inumber || !$bdate || !$phone || !$email || !$address || !$pcode) {
            echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios para crear un usuario.']);
            exit;
        }

        $stmt = $pdo->prepare("
            INSERT INTO user 
                (name, surname, dni, inscription_n, birthdate, phone_number, email, address, postal_code, password) 
            VALUES 
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $surname, $dni, $inumber, $bdate, $phone, $email, $address, $pcode, $hashed_pass]);
        $id = $pdo->lastInsertId();

        $stmt = $pdo->prepare("
            INSERT INTO inscriptions 
                (user_id, dni, inscription_number, date_incorporated) 
            VALUES 
                (?, ?, ?, ?)
        ");
        $date = date('Y-m-d');
        $stmt->execute([$id, $dni, $inumber, $date]);

        echo json_encode(['success' => true, 'message' => 'Usuario añadido correctamente.']);
        exit;
    }

    if ($action === 'delete_user') {
        $id = $_POST['user_id'] ?? '';

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID de usuario no proporcionado.']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM user WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['success' => true, 'message' => 'Usuario eliminado correctamente.']);
        exit;
    }



    if ($action === 'add_sananton_role') {
        $year = $_POST['year'] ?? '';
        $role = $_POST['role'] ?? '';

        if (!$year || !$role) {
            echo json_encode(['success' => false, 'message' => 'Año y rol son obligatorios.']);
            exit;
        }

        // Insert a new San Antón year/role (without observation)
        $stmt = $pdo->prepare("INSERT INTO san_anton (user_id, year, role) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $year, $role]);

        echo json_encode(['success' => true, 'message' => 'Año y rol añadidos correctamente.']);
        exit;
    }

    if ($action === 'add_sananton_observation') {
        $year = $_POST['year'] ?? '';
        $observation = $_POST['observation'] ?? '';

        if (!$year || !$observation) {
            echo json_encode(['success' => false, 'message' => 'Año y observación son obligatorios.']);
            exit;
        }

        // Update observation for a specific year of san_anton and user_id
        $stmt = $pdo->prepare("UPDATE san_anton SET observation = ? WHERE user_id = ? AND year = ?");
        $stmt->execute([$observation, $user_id, $year]);

        // If no rows updated, maybe insert a new row (optional)
        if ($stmt->rowCount() === 0) {
            $stmt = $pdo->prepare("INSERT INTO san_anton (user_id, year, observation) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $year, $observation]);
        }

        echo json_encode(['success' => true, 'message' => 'Observación añadida correctamente.']);
        exit;
    }

    if ($action === 'add_viernes_year') {
        $year = $_POST['year'] ?? '';
        $role = $_POST['role'] ?? '';
        $section = $_POST['section'] ?? '';
        $tunic = $_POST['tunic'] ?? '';
        $cape = $_POST['cape'] ?? '';
        $esclavina = $_POST['esclavina'] ?? '';

        if (!$year || !$role || !$section || !$tunic || !$cape || !$esclavina) {
            echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios para Viernes Dolores.']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO viernes_dolores (user_id, year, role, section, tunic, cape, esclavina) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $year, $role, $section, $tunic, $cape, $esclavina]);

        echo json_encode(['success' => true, 'message' => 'Datos de Viernes Dolores añadidos correctamente.']);
        exit;
    }

	if ($action === 'delete_sananton_year') {
    $year = $_POST['year'] ?? '';

    if (!$year) {
        echo json_encode(['success' => false, 'message' => 'El año es obligatorio para eliminar.']);
        exit;
    }

    // Delete San Antón records for this user and year
    $stmt = $pdo->prepare("DELETE FROM san_anton WHERE user_id = ? AND year = ?");
    $stmt->execute([$user_id, $year]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => "Año $year eliminado correctamente."]);
    } else {
        echo json_encode(['success' => false, 'message' => "No se encontró el año $year para eliminar."]);
    }
    exit;
	}

	if ($action === 'delete_sananton_observe') {
    $year = $_POST['year'] ?? '';

    if (!$year) {
        echo json_encode(['success' => false, 'message' => 'El año es obligatorio para eliminar.']);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE san_anton SET observation = NULL WHERE user_id = ? AND year = ?");
    $stmt->execute([$user_id, $year]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => "Observaciones del año $year eliminadas correctamente."]);
    } else {
        echo json_encode(['success' => false, 'message' => "No se encontró el año $year para eliminar."]);
    }
    exit;
	}

	if ($action === 'delete_viernes') {
    $year = $_POST['year'] ?? '';

    if (!$year) {
        echo json_encode(['success' => false, 'message' => 'El año es obligatorio para eliminar.']);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM viernes_dolores WHERE user_id = ? AND year = ?");
    $stmt->execute([$user_id, $year]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => "Observaciones del año $year eliminadas correctamente."]);
    } else {
        echo json_encode(['success' => false, 'message' => "No se encontró el año $year para eliminar."]);
    }
    exit;
	}

    echo json_encode(['success' => false, 'message' => 'Acción no reconocida.']);
    exit;

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
    exit;
}