<?php
/**
 * login.php
 *
 * Manejo de autenticación y cierre de sesión de forma segura.
 */

session_start(); // Asegúrate de iniciar la sesión al principio

include 'config.php';

$error = "";
$intentos_maximos = 5;
$bloqueo_tiempo = 60;

if (!isset($conn)) {
    die("Error: No se pudo conectar a la base de datos.");
}

// Manejo de cierre de sesión
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Verificación del formulario de login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login_identifier = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS); // Usamos 'username' como identificador genérico
    $password = $_POST['password'];

    // Inicialización de intentos fallidos
    if (!isset($_SESSION['intentos'])) {
        $_SESSION['intentos'] = 0;
        $_SESSION['ultimo_intento'] = time();
    }

    // Bloqueo temporal si se superan los intentos fallidos
    if ($_SESSION['intentos'] >= $intentos_maximos && (time() - $_SESSION['ultimo_intento']) < $bloqueo_tiempo) {
        $error = "Demasiados intentos fallidos. Intente nuevamente en 1 minuto.";
    } else {
        try {
            // Buscar al usuario por username o email
            $stmt = $conn->prepare("SELECT id, password, rol FROM usuarios WHERE username = :identifier OR email = :identifier");
            $stmt->bindValue(':identifier', $login_identifier);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verificación de las credenciales
            if ($user && password_verify($password, $user['password'])) {
                // Almacenar en sesión el ID y rol del usuario
                $_SESSION['id_usuario'] = $user['id']; // Guardamos el id del usuario en la sesión
                $_SESSION['rol'] = $user['rol']; // Guardamos el rol del usuario

                $_SESSION['intentos'] = 0; // Restablecer los intentos fallidos

                // Redirigir al usuario al dashboard o página principal
                header("Location: ../index.php");
                exit();
            } else {
                $_SESSION['intentos']++;
                $_SESSION['ultimo_intento'] = time();
                $error = "Usuario o contraseña incorrectos.";
            }
        } catch (PDOException $e) {
            error_log("Error en autenticación: " . $e->getMessage());
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
    <title>Login</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container login">
        <h2>Login</h2>

        <?php if (!empty($error)): ?>
            <p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <form method="post" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>">
            <label>Usuario o Correo:</label>
            <input type="text" name="username" required><br>

            <label>Contraseña:</label>
            <input type="password" name="password" required><br>

            <div class="form-submit">
                <input type="submit" value="Acceder">
            </div>
        </form>

        <div class="password-reset">
            <a href="recuperar_contraseña.php">¿Olvidaste tu contraseña?</a>
        </div>
    </div>
</body>
</html>
