<?php
/**
 * logout.php
 *
 * Cierra la sesión del usuario de forma segura.
 */

session_start(); // session_start(): Inicia una nueva sesión o reanuda una existente (necesario para acceder a las variables de sesión).

$_SESSION = array(); // $_SESSION = array(): Asigna un array vacío a la superglobal $_SESSION, limpiando todas las variables de sesión.

session_destroy(); // session_destroy(): Destruye completamente la sesión actual, eliminando los datos de sesión almacenados en el servidor.

header("Location: login.php"); // header("Location: login.php"): Envía una cabecera HTTP para redirigir al usuario a la página de inicio de sesión.
exit(); // exit(): Finaliza la ejecución del script después de la redirección.
?>