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

$pageStyles = ['css/modules.css'];
require_once 'includes/db_connection.php';
require_once 'includes/access_control.php';
require_once 'includes/header.php';

$id_usuario_actual = $_SESSION['id_usuario'];
$id_empresa_actual = $_SESSION['id_empresa_actual'];

// Verificar acceso a la empresa
$acceso_empresa = verificar_y_redirigir_acceso($mysqli, $id_usuario_actual, $id_empresa_actual);

// Manejar mensajes de sesión
$mensaje = '';
$mensaje_tipo = '';
if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    $mensaje_tipo = $_SESSION['mensaje_tipo'] ?? '';
    unset($_SESSION['mensaje']);
    unset($_SESSION['mensaje_tipo']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['mision'])) {
        $nueva_mision = $_POST['mision'];
        $stmt = $mysqli->prepare("UPDATE empresa SET mision = ? WHERE id = ?");
        $stmt->bind_param("si", $nueva_mision, $id_empresa_actual);
        if ($stmt->execute()) {
            $_SESSION['mensaje'] = '<div class="alert alert-success alert-success-auto">Misión guardada correctamente.</div>';
            $_SESSION['mensaje_tipo'] = 'success';
        } else {
            $_SESSION['mensaje'] = '<div class="alert alert-danger">Error al guardar la misión.</div>';
            $_SESSION['mensaje_tipo'] = 'error';
        }
        $stmt->close();
        
        // Redirigir para evitar resubmisión
        header('Location: mision.php');
        exit();
    }
}

$stmt_select = $mysqli->prepare("SELECT mision FROM empresa WHERE id = ?");
$stmt_select->bind_param("i", $id_empresa_actual);
$stmt_select->execute();
$stmt_select->bind_result($mision_actual_db);
$stmt_select->fetch();
$mision_actual = $mision_actual_db ?? '';
$stmt_select->close();

?>
<div class="container mt-4">
    <div class="module-container">
        <div class="module-header">
            <h2 class="module-title">1. MISIÓN</h2>
            <?php if ($acceso_empresa['es_colaborador']): ?>
                <div class="collaboration-indicator">
                    <i class="fas fa-users"></i> Modo Colaboración
                </div>
            <?php endif; ?>
        </div>
        <div class="module-content">
            <div class="row">
                <div class="col-md-5">
                    <div class="explanation-box">
                        <p><strong>La MISIÓN es la razón de ser de la empresa u organización.</strong></p>
                        <ul>
                            <li>Debe ser clara, concisa y compartida.</li>
                            <li>Siempre orientada hacia el cliente no hacia el producto o servicio.</li>
                            <li>Refleja el propósito fundamental de la empresa en el mercado.</li>
                        </ul>
                    </div>
                    
                    <div class="examples-card">
                        <div class="examples-header">EJEMPLOS</div>
                        <div class="examples-body">
                            <p><strong>Empresa de servicios:</strong><br><small>La gestión de servicios que contribuyen a la calidad de vida de las personas y generan valor para los grupos de interés.</small></p>
                            <hr>
                            <p><strong>Empresa productora de café:</strong><br><small>Gracias a nuestro entusiasmo, trabajo en equipo y valores, queremos deleitar a todos aquellos que, en el mundo, aman la calidad de vida, a través del mejor café que la naturaleza pueda ofrecer...</small></p>
                        </div>
                    </div>
                </div>

                <div class="col-md-7">
                    <div class="form-card">
                        <div class="form-header">
                            En este apartado describa la Misión de su empresa.
                        </div>
                        <div class="form-body">
                            <?php echo $mensaje; ?>
                            <form action="mision.php" method="POST">
                                <div class="mb-3">
                                    <label for="mision" class="form-label">Misión de la empresa</label>
                                    <textarea class="form-control" name="mision" id="mision" rows="10" placeholder="Escribe aquí la misión..."><?php echo htmlspecialchars($mision_actual); ?></textarea>
                                </div>
                                <button type="submit" class="btn btn-save">Guardar Misión</button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <a href="dashboard.php" class="btn btn-nav">&laquo; Volver al Índice</a>
                        <a href="vision.php" class="btn btn-save">Siguiente: Visión &raquo;</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($mensaje_tipo === 'success'): ?>
<script>
// Recargar la página después de 3 segundos para sincronizar con colaboradores
setTimeout(function() {
    window.location.reload();
}, 3000);
</script>
<?php endif; ?>

<?php
require_once 'includes/footer.php';
$mysqli->close();
?>