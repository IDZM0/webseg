<?php
/**
 * index.php
 *
 * Página principal del usuario autenticado.
 * Verifica la sesión y muestra opciones según el rol del usuario.
 */

session_start();
session_regenerate_id(true);

if (!isset($_SESSION['rol'])) {
    header("Location: php/login.php");
    exit();
}

$rol = htmlspecialchars($_SESSION['rol'], ENT_QUOTES, 'UTF-8');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1
    0">
    <title>Bienvenido</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .button-container {
            text-align: center;
            margin-top: 20px;
        }

        .button {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 14px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 18px;
            font-weight: 600;
            text-decoration: none;
            margin: 0 10px;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .button:hover {
            background-color: #0056b3;
            transform: scale(1.02);
        }
    </style>
</head>
<body>
    <div class="welcome-container">
        <h2>Bienvenido</h2>

        <?php if ($rol === "superusuario"): ?>
            <p id="welcome-message">¡Bienvenido, Super Usuario!</p>
            <div class="button-container">
                <a href="php/reg_personas.php" class="button">Registrar Miembro</a>
                <a href="php/logout.php" class="button">Cerrar Sesión</a>
            </div>
        <?php elseif ($rol === "digitador"): ?>
            <p id="welcome-message">¡Bienvenido, Digitador!</p>
            <div class="button-container">
                <a href="php/actualizar_contrasena.php" class="button">Cambiar Contraseña</a>
                <a href="php/logout.php" class="button">Cerrar Sesión</a>
            </div>
        <?php else: ?>
            <p id="welcome-message">¡Bienvenido, Usuario!</p>
            <div class="button-container">
                <a href="php/logout.php" class="button">Cerrar Sesión</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>