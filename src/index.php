<?php
/**
 * index.php
 *
 * Página principal del usuario autenticado con barra de navegación.
 */

session_start();
session_regenerate_id(true);

if (!isset($_SESSION['rol'])) {
    header("Location: php/login.php");
    exit();
}

$rol = htmlspecialchars($_SESSION['rol'], ENT_QUOTES, 'UTF-8');
$username = htmlspecialchars($_SESSION['username'] ?? 'Usuario', ENT_QUOTES, 'UTF-8'); // Asumiendo que tienes el username en sesión
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0; /* Aseguramos que no haya márgenes predeterminados en el body */
        }

        .navbar {
            background-color: rgb(0, 120, 249); /* Azul principal */
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
            /* Eliminamos el borde inferior: */
            /* border-bottom: 1px solid #dee2e6; */
        }

        .navbar-left {
            display: flex;
            align-items: center;
        }

        .navbar-right {
            display: flex;
            align-items: center;
        }

        .navbar a {
            margin-left: 15px;
            margin-right: 15px;
            text-decoration: none;
            color:rgb(27, 40, 100); /* Azul más oscuro para los enlaces */
            font-weight: bold;
        }

        .navbar a:hover {
            color: rgb(0, 0, 0); /* Blanco al pasar el ratón */
        }

        .welcome-message {
            margin-right: 20px;
            font-weight: bold;
            color: white;
        }

        .container {
            /* Reducimos el margen superior: */
            margin-top: 20px;
            padding: 20px; /* Añadimos un poco de espacio interno al contenedor principal */
            background-color:rgb(255, 255, 255); /* Puedes ajustar el color de fondo del contenido si lo deseas */
            border-radius: 8px; /* Opcional: para que el contenido no se vea tan "pegado" */
        }

        h2 {
            color: #10019c; /* Mantenemos el color del título */
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="navbar-left">
            <span class="welcome-message">
                <?php if ($rol === "superusuario"): ?>
                    ¡Bienvenido, Super Usuario!
                <?php elseif ($rol === "digitador"): ?>
                    ¡Bienvenido, Digitador!
                <?php else: ?>
                    ¡Bienvenido!
                <?php endif; ?>
            </span>
        </div>
        <div class="navbar-right">
            <?php if ($rol === "superusuario"): ?>
                <a href="php/ver_mi_inventario.php">Ver Inventario</a>
                <a href="php/reg_personas.php">Registrar Persona</a>
                <a href="php/logout.php">Cerrar Sesión</a>
            <?php elseif ($rol === "digitador"): ?>
                <a href="php/registrar_inventario.php">Registrar Inventario</a>
                <a href="php/ver_inventario_completo.php">Ver Inventario</a>
                <a href="php/actualizar_contrasena.php">Cambiar Contraseña</a>
                <a href="php/logout.php">Cerrar Sesión</a>
            <?php else: ?>
                <a href="php/logout.php">Cerrar Sesión</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="container">
        <h2>Contenido Principal</h2>
        <p>Este es el contenido principal de la página.</p>
        <?php
        // Aquí podrías mostrar contenido específico según el rol si fuera necesario
        ?>
    </div>
</body>
</html>