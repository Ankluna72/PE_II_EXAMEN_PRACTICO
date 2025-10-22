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

$pageStyles = ['css/collaboration.css', 'css/modules.css'];
require_once 'includes/db_connection.php';
require_once 'includes/header.php';

$id_usuario_actual = $_SESSION['id_usuario'];
$id_empresa_actual = $_SESSION['id_empresa_actual'];
$mensaje = '';
$mensaje_tipo = '';

// Verificar que el usuario es el propietario de la empresa
$stmt_owner = $mysqli->prepare("SELECT id_usuario FROM empresa WHERE id = ?");
$stmt_owner->bind_param("i", $id_empresa_actual);
$stmt_owner->execute();
$stmt_owner->bind_result($id_propietario);
$stmt_owner->fetch();
$stmt_owner->close();

if ($id_propietario != $id_usuario_actual) {
    header('Location: dashboard.php');
    exit();
}

// Obtener información de la empresa
$stmt_empresa = $mysqli->prepare("SELECT nombre_empresa FROM empresa WHERE id = ?");
$stmt_empresa->bind_param("i", $id_empresa_actual);
$stmt_empresa->execute();
$stmt_empresa->bind_result($nombre_empresa);
$stmt_empresa->fetch();
$stmt_empresa->close();

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['invitar_colaborador'])) {
        $email_colaborador = trim($_POST['email_colaborador']);
        
        if (empty($email_colaborador)) {
            $mensaje = 'Por favor ingresa un correo electrónico válido.';
            $mensaje_tipo = 'error';
        } else {
            // Verificar si el usuario existe
            $stmt_user = $mysqli->prepare("SELECT id, nombre, apellido FROM usuario WHERE email = ?");
            $stmt_user->bind_param("s", $email_colaborador);
            $stmt_user->execute();
            $resultado_user = $stmt_user->get_result();
            
            if ($resultado_user->num_rows === 0) {
                $mensaje = 'No se encontró un usuario registrado con ese correo electrónico.';
                $mensaje_tipo = 'error';
            } else {
                $usuario_colaborador = $resultado_user->fetch_assoc();
                
                // Verificar si ya es colaborador
                $stmt_check = $mysqli->prepare("SELECT id FROM colaboradores_empresa WHERE id_empresa = ? AND id_usuario_colaborador = ?");
                $stmt_check->bind_param("ii", $id_empresa_actual, $usuario_colaborador['id']);
                $stmt_check->execute();
                $stmt_check->store_result();
                
                if ($stmt_check->num_rows > 0) {
                    $mensaje = 'Este usuario ya es colaborador de la empresa.';
                    $mensaje_tipo = 'warning';
                } else {
                    // Verificar si es el propietario
                    if ($usuario_colaborador['id'] == $id_usuario_actual) {
                        $mensaje = 'No puedes invitarte a ti mismo como colaborador.';
                        $mensaje_tipo = 'warning';
                    } else {
                        // Agregar colaborador
                        $stmt_insert = $mysqli->prepare("INSERT INTO colaboradores_empresa (id_empresa, id_usuario_colaborador) VALUES (?, ?)");
                        $stmt_insert->bind_param("ii", $id_empresa_actual, $usuario_colaborador['id']);
                        
                        if ($stmt_insert->execute()) {
                            $mensaje = 'Colaborador agregado exitosamente.';
                            $mensaje_tipo = 'success';
                        } else {
                            $mensaje = 'Error al agregar el colaborador.';
                            $mensaje_tipo = 'error';
                        }
                        $stmt_insert->close();
                    }
                }
                $stmt_check->close();
            }
            $stmt_user->close();
        }
    } elseif (isset($_POST['remover_colaborador'])) {
        $id_colaborador = $_POST['id_colaborador'];
        
        $stmt_remove = $mysqli->prepare("DELETE FROM colaboradores_empresa WHERE id = ? AND id_empresa = ?");
        $stmt_remove->bind_param("ii", $id_colaborador, $id_empresa_actual);
        
        if ($stmt_remove->execute()) {
            $mensaje = 'Colaborador removido exitosamente.';
            $mensaje_tipo = 'success';
        } else {
            $mensaje = 'Error al remover el colaborador.';
            $mensaje_tipo = 'error';
        }
        $stmt_remove->close();
    }
}

