<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit();
}
if (!isset($_SESSION['id_empresa_actual'])) {
    header('Location: dashboard.php');
    exit();
}

require_once 'includes/db_connection.php';

$id_empresa_actual = $_SESSION['id_empresa_actual'];
$id_usuario_actual = $_SESSION['id_usuario'];

echo "<h2>Test FODA Directo - Sin JavaScript</h2>";
echo "<p>ID Empresa: " . $id_empresa_actual . "</p>";
echo "<p>ID Usuario: " . $id_usuario_actual . "</p>";

// Simular datos POST como si vinieran del formulario
$_POST['tabla_guardar'] = 'foda';
$_POST['fortaleza_3'] = 'Fortaleza 3 - Test Directo';
$_POST['fortaleza_4'] = 'Fortaleza 4 - Test Directo';
$_POST['debilidad_3'] = 'Debilidad 3 - Test Directo';
$_POST['debilidad_4'] = 'Debilidad 4 - Test Directo';

echo "<h3>Datos POST simulados:</h3>";
echo "<pre>" . print_r($_POST, true) . "</pre>";

// Simular la lógica de autodiagnostico_bdcg.php
$data['foda'] = [
    'fortaleza_3' => $_POST['fortaleza_3'] ?? '',
    'fortaleza_4' => $_POST['fortaleza_4'] ?? '',
    'debilidad_3' => $_POST['debilidad_3'] ?? '',
    'debilidad_4' => $_POST['debilidad_4'] ?? ''
];

echo "<h3>Datos FODA procesados:</h3>";
echo "<pre>" . print_r($data['foda'], true) . "</pre>";

// Función de guardado (copiada de autodiagnostico_bdcg.php)
function guardarFODAEnBD($fodaData, $mysqli) {
    $id_empresa = $_SESSION['id_empresa_actual'] ?? null;
    $id_usuario = $_SESSION['id_usuario'] ?? null;
    
    echo "<h4>🔍 Debug de la función guardarFODAEnBD:</h4>";
    echo "<p>ID Empresa: $id_empresa</p>";
    echo "<p>ID Usuario: $id_usuario</p>";
    
    if (!$id_empresa) {
        echo "<p>❌ Error: No hay ID de empresa</p>";
        return false;
    }
    
    try {
        // Limpiar FODA anterior de BCG
        $stmt_delete = $mysqli->prepare("DELETE FROM foda WHERE id_empresa = ? AND origen = 'bcg'");
        $stmt_delete->bind_param("i", $id_empresa);
        $stmt_delete->execute();
        $stmt_delete->close();
        echo "<p>✅ Datos anteriores de BCG eliminados</p>";
        
        // Insertar nuevas fortalezas y debilidades
        $stmt_insert = $mysqli->prepare("INSERT INTO foda (id_empresa, id_usuario, tipo, descripcion, origen, posicion) VALUES (?, ?, ?, ?, 'bcg', ?)");
        
        $guardados = 0;
        
        // Fortalezas
        if (!empty($fodaData['fortaleza_3'])) {
            $stmt_insert->bind_param("iisi", $id_empresa, $id_usuario, 'fortaleza', $fodaData['fortaleza_3'], 3);
            $stmt_insert->execute();
            $guardados++;
            echo "<p>✅ Fortaleza 3 guardada: " . htmlspecialchars($fodaData['fortaleza_3']) . "</p>";
        }
        if (!empty($fodaData['fortaleza_4'])) {
            $stmt_insert->bind_param("iisi", $id_empresa, $id_usuario, 'fortaleza', $fodaData['fortaleza_4'], 4);
            $stmt_insert->execute();
            $guardados++;
            echo "<p>✅ Fortaleza 4 guardada: " . htmlspecialchars($fodaData['fortaleza_4']) . "</p>";
        }
        
        // Debilidades
        if (!empty($fodaData['debilidad_3'])) {
            $stmt_insert->bind_param("iisi", $id_empresa, $id_usuario, 'debilidad', $fodaData['debilidad_3'], 3);
            $stmt_insert->execute();
            $guardados++;
            echo "<p>✅ Debilidad 3 guardada: " . htmlspecialchars($fodaData['debilidad_3']) . "</p>";
        }
        if (!empty($fodaData['debilidad_4'])) {
            $stmt_insert->bind_param("iisi", $id_empresa, $id_usuario, 'debilidad', $fodaData['debilidad_4'], 4);
            $stmt_insert->execute();
            $guardados++;
            echo "<p>✅ Debilidad 4 guardada: " . htmlspecialchars($fodaData['debilidad_4']) . "</p>";
        }
        
        $stmt_insert->close();
        echo "<p><strong>Total guardados: $guardados registros</strong></p>";
        return true;
        
    } catch (mysqli_sql_exception $e) {
        echo "<p>❌ Error: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Verificar condición de guardado
if (isset($_POST['tabla_guardar']) && $_POST['tabla_guardar'] === 'foda') {
    echo "<h3>✅ Condición de guardado FODA activada</h3>";
    
    $resultado = guardarFODAEnBD($data['foda'], $mysqli);
    
    if ($resultado) {
        echo "<h3 style='color: green;'>✅ Guardado exitoso!</h3>";
    } else {
        echo "<h3 style='color: red;'>❌ Error en el guardado!</h3>";
    }
} else {
    echo "<h3>❌ Condición de guardado FODA NO activada</h3>";
}

// Mostrar datos actuales
echo "<h3>📊 Datos actuales en la tabla FODA:</h3>";
$stmt = $mysqli->prepare("SELECT tipo, descripcion, origen, posicion, fecha_creacion FROM foda WHERE id_empresa = ? ORDER BY origen, tipo, posicion ASC");
$stmt->bind_param("i", $id_empresa_actual);
$stmt->execute();
$result = $stmt->get_result();

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background-color: #f0f0f0;'><th>Tipo</th><th>Descripción</th><th>Origen</th><th>Posición</th><th>Fecha</th></tr>";

while ($row = $result->fetch_assoc()) {
    $bgColor = $row['origen'] === 'bcg' ? '#e8f5e8' : '#f0f0f0';
    echo "<tr style='background-color: $bgColor;'>";
    echo "<td>" . htmlspecialchars($row['tipo']) . "</td>";
    echo "<td>" . htmlspecialchars($row['descripcion']) . "</td>";
    echo "<td><strong>" . htmlspecialchars($row['origen']) . "</strong></td>";
    echo "<td>" . htmlspecialchars($row['posicion']) . "</td>";
    echo "<td>" . htmlspecialchars($row['fecha_creacion']) . "</td>";
    echo "</tr>";
}
echo "</table>";

$stmt->close();
$mysqli->close();
?>

<p><a href="autodiagnostico_bdcg.php">← Volver a BCG</a></p>
