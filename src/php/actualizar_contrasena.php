<?php
/**
 * actualizar_contrasena.php
 *
 * Manejo del cambio de contraseña de forma segura.
 */

session_start();
include 'config.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $user_id = $_SESSION['user_id'];

    // Validaciones
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "Todos los campos son obligatorios.";
    } elseif ($new_password !== $confirm_password) {
        $error = "La nueva contraseña y la confirmación no coinciden.";
    } elseif (strlen($new_password) < 8) {
        $error = "La nueva contraseña debe tener al menos 8 caracteres.";
    } else {
        try {
            // Obtener contraseña actual
            $stmt = $conn->prepare("SELECT password FROM usuarios WHERE id = :id");
            $stmt->bindParam(':id', $user_id);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($current_password, $user['password'])) {
                // Hash de la nueva contraseña
                $new_password_hash = password_hash($new_password, PASSWORD_BCRYPT);

                // Actualizar contraseña
                $update_stmt = $conn->prepare("UPDATE usuarios SET password = :password WHERE id = :id");
                $update_stmt->bindParam(':password', $new_password_hash);
                $update_stmt->bindParam(':id', $user_id);

                if ($update_stmt->execute()) {
                    $success = "Contraseña actualizada correctamente. Serás redirigido al inicio.";
                    header("refresh:2;url=../index.php");
                } else {
                    $error = "Error al actualizar la contraseña.";
                }
            } else {
                $error = "La contraseña actual es incorrecta.";
            }
        } catch (PDOException $e) {
            error_log("Error al cambiar contraseña: " . $e->getMessage());
            $error = "Error interno. Intente más tarde.";
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
</head>
<body>
    <div class="container login">
        <h2>Cambiar Contraseña</h2>

        <?php if (!empty($error)): ?>
            <p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <p class="success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <form method="post" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>">
            <label>Contraseña Actual:</label>
            <input type="password" name="current_password" required><br>

            <label>Nueva Contraseña:</label>
            <input type="password" name="new_password" required minlength="8"><br>

            <label>Confirmar Nueva Contraseña:</label>
            <input type="password" name="confirm_password" required minlength="8"><br>

            <div class="form-submit">
                <input type="submit" value="Guardar Cambios">
            </div>
        </form>

        <div style="margin-top: 20px; text-align: center;">
            <a href="../index.php">Volver al Inicio</a>
        </div>
    </div>
</body>
</html>