// Obtener lista de colaboradores
$stmt_colaboradores = $mysqli->prepare("
    SELECT c.id, c.fecha_invitacion, c.estado, u.nombre, u.apellido, u.email 
    FROM colaboradores_empresa c 
    JOIN usuario u ON c.id_usuario_colaborador = u.id 
    WHERE c.id_empresa = ? 
    ORDER BY c.fecha_invitacion DESC
");
$stmt_colaboradores->bind_param("i", $id_empresa_actual);
$stmt_colaboradores->execute();
$colaboradores = $stmt_colaboradores->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_colaboradores->close();
?>

<div class="container mt-4">
    <div class="module-container">
        <div class="module-header">
            <h2 class="module-title">Gestionar Colaboradores - <?php echo htmlspecialchars($nombre_empresa); ?></h2>
        </div>
        <div class="module-content">
            <?php if ($mensaje): ?>
                <div class="collaboration-alert <?php echo $mensaje_tipo; ?>">
                    <i class="fas fa-<?php echo $mensaje_tipo === 'success' ? 'check-circle' : ($mensaje_tipo === 'warning' ? 'exclamation-triangle' : 'times-circle'); ?>"></i>
                    <?php echo htmlspecialchars($mensaje); ?>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-6">
                    <div class="invite-form">
                        <h5 class="mb-3">
                            <i class="fas fa-user-plus"></i> Invitar Colaborador
                        </h5>
                        <form action="gestionar_colaboradores.php" method="POST">
                            <div class="mb-3">
                                <label for="email_colaborador" class="form-label">Correo electrónico del colaborador</label>
                                <input 
                                    type="email" 
                                    class="form-control" 
                                    id="email_colaborador" 
                                    name="email_colaborador" 
                                    placeholder="colaborador@ejemplo.com"
                                    required
                                >
                                <div class="form-text">El usuario debe estar registrado en la plataforma.</div>
                            </div>
                            <button type="submit" name="invitar_colaborador" class="btn-send-invite">
                                <i class="fas fa-paper-plane"></i> Enviar Invitación
                            </button>
                        </form>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="collaborative-enterprises">
                        <h5 class="mb-3">
                            <i class="fas fa-users"></i> Colaboradores Actuales
                        </h5>
                        
                        <?php if (empty($colaboradores)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-user-friends fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No hay colaboradores agregados aún.</p>
                            </div>
                        <?php else: ?>
                            <div class="collaborators-list">
                                <?php foreach ($colaboradores as $colaborador): ?>
                                    <div class="collaborator-item">
                                        <div class="collaborator-info">
                                            <div class="collaborator-avatar">
                                                <?php echo strtoupper(substr($colaborador['nombre'], 0, 1) . substr($colaborador['apellido'], 0, 1)); ?>
                                            </div>
                                            <div class="collaborator-details">
                                                <h6><?php echo htmlspecialchars($colaborador['nombre'] . ' ' . $colaborador['apellido']); ?></h6>
                                                <small><?php echo htmlspecialchars($colaborador['email']); ?></small>
                                                <br>
                                                <small class="text-muted">
                                                    Agregado: <?php echo date('d/m/Y H:i', strtotime($colaborador['fecha_invitacion'])); ?>
                                                </small>
                                            </div>
                                        </div>
                                        <div class="collaborator-actions">
                                            <form action="gestionar_colaboradores.php" method="POST" style="display: inline;">
                                                <input type="hidden" name="id_colaborador" value="<?php echo $colaborador['id']; ?>">
                                                <button type="submit" name="remover_colaborador" class="btn-remove-collaborator" 
                                                        onclick="return confirm('¿Estás seguro de que quieres remover este colaborador?')">
                                                    <i class="fas fa-trash"></i> Remover
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="dashboard.php" class="btn btn-nav">
                    <i class="fas fa-arrow-left"></i> Volver al Dashboard
                </a>
                <div class="collaboration-indicator">
                    <i class="fas fa-users"></i>
                    <?php echo count($colaboradores); ?> colaborador<?php echo count($colaboradores) !== 1 ? 'es' : ''; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
$mysqli->close();
?>
