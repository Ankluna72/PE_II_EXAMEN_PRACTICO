<?php
/**
 * Funciones de control de acceso para empresas
 */

/**
 * Verifica si un usuario tiene acceso a una empresa (como propietario o colaborador)
 * @param mysqli $mysqli Conexión a la base de datos
 * @param int $id_usuario ID del usuario
 * @param int $id_empresa ID de la empresa
 * @return array Array con información del acceso: ['tiene_acceso' => bool, 'es_propietario' => bool, 'es_colaborador' => bool]
 */
function verificar_acceso_empresa($mysqli, $id_usuario, $id_empresa) {
    $resultado = [
        'tiene_acceso' => false,
        'es_propietario' => false,
        'es_colaborador' => false
    ];
    
    // Verificar si es propietario
    $stmt_propietario = $mysqli->prepare("SELECT id FROM empresa WHERE id = ? AND id_usuario = ?");
    $stmt_propietario->bind_param("ii", $id_empresa, $id_usuario);
    $stmt_propietario->execute();
    $stmt_propietario->store_result();
    
    if ($stmt_propietario->num_rows > 0) {
        $resultado['es_propietario'] = true;
        $resultado['tiene_acceso'] = true;
    }
    $stmt_propietario->close();
    
    // Si no es propietario, verificar si es colaborador
    if (!$resultado['es_propietario']) {
        $stmt_colaborador = $mysqli->prepare("SELECT id FROM colaboradores_empresa WHERE id_empresa = ? AND id_usuario_colaborador = ? AND estado = 'activo'");
        $stmt_colaborador->bind_param("ii", $id_empresa, $id_usuario);
        $stmt_colaborador->execute();
        $stmt_colaborador->store_result();
        
        if ($stmt_colaborador->num_rows > 0) {
            $resultado['es_colaborador'] = true;
            $resultado['tiene_acceso'] = true;
        }
        $stmt_colaborador->close();
    }
    
    return $resultado;
}

/**
 * Verifica si un usuario tiene acceso a una empresa y redirige si no lo tiene
 * @param mysqli $mysqli Conexión a la base de datos
 * @param int $id_usuario ID del usuario
 * @param int $id_empresa ID de la empresa
 * @param string $redirect_url URL a la que redirigir si no tiene acceso
 */
function verificar_y_redirigir_acceso($mysqli, $id_usuario, $id_empresa, $redirect_url = 'dashboard.php') {
    $acceso = verificar_acceso_empresa($mysqli, $id_usuario, $id_empresa);
    
    if (!$acceso['tiene_acceso']) {
        header("Location: $redirect_url");
        exit();
    }
    
    return $acceso;
}

/**
 * Obtiene información de la empresa actual para mostrar en la interfaz
 * @param mysqli $mysqli Conexión a la base de datos
 * @param int $id_empresa ID de la empresa
 * @return array Información de la empresa
 */
function obtener_info_empresa($mysqli, $id_empresa) {
    $stmt = $mysqli->prepare("
        SELECT e.nombre_empresa, e.id_usuario, u.nombre, u.apellido 
        FROM empresa e 
        JOIN usuario u ON e.id_usuario = u.id 
        WHERE e.id = ?
    ");
    $stmt->bind_param("i", $id_empresa);
    $stmt->execute();
    $resultado = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    return $resultado;
}
?>
