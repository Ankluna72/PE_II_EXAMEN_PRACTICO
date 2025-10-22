<?php
session_start();
require_once 'includes/db_connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$id_empresa = intval($input['id_empresa']);
$id_usuario = $_SESSION['id_usuario'];

// Verificar que el usuario es propietario de la empresa
$stmt = $mysqli->prepare("SELECT imagen FROM empresa WHERE id = ? AND id_usuario = ?");
$stmt->bind_param("ii", $id_empresa, $id_usuario);
$stmt->execute();
$stmt->bind_result($imagen_actual);
$stmt->fetch();
$stmt->close();

if (!$imagen_actual) {
    echo json_encode(['success' => false, 'message' => 'No hay imagen para eliminar o no tienes permisos']);
    exit();
}

// Eliminar archivo físico
$upload_dir = 'uploads/empresa_images/';
if (file_exists($upload_dir . $imagen_actual)) {
    unlink($upload_dir . $imagen_actual);
}

// Actualizar base de datos
$stmt = $mysqli->prepare("UPDATE empresa SET imagen = NULL WHERE id = ?");
$stmt->bind_param("i", $id_empresa);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Imagen eliminada correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar la base de datos']);
}

$stmt->close();
$mysqli->close();
?>