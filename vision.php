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
require_once 'includes/header.php';

$id_empresa_actual = $_SESSION['id_empresa_actual'];

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
    if (isset($_POST['vision'])) {
        $nueva_vision = $_POST['vision'];
        $stmt = $mysqli->prepare("UPDATE empresa SET vision = ? WHERE id = ?");
        $stmt->bind_param("si", $nueva_vision, $id_empresa_actual);
        if ($stmt->execute()) {
            $_SESSION['mensaje'] = '<div class="alert alert-success alert-success-auto">Visión guardada correctamente.</div>';
            $_SESSION['mensaje_tipo'] = 'success';
        } else {
            $_SESSION['mensaje'] = '<div class="alert alert-danger">Error al guardar la visión.</div>';
            $_SESSION['mensaje_tipo'] = 'error';
        }
        $stmt->close();
        
        // Redirigir para evitar resubmisión
        header('Location: vision.php');
        exit();
    }
}

$stmt_select = $mysqli->prepare("SELECT vision FROM empresa WHERE id = ?");
$stmt_select->bind_param("i", $id_empresa_actual);
$stmt_select->execute();
$stmt_select->bind_result($vision_actual_db);
$stmt_select->fetch();
$vision_actual = $vision_actual_db ?? ''; 
$stmt_select->close();

?>
<div class="container mt-4">
    <div class="module-container">
        <div class="module-header">
            <h2 class="module-title">2. VISIÓN</h2>
        </div>
        <div class="module-content">
            <div class="row">
                <div class="col-md-5">
                    <div class="explanation-box">
                        <p><strong>La VISIÓN de una empresa define lo que la empresa/organización quiere lograr en el futuro. Es lo que la organización aspira llegar a ser en torno a 2-3 años.</strong></p>
                        <ul>
                            <li>Debe ser retadora, positiva, compartida y coherente con la misión.</li>
                            <li>Marca el fin último que la estrategia debe seguir.</li>
                            <li>Proyecta la imagen de destino que se pretende alcanzar.</li>
                        </ul>
                    </div>
                    
                    <div class="examples-card">
                        <div class="examples-header">EJEMPLOS</div>
                        <div class="examples-body">
                            <p><strong>Empresa de servicios:</strong><br><small>Ser el grupo empresarial de referencia en nuestras áreas de actividad.</small></p>
                            <hr>
                            <p><strong>Empresa productora de café:</strong><br><small>Queremos ser en el mundo el punto de referencia de la cultura y de la excelencia del café. Una empresa innovadora que propone los mejores productos y lugares de consumo y que, gracias a ello, crece y se convierte...</small></p>
                        </div>
                    </div>
                </div>

                <div class="col-md-7">
                    <div class="form-card">
                        <div class="form-header">
                            En este apartado describa la Visión de su empresa.
                        </div>
                        <div class="form-body">
                            <?php echo $mensaje; ?>
                            <form action="vision.php" method="POST">
                                <div class="mb-3">
                                    <label for="vision" class="form-label">Visión de la empresa</label>
                                    <textarea class="form-control" name="vision" id="vision" rows="10" placeholder="Escribe aquí la visión..."><?php echo htmlspecialchars($vision_actual); ?></textarea>
                                </div>
                                <button type="submit" class="btn btn-save">Guardar Visión</button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <a href="mision.php" class="btn btn-nav">&laquo; Anterior: Misión</a>
                        <a href="dashboard.php" class="btn btn-nav-outline">Volver al Índice</a>
                        <a href="valores.php" class="btn btn-save">Siguiente: Valores &raquo;</a>
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