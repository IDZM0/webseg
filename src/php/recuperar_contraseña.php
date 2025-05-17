<?php
/**
 * recuperar_contraseña.php
 *
 * Página para recuperar la contraseña del usuario.
 */

session_start();
include 'config.php';

$error = "";
$mensaje = "";
$mostrar_preguntas = false;
$mostrar_nueva_contrasena = false;
$user_data = null;
$error_nueva_password = [];

if (!isset($conn)) {
    die("Error: No se pudo conectar a la base de datos.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Paso 1: Verificar el correo electrónico
    if (isset($_POST['email_recuperacion'])) {
        $email = filter_var($_POST['email_recuperacion'], FILTER_VALIDATE_EMAIL);
        if ($email) {
            try {
                $stmt = $conn->prepare("SELECT id, pregunta_recuperacion1, respuesta_recuperacion1, pregunta_recuperacion2, respuesta_recuperacion2, pregunta_recuperacion3, respuesta_recuperacion3 FROM usuarios WHERE email = :email");
                $stmt->bindParam(':email', $email);
                $stmt->execute();
                $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user_data) {
                    $_SESSION['recuperacion_user_id'] = $user_data['id'];
                    $mostrar_preguntas = true;
                } else {
                    $error = "No se encontró ningún usuario con ese correo electrónico.";
                }
            } catch (PDOException $e) {
                error_log("Error al buscar email para recuperación: " . $e->getMessage());
                $error = "Error interno. Intente más tarde.";
            }
        } else {
            $error = "Por favor, ingrese un correo electrónico válido.";
        }
    }
    // Paso 2: Verificar las respuestas de seguridad
    elseif (isset($_POST['respuesta1']) && isset($_POST['respuesta2']) && isset($_POST['respuesta3']) && isset($_SESSION['recuperacion_user_id'])) {
        $respuesta1 = trim($_POST['respuesta1']);
        $respuesta2 = trim($_POST['respuesta2']);
        $respuesta3 = trim($_POST['respuesta3']);
        $user_id = $_SESSION['recuperacion_user_id'];

        try {
            $stmt = $conn->prepare("SELECT respuesta_recuperacion1, respuesta_recuperacion2, respuesta_recuperacion3 FROM usuarios WHERE id = :id");
            $stmt->bindParam(':id', $user_id);
            $stmt->execute();
            $respuestas_db = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($respuestas_db &&
                password_verify($respuesta1, $respuestas_db['respuesta_recuperacion1']) &&
                password_verify($respuesta2, $respuestas_db['respuesta_recuperacion2']) &&
                password_verify($respuesta3, $respuestas_db['respuesta_recuperacion3'])
            ) {
                $mostrar_nueva_contrasena = true;
                $mensaje = "Respuestas correctas. Ingrese su nueva contraseña.";
            } else {
                $error = "Las respuestas a las preguntas de seguridad son incorrectas.";
            }
        } catch (PDOException $e) {
            error_log("Error al verificar respuestas de recuperación: " . $e->getMessage());
            $error = "Error interno. Intente más tarde.";
        }
    }
    // Paso 3: Guardar la nueva contraseña
    elseif (isset($_POST['nueva_password']) && isset($_POST['confirmar_nueva_password']) && isset($_SESSION['recuperacion_user_id'])) {
        $nueva_password = $_POST['nueva_password'];
        $confirmar_nueva_password = $_POST['confirmar_nueva_password'];
        $user_id = $_SESSION['recuperacion_user_id'];

        if (empty($nueva_password)) {
            $error_nueva_password['nueva_password'] = "Por favor, ingrese la nueva contraseña.";
        } elseif (strlen($nueva_password) < 8 || !preg_match('/[A-Z]/', $nueva_password) || !preg_match('/[a-z]/', $nueva_password) || !preg_match('/[0-9]/', $nueva_password) || !preg_match('/[^a-zA-Z0-9\s]/', $nueva_password)) {
            $error_nueva_password['nueva_password'] = "La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula, un número y un carácter especial.";
        } elseif ($nueva_password !== $confirmar_nueva_password) {
            $error_nueva_password['confirmar_nueva_password'] = "Las nuevas contraseñas no coinciden.";
        }

        if (empty($error_nueva_password)) {
            $hashed_password = password_hash($nueva_password, PASSWORD_BCRYPT);
            try {
                $stmt = $conn->prepare("UPDATE usuarios SET password = :password WHERE id = :id");
                $stmt->bindParam(':password', $hashed_password);
                $stmt->bindParam(':id', $user_id);

                if ($stmt->execute()) {
                    $mensaje = "Contraseña actualizada con éxito. Será redirigido al login en unos segundos.";
                    unset($_SESSION['recuperacion_user_id']);
                    header("Location: login.php");
                    exit();
                } else {
                    $error = "Error al actualizar la contraseña.";
                    $mostrar_nueva_contrasena = true;
                }
            } catch (PDOException $e) {
                error_log("Error al actualizar contraseña: " . $e->getMessage());
                $error = "Error interno al actualizar la contraseña. Intente más tarde.";
                $mostrar_nueva_contrasena = true;
            }
        } else {
            $mostrar_nueva_contrasena = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .error-message {
            color: #ff0019;
            font-size: 0.9em;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container login">
        <h2>Recuperar Contraseña</h2>

        <?php if (!empty($error)): ?>
            <p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <?php if (!empty($mensaje)): ?>
            <p class="success"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <?php if (!$mostrar_preguntas && !$mostrar_nueva_contrasena): ?>
            <form method="post" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>">
                <label>Ingrese su correo electrónico:</label>
                <input type="email" name="email_recuperacion" required><br>
                <div class="form-submit">
                    <input type="submit" value="Enviar">
                </div>
            </form>
        <?php elseif ($mostrar_preguntas): ?>
            <form method="post" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>">
                <p>Por favor, responda las siguientes preguntas de seguridad:</p>
                <label><?= htmlspecialchars($user_data['pregunta_recuperacion1'], ENT_QUOTES, 'UTF-8') ?>:</label>
                <input type="text" name="respuesta1" required><br>

                <label><?= htmlspecialchars($user_data['pregunta_recuperacion2'], ENT_QUOTES, 'UTF-8') ?>:</label>
                <input type="text" name="respuesta2" required><br>

                <label><?= htmlspecialchars($user_data['pregunta_recuperacion3'], ENT_QUOTES, 'UTF-8') ?>:</label>
                <input type="text" name="respuesta3" required><br>

                <div class="form-submit">
                    <input type="submit" value="Verificar Respuestas">
                </div>
            </form>
        <?php elseif ($mostrar_nueva_contrasena): ?>
            <form method="post" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>">
                <label>Nueva Contraseña:</label>
                <input type="password" name="nueva_password" required minlength="8"><br>
                <?php if (isset($error_nueva_password['nueva_password'])): ?>
                    <p class="error-message"><?= htmlspecialchars($error_nueva_password['nueva_password'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>

                <label>Confirmar Nueva Contraseña:</label>
                <input type="password" name="confirmar_nueva_password" required minlength="8"><br>
                <?php if (isset($error_nueva_password['confirmar_nueva_password'])): ?>
                    <p class="error-message"><?= htmlspecialchars($error_nueva_password['confirmar_nueva_password'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>

                <div class="form-submit">
                    <input type="submit" value="Guardar Nueva Contraseña">
                </div>
            </form>
        <?php endif; ?>

        <div style="margin-top: 20px; text-align: center;">
            <a href="login.php">Volver al Login</a>
            
        </div>
    </div>
</body>

</html>
