<?php
/**
 * config.php
 *
 * Configuración de la conexión a PostgreSQL de forma segura usando variables de entorno.
 */

// Cargar las variables de entorno desde el archivo .env
require __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Obtener las variables de entorno para la conexión a la base de datos
$host = $_ENV['DB_HOST'] ?? 'localhost';
$port = $_ENV['DB_PORT'] ?? '5432';
$dbname = $_ENV['DB_NAME'] ?? 'webseguridad';
$user = $_ENV['DB_USER'] ?? 'postgres';
$password = $_ENV['DB_PASSWORD'] ?? '1212';

try {
    // Crear conexión segura a PostgreSQL con PDO
    $conn = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => true
        ]
    );
} catch (PDOException $e) {
    // Registrar errores en un log y no mostrarlos al usuario
    error_log("Error de conexión: " . $e->getMessage());
    die("Error de conexión. Contacte al administrador.");
}
?>