<?php
/**
 * cambiar_contraseña.php
 *
 * Página para que los usuarios puedan cambiar su contraseña.
 */

session_start();
include 'config.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'digitador') {
    header("Location: ../index.php"); // Redirigir si no es digitador
    exit();
}

$error = "";
$mensaje = "";

if (!isset($conn)) {
    die("Error: No se pudo conectar a la base de datos.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];
    $user_id = $_SESSION['user_id']; // Asumimos que guardaste el user_id en la sesión al loguearse

    if ($new_password !== $confirm_new_password) {
        $error = "Las nuevas contraseñas no coinciden.";
    } else {
        try {
            $stmt = $conn->prepare("SELECT password FROM usuarios WHERE id = :id");
            $stmt->bindParam(':id', $user_id);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($current_password, $user['password'])) {
                $hashed_new_password = password_hash($new_password, PASSWORD_BCRYPT);
                $stmt_update = $conn->prepare("UPDATE usuarios SET password = :password WHERE id = :id");
                $stmt_update->bindParam(':password', $hashed_new_password);
                $stmt_update->bindParam(':id', $user_id);
                $stmt_update->execute();
                $mensaje = "Contraseña cambiada con éxito.";
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
    <div class="back-link">
        <a href="../index.php">Volver</a>
    </div>

    <div class="container login">
        <h2>Cambiar Contraseña</h2>

        <?php if (!empty($error)): ?>
            <p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <?php if (!empty($mensaje)): ?>
            <p class="success"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <form method="post" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>">
            <label>Contraseña Actual:</label>
            <input type="password" name="current_password" required><br>

            <label>Nueva Contraseña:</label>
            <input type="password" name="new_password" required><br>

            <label>Confirmar Nueva Contraseña:</label>
            <input type="password" name="confirm_new_password" required><br>

            <div class="form-submit">
                <input type="submit" value="Cambiar Contraseña">
            </div>
        </form>
    </div>
</body>
</html>