<?php
/**
 * reg_personas.php
 *
 * Página de registro de nuevos usuarios con seguridad mejorada y preguntas de recuperación.
 */

include 'config.php';

$error = "";
$errores = []; // Array para almacenar los errores individuales

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

        // Validación de la contraseña
        if (strlen($password) < 8) {
            $errores['password_length'] = "La contraseña debe tener al menos 8 caracteres.";
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errores['password_lower'] = "La contraseña debe contener al menos una minúscula.";
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errores['password_upper'] = "La contraseña debe contener al menos una mayúscula.";
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errores['password_number'] = "La contraseña debe contener al menos un número.";
        }
        if (!preg_match('/[^a-zA-Z0-9\s]/', $password)) {
            $errores['password_special'] = "La contraseña debe contener al menos un carácter especial.";
        }

        if (!empty($errores)) {
            $error = "Por favor, corrige los siguientes errores en la contraseña:";
        } elseif (empty($confirm_password)) {
            $error = "Por favor ingresa una contraseña y confirma.";
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

        .error-password {
            color: red;
            font-size: 0.8em;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container registro">
        <h2>Registro de Personas</h2>

        <?php if (!empty($error) && empty($errores)): ?>
            <p class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php elseif (!empty($errores)): ?>
            <p class="error">Por favor, corrige los siguientes errores:</p>
            <?php if (isset($errores['password_length'])): ?>
                <p class="error-password"><?php echo htmlspecialchars($errores['password_length'], ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>
            <?php if (isset($errores['password_lower'])): ?>
                <p class="error-password"><?php echo htmlspecialchars($errores['password_lower'], ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>
            <?php if (isset($errores['password_upper'])): ?>
                <p class="error-password"><?php echo htmlspecialchars($errores['password_upper'], ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>
            <?php if (isset($errores['password_number'])): ?>
                <p class="error-password"><?php echo htmlspecialchars($errores['password_number'], ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>
            <?php if (isset($errores['password_special'])): ?>
                <p class="error-password"><?php echo htmlspecialchars($errores['password_special'], ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>
            <?php if (!empty($error) && !isset($errores['password_length']) && !isset($errores['password_lower']) && !isset($errores['password_upper']) && !isset($errores['password_number']) && !isset($errores['password_special'])): ?>
                <p class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>
        <?php endif; ?>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-row">
                <div class="form-column">
                    <label>Nombres:</label>
                    <input type="text" name="nombres" value="<?php echo isset($_POST['nombres']) ? htmlspecialchars($_POST['nombres'], ENT_QUOTES, 'UTF-8') : ''; ?>" required><br>

                    <label>Apellidos:</label>
                    <input type="text" name="apellidos" value="<?php echo isset($_POST['apellidos']) ? htmlspecialchars($_POST['apellidos'], ENT_QUOTES, 'UTF-8') : ''; ?>" required><br>

                    <label>Email:</label>
                    <input type="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8') : ''; ?>" required><br>

                    <label>Username:</label>
                    <input type="text" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8') : ''; ?>" required><br>

                    <label>Contraseña:</label>
                    <input type="password" name="password" required><br>

                    <label>Confirmar Contraseña:</label>
                    <input type="password" name="confirm_password" required><br>
                </div>

                <div class="form-column">
                    <label>Tipo de Documento:</label>
                    <select name="tipo_documento" required>
                        <option value="">---</option>
                        <option value="cedula" <?php if (isset($_POST['tipo_documento']) && $_POST['tipo_documento'] == 'cedula') echo 'selected'; ?>>Cédula de Ciudadanía</option>
                        <option value="tarjeta_identidad" <?php if (isset($_POST['tipo_documento']) && $_POST['tipo_documento'] == 'tarjeta_identidad') echo 'selected'; ?>>Tarjeta de Identidad</option>
                        <option value="cedula_extranjeria" <?php if (isset($_POST['tipo_documento']) && $_POST['tipo_documento'] == 'cedula_extranjeria') echo 'selected'; ?>>Cédula Extranjera</option>
                    </select><br>

                    <label>Número de Documento:</label>
                    <input type="text" name="num_documento" value="<?php echo isset($_POST['num_documento']) ? htmlspecialchars($_POST['num_documento'], ENT_QUOTES, 'UTF-8') : ''; ?>" required><br>

                    <label>Teléfono:</label>
                    <input type="text" name="telefono" value="<?php echo isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono'], ENT_QUOTES, 'UTF-8') : ''; ?>" required><br>

                    <label>Dirección:</label>
                    <input type="text" name="direccion" value="<?php echo isset($_POST['direccion']) ? htmlspecialchars($_POST['direccion'], ENT_QUOTES, 'UTF-8') : ''; ?>" required><br>

                    <label>Rol:</label>
                    <select name="rol" required>
                        <option value="">---</option>
                        <option value="superusuario" <?php if (isset($_POST['rol']) && $_POST['rol'] == 'superusuario') echo 'selected'; ?>>Super Usuario</option>
                        <option value="digitador" <?php if (isset($_POST['rol']) && $_POST['rol'] == 'digitador') echo 'selected'; ?>>Digitador</option>
                    </select><br>
                </div>
            </div>

            <div class="preguntas-container">
                <h3 style="color: #10019c;">Preguntas de Recuperación de Contraseña</h3>
                <label>Nombre de un Familiar Querido:</label>
                <input type="text" name="respuesta1" value="<?php echo isset($_POST['respuesta1']) ? htmlspecialchars($_POST['respuesta1'], ENT_QUOTES, 'UTF-8') : ''; ?>" required class="pregunta-input"><br>

                <label>Nombre de tu Mejor Amigo:</label>
                <input type="text" name="respuesta2" value="<?php echo isset($_POST['respuesta2']) ? htmlspecialchars($_POST['respuesta2'], ENT_QUOTES, 'UTF-8') : ''; ?>" required class="pregunta-input"><br>

                <label>Nombre de tu Primera Mascota:</label>
                <input type="text" name="respuesta3" value="<?php echo isset($_POST['respuesta3']) ? htmlspecialchars($_POST['respuesta3'], ENT_QUOTES, 'UTF-8') : ''; ?>" required class="pregunta-input"><br>
            </div>

            <div class="form-submit">
                <input type="submit" value="Registrar">
            </div>
        </form>
    </div>
</body>
</html>