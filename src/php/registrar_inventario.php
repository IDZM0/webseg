<?php
/**
 * registrar_inventario.php
 *
 * Página para que los digitadores registren elementos del inventario,
 * ya sea manualmente o mediante la carga de un archivo CSV.
 */

session_start();
include 'config.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'digitador') {
    header("Location: ../index.php");
    exit();
}

$mensaje = "";
$error = "";
$errores_csv = [];
$errores_manual = []; // Inicializar el array de errores manuales

// Obtener la lista de usuarios para el formulario manual
try {
    $stmt_usuarios = $conn->prepare("SELECT id, nombres, apellidos FROM usuarios ORDER BY nombres, apellidos");
    $stmt_usuarios->execute();
    $usuarios = $stmt_usuarios->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error al obtener usuarios: " . $e->getMessage());
    $error = "Error al cargar la lista de responsables.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['registrar_manual'])) {
        // Lógica para el registro manual
        $marca = filter_var(trim($_POST['marca']), FILTER_SANITIZE_STRING);
        $modelo = filter_var(trim($_POST['modelo']), FILTER_SANITIZE_STRING);
        $serial = filter_var(trim($_POST['serial']), FILTER_SANITIZE_STRING);
        $categoria = filter_var(trim($_POST['categoria']), FILTER_SANITIZE_STRING);
        $estado = filter_var(trim($_POST['estado']), FILTER_SANITIZE_STRING);
        $id_persona = filter_var($_POST['id_persona'], FILTER_SANITIZE_NUMBER_INT);

        $todo_ok = true;

        if (empty($marca)) {
            $errores_manual['marca'] = "La marca es requerida.";
            $todo_ok = false;
        }
        if (empty($modelo)) {
            $errores_manual['modelo'] = "El modelo es requerido.";
            $todo_ok = false;
        }
        if (empty($serial)) {
            $errores_manual['serial'] = "El serial es requerido.";
            $todo_ok = false;
        }
        if (empty($categoria)) {
            $errores_manual['categoria'] = "La categoría es requerida.";
            $todo_ok = false;
        }
        if (empty($estado)) {
            $errores_manual['estado'] = "El estado es requerido.";
            $todo_ok = false;
        }
        if (!is_numeric($id_persona) || $id_persona <= 0) {
            $errores_manual['id_persona'] = "Seleccione un responsable válido.";
            $todo_ok = false;
        }

        if ($todo_ok) {
            try {
                $stmt = $conn->prepare("INSERT INTO inventarios (marca, modelo, serial, categoria, estado, id_persona) VALUES (:marca, :modelo, :serial, :categoria, :estado, :id_persona)");
                $stmt->execute([
                    ':marca' => $marca,
                    ':modelo' => $modelo,
                    ':serial' => $serial,
                    ':categoria' => $categoria,
                    ':estado' => $estado,
                    ':id_persona' => $id_persona
                ]);
                $mensaje = "Elemento de inventario registrado con éxito.";
                // Limpiar el formulario aquí si es necesario
                $_POST['marca'] = '';
                $_POST['modelo'] = '';
                $_POST['serial'] = '';
                $_POST['categoria'] = '';
                $_POST['estado'] = '';
                $_POST['id_persona'] = '';
            } catch (PDOException $e) {
                error_log("Error al registrar inventario manual: " . $e->getMessage());
                $error = "Error al registrar el elemento de inventario.";
            }
        } else {
            $error = "Por favor, corrija los errores en el formulario.";
        }

    } elseif (isset($_POST['cargar_csv'])) {
        // Lógica para la carga de CSV
        if (isset($_FILES['archivo_csv']) && $_FILES['archivo_csv']['error'] == 0) {
            $archivo = $_FILES['archivo_csv'];
            $nombre_archivo = $archivo['name'];
            $tipo_archivo = $archivo['type'];
            $ruta_temporal = $archivo['tmp_name'];

            $extension = pathinfo($nombre_archivo, PATHINFO_EXTENSION);

            if ($extension === 'csv' && $tipo_archivo === 'text/csv') {
                // Procesar el archivo CSV
                if (($handle = fopen($ruta_temporal, "r")) !== FALSE) {
                    $row = 0;
                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        $row++;
                        if ($row === 1) {
                            // Validar encabezados (opcionalmente)
                            if (count($data) !== 6 || $data[0] !== 'marca' || $data[1] !== 'modelo' || $data[2] !== 'serial' || $data[3] !== 'categoria' || $data[4] !== 'estado' || $data[5] !== 'id_persona') {
                                $error = "El archivo CSV tiene un formato incorrecto. Debe ser: marca,modelo,serial,categoria,estado,id_persona";
                                break;
                            }
                            continue; // Saltar la fila de encabezados
                        }

                        if (count($data) === 6) {
                            $marca_csv = filter_var(trim($data[0]), FILTER_SANITIZE_STRING);
                            $modelo_csv = filter_var(trim($data[1]), FILTER_SANITIZE_STRING);
                            $serial_csv = filter_var(trim($data[2]), FILTER_SANITIZE_STRING);
                            $categoria_csv = filter_var(trim($data[3]), FILTER_SANITIZE_STRING);
                            $estado_csv = filter_var(trim($data[4]), FILTER_SANITIZE_STRING);
                            $id_persona_csv = filter_var($data[5], FILTER_SANITIZE_NUMBER_INT);

                            if (!empty($marca_csv) && !empty($modelo_csv) && !empty($serial_csv) && !empty($categoria_csv) && !empty($estado_csv) && is_numeric($id_persona_csv) && $id_persona_csv > 0) {
                                try {
                                    $stmt_insert_csv = $conn->prepare("INSERT INTO inventarios (marca, modelo, serial, categoria, estado, id_persona) VALUES (:marca, :modelo, :serial, :categoria, :estado, :id_persona)");
                                    $stmt_insert_csv->execute([
                                        ':marca' => $marca_csv,
                                        ':modelo' => $modelo_csv,
                                        ':serial' => $serial_csv,
                                        ':categoria' => $categoria_csv,
                                        ':estado' => $estado_csv,
                                        ':id_persona' => $id_persona_csv
                                    ]);
                                } catch (PDOException $e) {
                                    error_log("Error al insertar fila CSV: " . $e->getMessage());
                                    $errores_csv[] = "Error al procesar la fila " . $row . ": " . $e->getMessage();
                                }
                            } else {
                                $errores_csv[] = "Datos inválidos en la fila " . $row . ".";
                            }
                        } else {
                            $errores_csv[] = "Número incorrecto de campos en la fila " . $row . ".";
                        }
                    }
                    fclose($handle);

                    if (empty($errores_csv) && empty($error)) {
                        $mensaje = "Inventario cargado desde CSV con éxito.";
                    } elseif (!empty($errores_csv)) {
                        $error = "Se encontraron algunos errores al procesar el CSV:";
                    }
                } else {
                    $error = "Error al abrir el archivo CSV.";
                }
            } else {
                $error = "Por favor, seleccione un archivo CSV válido.";
            }
        } else {
            $error = "Por favor, seleccione un archivo CSV para cargar.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Inventario</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        

        .container {
            width: 90%;
            max-width: 600px; /* Reducir el ancho máximo para centrar mejor */
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin: 20px;
        }

        h2 {
            text-align: center;
            color: #10019c;
            margin-bottom: 20px;
        }

        .form-switch {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-switch button {
            padding: 10px 20px;
            margin: 0 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            cursor: pointer;
            background-color: #ffff;
            color: #333;
        }

        .form-switch button.active {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }

        .manual-form, .csv-form {
            margin-top: 20px;
        }

        .manual-form .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
            font-size: 0.9em;
        }

        .form-group input[type="text"],
        .form-group select {
            width: calc(100% - 12px);
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            color: #333;
            font-size: 1em;
        }

        .manual-form .full-width {
            text-align: center;
            margin-top: 20px;
        }

        .manual-form .full-width button {
            padding: 12px 25px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1.1em;
            transition: background-color 0.3s ease;
        }

        .manual-form .full-width button:hover {
            background-color: #0056b3;
        }

        .error-message {
            color: #dc3545;
            font-size: 0.85em;
            margin-top: 5px;
        }

        .success-message {
            color: #28a745;
            margin-top: 20px;
            text-align: center;
            font-weight: bold;
        }

        .csv-form {
            margin-top: 20px;
            text-align: center;
            display: none;
        }

        

        .csv-upload label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            color: #555;
            font-size: 0.9em;
        }

        .csv-upload input[type="file"] {
            margin-bottom: 15px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1em;
        }

        .csv-form button[name="cargar_csv"] {
            padding: 12px 25px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1.1em;
            transition: background-color 0.3s ease;
        }

        .csv-form button[name="cargar_csv"]:hover {
            background-color: #0056b3;
        }

        .errores-csv-lista {
            margin-top: 10px;
            color: #dc3545;
            font-size: 0.85em;
            text-align: left;
        }

        .back-link {
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1000;
        }
    </style>
