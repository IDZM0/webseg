<?php
/**
 * reg_personas.php
 *
 * Página de registro de nuevos usuarios con seguridad mejorada y preguntas de recuperación.
 */

include 'config.php';

$error = "";

try {
    if (!isset($conn)) {
        throw new Exception("Error: No se pudo conectar a la base de datos.");
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $nombres = trim($_POST['nombres']);
        $apellidos = trim($_POST['apellidos']);
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) ? $_POST['email'] : "";
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $tipo_documento = trim($_POST['tipo_documento']);
        $num_documento = preg_match('/^\d+$/', $_POST['num_documento']) ? $_POST['num_documento'] : "";
        $telefono = preg_match('/^\d+$/', $_POST['telefono']) ? $_POST['telefono'] : "";
        $direccion = trim($_POST['direccion']);
        $rol = trim($_POST['rol']);
        $respuesta1 = trim($_POST['respuesta1']);
        $respuesta2 = trim($_POST['respuesta2']);
        $respuesta3 = trim($_POST['respuesta3']);
        $pregunta1 = "Nombre de un Familiar Querido";
        $pregunta2 = "Nombre de tu Mejor Amigo";
        $pregunta3 = "Nombre de tu Primera Mascota";

        // Validación de contraseñas con caracteres especiales
        if (empty($password) || empty($confirm_password)) {
            $error = "Por favor ingresa una contraseña y confirma.";
        } elseif (!preg_match("/[A-Za-z0-9@#$%^&+=!]/", $password)) {
            $error = "La contraseña debe contener al menos un carácter especial (@, #, $, %, etc.).";
        } elseif ($password !== $confirm_password) {
            $error = "Las contraseñas no coinciden.";
        } elseif (empty($rol) || empty($tipo_documento)) {
            $error = "Por favor, selecciona un rol y un tipo de documento.";
        } elseif (!$email) {
            $error = "Correo electrónico no válido.";
        } elseif (empty($num_documento)) {
            $error = "Número de documento inválido.";
        } elseif (empty($telefono)) {
            $error = "Número de teléfono inválido.";
        } elseif (empty($respuesta1) || empty($respuesta2) || empty($respuesta3)) {
            $error = "Por favor, responde todas las preguntas de seguridad.";
        } else {
            $stmt = $conn->prepare("SELECT username, email, num_documento FROM usuarios WHERE username = :username OR email = :email OR num_documento = :num_documento");
            $stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':num_documento' => $num_documento
            ]);
            $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingUser) {
                if ($existingUser['username'] === $username) {
                    $error = "El nombre de usuario ya existe.";
                } elseif ($existingUser['email'] === $email) {
                    $error = "El correo electrónico ya está registrado.";
                } elseif ($existingUser['num_documento'] === $num_documento) {
                    $error = "El número de documento ya está registrado.";
                }
            } else {
                // Hashed password to store securely
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                $stmt = $conn->prepare("INSERT INTO usuarios (nombres, apellidos, email, username, password, tipo_documento, num_documento, telefono, direccion, rol, pregunta_recuperacion1, respuesta_recuperacion1, pregunta_recuperacion2, respuesta_recuperacion2, pregunta_recuperacion3, respuesta_recuperacion3) VALUES (:nombres, :apellidos, :email, :username, :password, :tipo_documento, :num_documento, :telefono, :direccion, :rol, :pregunta1, :respuesta1, :pregunta2, :respuesta2, :pregunta3, :respuesta3)");
                $stmt->execute([
                    ':nombres' => $nombres,
                    ':apellidos' => $apellidos,
                    ':email' => $email,
                    ':username' => $username,
                    ':password' => $hashed_password,
                    ':tipo_documento' => $tipo_documento,
                    ':num_documento' => $num_documento,
                    ':telefono' => $telefono,
                    ':direccion' => $direccion,
                    ':rol' => $rol,
                    ':pregunta1' => htmlspecialchars($pregunta1, ENT_QUOTES, 'UTF-8'),
                    ':respuesta1' => htmlspecialchars($respuesta1, ENT_QUOTES, 'UTF-8'),
                    ':pregunta2' => htmlspecialchars($pregunta2, ENT_QUOTES, 'UTF-8'),
                    ':respuesta2' => htmlspecialchars($respuesta2, ENT_QUOTES, 'UTF-8'),
                    ':pregunta3' => htmlspecialchars($pregunta3, ENT_QUOTES, 'UTF-8'),
                    ':respuesta3' => htmlspecialchars($respuesta3, ENT_QUOTES, 'UTF-8')
                ]);

                header("Location: login.php");
                exit();
            }
        }
    }
} catch (Exception $e) {
    error_log("Error en registro: " . $e->getMessage());
    $error = "Ocurrió un error al procesar el registro.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Personas</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .preguntas-container {
            text-align: center;
            margin-top: 30px;
        }

        .pregunta-input {
            width: 80%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            max-width: 300px; /* Limitar el ancho si es necesario */
            margin-left: auto;
            margin-right: auto;
        }
    </style>
</head>
<body>
    <div class="container registro">
        <h2>Registro de Personas</h2>

        <?php if (!empty($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-row">
                <div class="form-column">
                    <label>Nombres:</label>
                    <input type="text" name="nombres" required><br>

                    <label>Apellidos:</label>
                    <input type="text" name="apellidos" required><br>

                    <label>Email:</label>
                    <input type="email" name="email" required><br>

                    <label>Username:</label>
                    <input type="text" name="username" required><br>

                    <label>Contraseña:</label>
                    <input type="password" name="password" required><br>

                    <label>Confirmar Contraseña:</label>
                    <input type="password" name="confirm_password" required><br>
                </div>

                <div class="form-column">
                    <label>Tipo de Documento:</label>
                    <select name="tipo_documento" required>
                        <option value="">---</option>
                        <option value="cedula">Cédula de Ciudadanía</option>
                        <option value="tarjeta_identidad">Tarjeta de Identidad</option>
                        <option value="cedula_extranjeria">Cédula Extranjera</option>
                    </select><br>

                    <label>Número de Documento:</label>
                    <input type="text" name="num_documento" required><br>

                    <label>Teléfono:</label>
                    <input type="text" name="telefono" required><br>

                    <label>Dirección:</label>
                    <input type="text" name="direccion" required><br>

                    <label>Rol:</label>
                    <select name="rol" required>
                        <option value="">---</option>
                        <option value="superusuario">Super Usuario</option>
                        <option value="digitador">Digitador</option>
                    </select><br>
                </div>
            </div>

            <div class="preguntas-container">
                <h3 style="color: #10019c;">Preguntas de Recuperación de Contraseña</h3>
                <label>Nombre de un Familiar Querido:</label>
                <input type="text" name="respuesta1" required class="pregunta-input"><br>

                <label>Nombre de tu Mejor Amigo:</label>
                <input type="text" name="respuesta2" required class="pregunta-input"><br>

                <label>Nombre de tu Primera Mascota:</label>
                <input type="text" name="respuesta3" required class="pregunta-input"><br>
            </div>

            <div class="form-submit">
                <input type="submit" value="Registrar">
            </div>
        </form>
    </div>
</body>
</html>