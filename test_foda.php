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

echo "<h2>Test de Datos FODA</h2>";
echo "<p>ID Empresa: " . $id_empresa_actual . "</p>";

// Verificar datos en la tabla foda
$stmt = $mysqli->prepare("SELECT tipo, descripcion, origen, posicion FROM foda WHERE id_empresa = ? ORDER BY tipo, posicion ASC");
$stmt->bind_param("i", $id_empresa_actual);
$stmt->execute();
$result = $stmt->get_result();

echo "<h3>Datos en la tabla FODA:</h3>";
echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Tipo</th><th>Descripción</th><th>Origen</th><th>Posición</th></tr>";

$foda_data = [
    'debilidad' => [],
    'amenaza' => [],
    'fortaleza' => [],
    'oportunidad' => []
];

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['tipo']) . "</td>";
    echo "<td>" . htmlspecialchars($row['descripcion']) . "</td>";
    echo "<td>" . htmlspecialchars($row['origen']) . "</td>";
    echo "<td>" . htmlspecialchars($row['posicion']) . "</td>";
    echo "</tr>";
    
    $foda_data[$row['tipo']][] = $row['descripcion'];
}
echo "</table>";

echo "<h3>Datos agrupados:</h3>";
foreach ($foda_data as $tipo => $items) {
    echo "<h4>" . strtoupper($tipo) . " (" . count($items) . " elementos):</h4>";
    foreach ($items as $item) {
        echo "<p>- " . htmlspecialchars($item) . "</p>";
    }
}

$stmt->close();
$mysqli->close();
?>