</head>
<body>

    <div class="registro-inventario-container">
        <div class="container">
            <h2>Registrar Inventario</h2>

            <?php if (!empty($mensaje)): ?>
                <p class="success-message"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <p class="error-message"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
                <?php if (!empty($errores_csv)): ?>
                    <ul class="errores-csv-lista">
                        <?php foreach ($errores_csv as $e): ?>
                            <li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <?php if (!empty($errores_manual)): ?>
                    <ul class="error-message">
                        <?php foreach ($errores_manual as $key => $e): ?>
                            <li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            <?php endif; ?>

            <div class="form-switch">
                <button id="mostrar-manual" class="active">Registro Manual</button>
                <button id="mostrar-csv">Cargar desde CSV</button>
            </div>

            <div id="formulario-manual" class="manual-form">
                <form method="post" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>">
                    <div class="form-group">
                        <label for="marca">Marca:</label>
                        <input type="text" id="marca" name="marca" value="<?= htmlspecialchars($_POST['marca'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                        <?php if (isset($errores_manual['marca'])): ?>
                            <p class="error-message"><?= htmlspecialchars($errores_manual['marca'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="modelo">Modelo:</label>
                        <input type="text" id="modelo" name="modelo" value="<?= htmlspecialchars($_POST['modelo'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                        <?php if (isset($errores_manual['modelo'])): ?>
                            <p class="error-message"><?= htmlspecialchars($errores_manual['modelo'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="serial">Serial:</label>
                        <input type="text" id="serial" name="serial" value="<?= htmlspecialchars($_POST['serial'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                        <?php if (isset($errores_manual['serial'])): ?>
                            <p class="error-message"><?= htmlspecialchars($errores_manual['serial'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="categoria">Categoría:</label>
                        <input type="text" id="categoria" name="categoria" value="<?= htmlspecialchars($_POST['categoria'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                        <?php if (isset($errores_manual['categoria'])): ?>
                            <p class="error-message"><?= htmlspecialchars($errores_manual['categoria'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="estado">Estado:</label>
                        <input type="text" id="estado" name="estado" value="<?= htmlspecialchars($_POST['estado'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                        <?php if (isset($errores_manual['estado'])): ?>
                            <p class="error-message"><?= htmlspecialchars($errores_manual['estado'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="id_persona">Persona Responsable:</label>
                        <select id="id_persona" name="id_persona" required>
                            <option value="">Seleccionar responsable</option>
                            <?php foreach ($usuarios as $usuario): ?>
                                <option value="<?= htmlspecialchars($usuario['id'], ENT_QUOTES, 'UTF-8') ?>" <?= (isset($_POST['id_persona']) && $_POST['id_persona'] == $usuario['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($usuario['nombres'], ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($usuario['apellidos'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errores_manual['id_persona'])): ?>
                            <p class="error-message"><?= htmlspecialchars($errores_manual['id_persona'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="full-width">
                        <button type="submit" name="registrar_manual">Registrar</button>
                    </div>
                </form>
            </div>

            <div id="formulario-csv" class="csv-form">
                <h3>Cargar desde Archivo CSV</h3>
                <form method="post" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>" enctype="multipart/form-data">
                    <div class="csv-upload">
                        <label for="archivo_csv">Seleccione un archivo CSV:</label>
                        <input type="file" id="archivo_csv" name="archivo_csv" accept=".csv" required>
                        <p style="margin-top: 10px; font-size: 0.9em;">El archivo CSV debe tener el formato: marca,modelo,serial,categoria,estado,id_persona</p>
                        <button type="submit" name="cargar_csv">Cargar CSV</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const mostrarManualBtn = document.getElementById('mostrar-manual');
        const mostrarCsvBtn = document.getElementById('mostrar-csv');
        const formularioManualDiv = document.getElementById('formulario-manual');
        const formularioCsvDiv = document.getElementById('formulario-csv');

        mostrarManualBtn.addEventListener('click', () => {
            formularioManualDiv.style.display = 'block';
            formularioCsvDiv.style.display = 'none';
            mostrarManualBtn.classList.add('active');
            mostrarCsvBtn.classList.remove('active');
        });

        mostrarCsvBtn.addEventListener('click', () => {
            formularioManualDiv.style.display = 'none';
            formularioCsvDiv.style.display = 'block';
            mostrarManualBtn.classList.remove('active');
            mostrarCsvBtn.classList.add('active');
        });

        // Inicialmente mostrar el formulario manual
        formularioCsvDiv.style.display = 'none';
        mostrarCsvBtn.classList.remove('active');
        formularioManualDiv.style.display = 'block'; // Mostrar manual por defecto
        mostrarManualBtn.classList.add('active');
    </script>
</body>
</html>