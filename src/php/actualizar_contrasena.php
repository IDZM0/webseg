<?php
/**
 * actualizar_contrasena.php
 *
 * Página para que los usuarios puedan cambiar su contraseña.
 */

session_start();
include 'config.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'digitador') {
    header("Location: ../index.php"); // Redirigir si no es digitador
    exit();
}

$error = [];
$mensaje = "";

if (!isset($conn)) {
    die("Error: No se pudo conectar a la base de datos.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];
$user_id = $_SESSION['id_usuario']; // Usamos la clave correcta definida en login.php
    $todo_ok = true;

    if (empty($current_password)) {
        $error['current_password'] = "Por favor, ingrese su contraseña actual.";
        $todo_ok = false;
    }

    if (empty($new_password)) {
        $error['new_password'] = "Por favor, ingrese la nueva contraseña.";
        $todo_ok = false;
    } elseif (strlen($new_password) < 8 || !preg_match('/[A-Z]/', $new_password) || !preg_match('/[a-z]/', $new_password) || !preg_match('/[0-9]/', $new_password) || !preg_match('/[^a-zA-Z0-9\s]/', $new_password)) {
        $error['new_password'] = "La nueva contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula, un número y un carácter especial.";
        $todo_ok = false;
    }

    if (empty($confirm_new_password)) {
        $error['confirm_new_password'] = "Por favor, confirme la nueva contraseña.";
        $todo_ok = false;
    } elseif ($new_password !== $confirm_new_password) {
        $error['confirm_new_password'] = "Las nuevas contraseñas no coinciden.";
        $todo_ok = false;
    }

    if ($todo_ok) {
        try {
            $stmt = $conn->prepare("SELECT password FROM usuarios WHERE id = :id");
            $stmt->bindParam(':id', $user_id);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($current_password, $user['password'])) {
                // Verificar que la nueva contraseña no sea igual a la actual
                if (password_verify($new_password, $user['password'])) {
                    $error['new_password'] = "La nueva contraseña no puede ser igual a la contraseña actual.";
                } else {
                    $hashed_new_password = password_hash($new_password, PASSWORD_BCRYPT);
                    $stmt_update = $conn->prepare("UPDATE usuarios SET password = :password WHERE id = :id");
                    $stmt_update->bindParam(':password', $hashed_new_password);
                    $stmt_update->bindParam(':id', $user_id);
                    $stmt_update->execute();
                    $mensaje = "Contraseña cambiada con éxito. Redirigiendo...";
                    // Limpiar los campos de contraseña después del éxito
                    $_POST['current_password'] = '';
                    $_POST['new_password'] = '';
                    $_POST['confirm_new_password'] = '';
                    header("Location:../index.php");
                    exit();
                }
            } else {
                $error['current_password'] = "La contraseña actual es incorrecta.";
            }
        } catch (PDOException $e) {
            error_log("Error al cambiar contraseña: " . $e->getMessage());
            $error['general'] = "Error interno. Intente más tarde.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .error-message {
            color: #ff0019;
            font-size: 0.9em;
            margin-top: 5px;
        }
        .success {
            color: green;
            text-align: center;
            margin-bottom: 20px;
            font-size: 15px;
        }
    </style>
</head>
<body>

    <div class="container login">
        <h2>Cambiar Contraseña</h2>

        <?php if (isset($error['general'])): ?>
            <p class="error"><?= htmlspecialchars($error['general'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <?php if (!empty($mensaje)): ?>
            <p class="success"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <form method="post" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>">
            <label>Contraseña Actual:</label>
            <input type="password" name="current_password" value="<?= htmlspecialchars($_POST['current_password'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            <?php if (isset($error['current_password'])): ?>
                <p class="error-message"><?= htmlspecialchars($error['current_password'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?><br>

            <label>Nueva Contraseña:</label>
            <input type="password" name="new_password" value="<?= htmlspecialchars($_POST['new_password'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            <?php if (isset($error['new_password'])): ?>
                <p class="error-message"><?= htmlspecialchars($error['new_password'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?><br>

            <label>Confirmar Nueva Contraseña:</label>
            <input type="password" name="confirm_new_password" value="<?= htmlspecialchars($_POST['confirm_new_password'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            <?php if (isset($error['confirm_new_password'])): ?>
                <p class="error-message"><?= htmlspecialchars($error['confirm_new_password'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?><br>

            <div class="form-submit">
                <input type="submit" value="Cambiar Contraseña">
            </div>
        </form>
    </div>
</body>
</html>