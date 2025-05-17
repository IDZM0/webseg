<?php
/**
 * ver_mi_inventario.php
 *
 * Muestra los elementos del inventario asignados al usuario que ha iniciado sesión.
 */

session_start(); // ¡IMPORTANTE! Siempre debe ir al principio

include 'config.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['rol']) || !isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit();
}

$id_usuario_sesion = $_SESSION['id_usuario'];

$inventarios_usuario = [];
$error = "";

try {
    // Consulta para obtener los elementos del inventario asignados al usuario logueado
    $stmt = $conn->prepare("
        SELECT
            i.id_inventario,
            i.marca,
            i.modelo,
            i.serial,
            i.categoria,
            i.estado
        FROM
            inventarios i
        WHERE
            i.id_persona = :id_usuario
        ORDER BY
            i.id_inventario DESC
    ");
    $stmt->bindParam(':id_usuario', $id_usuario_sesion, PDO::PARAM_INT);
    $stmt->execute();
    $inventarios_usuario = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error al obtener el inventario del usuario: " . $e->getMessage());
    $error = "Error al cargar su lista de inventario.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Inventario</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin: 0;
        background-color: #f0f8ff; /* Light blue background */
    }

    .inventario-container {
        padding: 20px;
        display: flex;
        justify-content: center;
    }

    .inventario-table {
        width: 95%; /* Slightly wider */
        max-width: 900px; /* Slightly wider */
        background-color: #fff;
        border-collapse: collapse;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        overflow: hidden;
    }

    .inventario-table th, .inventario-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #a6c8ff; /* Light blue border */
    }

    .inventario-table th {
        background-color: #a6c8ff; /* Light blue header */
        font-weight: bold;
        color: #fff; /* White text */
    }

    .inventario-table tr:nth-child(even) {
        background-color: #e6f0ff; /* Very light blue for even rows */
    }

    .inventario-table tr:hover {
        background-color: #b3d9ff; /* Slightly darker blue on hover */
    }

    h2 {
        text-align: center;
        color: #004080; /* Dark blue heading */
        margin-bottom: 20px;
    }

    .error-message {
        color: #dc3545;
        text-align: center;
        margin-top: 20px;
    }
</style>
</head>
<body>
    <div class="inventario-container">
        <div style="width: 100%; max-width: 800px;">
            <h2>Mi Inventario Asignado</h2>

            <?php if (!empty($error)): ?>
                <p class="error-message"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
            <?php else: ?>
                <table class="inventario-table">
                    <thead>
                        <tr>
                            <th>ID Inventario</th>
                            <th>Marca</th>
                            <th>Modelo</th>
                            <th>Serial</th>
                            <th>Categoría</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($inventarios_usuario)): ?>
                            <?php foreach ($inventarios_usuario as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['id_inventario']) ?></td>
                                    <td><?= htmlspecialchars($item['marca']) ?></td>
                                    <td><?= htmlspecialchars($item['modelo']) ?></td>
                                    <td><?= htmlspecialchars($item['serial']) ?></td>
                                    <td><?= htmlspecialchars($item['categoria']) ?></td>
                                    <td><?= htmlspecialchars($item['estado']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" style="text-align: center;">No tienes elementos asignados en el inventario.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>