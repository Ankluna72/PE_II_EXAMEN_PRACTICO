<?php
session_start();
require_once 'includes/db_connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario']) || !isset($_POST['id_empresa'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

$id_empresa = intval($_POST['id_empresa']);
$id_usuario = $_SESSION['id_usuario'];

// Verificar que el usuario es propietario de la empresa
$stmt = $mysqli->prepare("SELECT id FROM empresa WHERE id = ? AND id_usuario = ?");
$stmt->bind_param("ii", $id_empresa, $id_usuario);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'No tienes permisos para esta empresa']);
    exit();
}
$stmt->close();

// Crear directorio si no existe
$upload_dir = 'uploads/empresa_images/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Validar archivo
if (!isset($_FILES['empresa_image']) || $_FILES['empresa_image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Error al subir el archivo']);
    exit();
}

$file = $_FILES['empresa_image'];
$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
$max_size = 5 * 1024 * 1024; // 5MB

if (!in_array($file['type'], $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Tipo de archivo no permitido']);
    exit();
}

if ($file['size'] > $max_size) {
    echo json_encode(['success' => false, 'message' => 'El archivo es demasiado grande']);
    exit();
}

// Generar nombre único
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'empresa_' . $id_empresa . '_' . time() . '.' . $extension;
$filepath = $upload_dir . $filename;

// Eliminar imagen anterior si existe
$stmt = $mysqli->prepare("SELECT imagen FROM empresa WHERE id = ?");
$stmt->bind_param("i", $id_empresa);
$stmt->execute();
$stmt->bind_result($imagen_anterior);
$stmt->fetch();
$stmt->close();

if ($imagen_anterior && file_exists($upload_dir . $imagen_anterior)) {
    unlink($upload_dir . $imagen_anterior);
}

// Mover archivo
if (move_uploaded_file($file['tmp_name'], $filepath)) {
    // Actualizar base de datos
    $stmt = $mysqli->prepare("UPDATE empresa SET imagen = ? WHERE id = ?");
    $stmt->bind_param("si", $filename, $id_empresa);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Imagen subida correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar en la base de datos']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Error al mover el archivo']);
}

$mysqli->close();
?>