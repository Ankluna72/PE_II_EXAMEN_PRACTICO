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

echo "<h2>Debug BCG FODA - Análisis Completo</h2>";
echo "<p>ID Empresa: " . $id_empresa_actual . "</p>";
echo "<p>ID Usuario: " . $id_usuario_actual . "</p>";

// Verificar si hay datos POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>🔍 Datos POST recibidos:</h3>";
    echo "<pre>" . print_r($_POST, true) . "</pre>";
    
    // Verificar específicamente los campos FODA
    $fodaData = [
        'fortaleza_3' => $_POST['fortaleza_3'] ?? '',
        'fortaleza_4' => $_POST['fortaleza_4'] ?? '',
        'debilidad_3' => $_POST['debilidad_3'] ?? '',
        'debilidad_4' => $_POST['debilidad_4'] ?? ''
    ];
    
    echo "<h3>📝 Datos FODA extraídos:</h3>";
    echo "<pre>" . print_r($fodaData, true) . "</pre>";
    
    // Verificar si se envió el parámetro tabla_guardar
    $tabla_guardar = $_POST['tabla_guardar'] ?? 'NO_ENVIADO';
    echo "<h3>🏷️ Parámetro tabla_guardar: " . $tabla_guardar . "</h3>";
    
    if ($tabla_guardar === 'foda') {
        echo "<h3>✅ Condición de guardado FODA activada</h3>";
        
        // Intentar guardar
        try {
            // Limpiar FODA anterior de BCG
            $stmt_delete = $mysqli->prepare("DELETE FROM foda WHERE id_empresa = ? AND origen = 'bcg'");
            $stmt_delete->bind_param("i", $id_empresa_actual);
            $stmt_delete->execute();
            $stmt_delete->close();
            echo "<p>✅ Datos anteriores de BCG eliminados</p>";
            
            // Insertar nuevas fortalezas y debilidades
            $stmt_insert = $mysqli->prepare("INSERT INTO foda (id_empresa, id_usuario, tipo, descripcion, origen, posicion) VALUES (?, ?, ?, ?, 'bcg', ?)");
            
            $guardados = 0;
            
            // Fortalezas
            if (!empty($fodaData['fortaleza_3'])) {
                $stmt_insert->bind_param("iisi", $id_empresa_actual, $id_usuario_actual, 'fortaleza', $fodaData['fortaleza_3'], 3);
                $stmt_insert->execute();
                $guardados++;
                echo "<p>✅ Fortaleza 3 guardada: " . htmlspecialchars($fodaData['fortaleza_3']) . "</p>";
            }
            if (!empty($fodaData['fortaleza_4'])) {
                $stmt_insert->bind_param("iisi", $id_empresa_actual, $id_usuario_actual, 'fortaleza', $fodaData['fortaleza_4'], 4);
                $stmt_insert->execute();
                $guardados++;
                echo "<p>✅ Fortaleza 4 guardada: " . htmlspecialchars($fodaData['fortaleza_4']) . "</p>";
            }
            
            // Debilidades
            if (!empty($fodaData['debilidad_3'])) {
                $stmt_insert->bind_param("iisi", $id_empresa_actual, $id_usuario_actual, 'debilidad', $fodaData['debilidad_3'], 3);
                $stmt_insert->execute();
                $guardados++;
                echo "<p>✅ Debilidad 3 guardada: " . htmlspecialchars($fodaData['debilidad_3']) . "</p>";
            }
            if (!empty($fodaData['debilidad_4'])) {
                $stmt_insert->bind_param("iisi", $id_empresa_actual, $id_usuario_actual, 'debilidad', $fodaData['debilidad_4'], 4);
                $stmt_insert->execute();
                $guardados++;
                echo "<p>✅ Debilidad 4 guardada: " . htmlspecialchars($fodaData['debilidad_4']) . "</p>";
            }
            
            $stmt_insert->close();
            echo "<p><strong>Total guardados: $guardados registros</strong></p>";
            
        } catch (mysqli_sql_exception $e) {
            echo "<p>❌ Error: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<h3>❌ Condición de guardado FODA NO activada</h3>";
        echo "<p>El parámetro 'tabla_guardar' debe ser 'foda' para que se ejecute el guardado.</p>";
    }
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

<h3>🧪 Formulario de Prueba:</h3>
<form method="post">
    <p>
        <label>Fortaleza 3:</label><br>
        <input type="text" name="fortaleza_3" value="Fortaleza BCG 3 - Debug" style="width: 300px;">
    </p>
    <p>
        <label>Fortaleza 4:</label><br>
        <input type="text" name="fortaleza_4" value="Fortaleza BCG 4 - Debug" style="width: 300px;">
    </p>
    <p>
        <label>Debilidad 3:</label><br>
        <input type="text" name="debilidad_3" value="Debilidad BCG 3 - Debug" style="width: 300px;">
    </p>
    <p>
        <label>Debilidad 4:</label><br>
        <input type="text" name="debilidad_4" value="Debilidad BCG 4 - Debug" style="width: 300px;">
    </p>
    <p>
        <button type="submit" name="tabla_guardar" value="foda">Probar Guardado FODA</button>
    </p>
</form>

<p><a href="autodiagnostico_bdcg.php">← Volver a BCG</a></p>
