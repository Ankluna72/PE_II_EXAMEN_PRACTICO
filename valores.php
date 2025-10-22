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
$mensaje = '';
$valores_actuales = '';

// Manejar mensajes de la sesión (patrón PRG)
if (isset($_SESSION['mensaje_valores'])) {
    $mensaje = $_SESSION['mensaje_valores'];
    unset($_SESSION['mensaje_valores']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['valores'])) {
        $nuevos_valores = $_POST['valores'];
        $stmt = $mysqli->prepare("UPDATE empresa SET valores = ? WHERE id = ?");
        $stmt->bind_param("si", $nuevos_valores, $id_empresa_actual);
        if ($stmt->execute()) {
            $_SESSION['mensaje_valores'] = '<div class="alert alert-success alert-success-auto">Valores guardados correctamente.</div>';
        } else {
            $_SESSION['mensaje_valores'] = '<div class="alert alert-danger">Error al guardar los valores.</div>';
        }
        $stmt->close();
        
        // Redireccionar para evitar reenvío de POST
        header('Location: valores.php');
        exit();
    }
}

$stmt_select = $mysqli->prepare("SELECT valores FROM empresa WHERE id = ?");
$stmt_select->bind_param("i", $id_empresa_actual);
$stmt_select->execute();
$stmt_select->bind_result($valores_actuales_db);
$stmt_select->fetch();
$valores_actuales = $valores_actuales_db ?? '';
$stmt_select->close();

?>
<div class="container mt-4">
    <div class="module-container">
        <div class="module-header">
            <h2 class="module-title">3. VALORES</h2>
        </div>
        <div class="module-content">
            <div class="row">
                <div class="col-md-5">
                    <div class="explanation-box">
                        <p><strong>Los VALORES de una empresa son el conjunto de principios, reglas y aspectos culturales con los que se rige la organización. Son las pautas de comportamiento de la empresa y generalmente son pocos, entre 3 y 6.</strong></p>
                        <ul>
                            <li>Integridad</li>
                            <li>Compromiso con el desarrollo humano</li>
                            <li>Ética profesional</li>
                            <li>Responsabilidad social</li>
                            <li>Innovación</li>
                        </ul>
                    </div>
                    
                    <div class="examples-card">
                        <div class="examples-header">EJEMPLOS</div>
                        <div class="examples-body">
                            <p><strong>Empresa de servicios:</strong><br><small>La excelencia en la prestación de servicios. La innovación orientada a la mejora continua de procesos productos y servicios.</small></p>
                            <hr>
                            <p><strong>Agencia de certificación:</strong><br><small>Integridad y ética. Consejo y validación imparciales. Respeto por todas las personas.</small></p>
                        </div>
                    </div>
                </div>

                <div class="col-md-7">
                    <div class="form-card">
                        <div class="form-header">
                            En este apartado exponga los Valores de su empresa.
                        </div>
                        <div class="form-body">
                            <?php echo $mensaje; ?>
                            <form action="valores.php" method="POST">
                                <div class="mb-3">
                                    <label for="valores" class="form-label">Valores de la empresa</label>
                                    <textarea class="form-control" name="valores" id="valores" rows="10" placeholder="Escribe aquí los valores, uno por línea..."><?php echo htmlspecialchars($valores_actuales); ?></textarea>
                                </div>
                                <button type="submit" class="btn btn-save">Guardar Valores</button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <a href="vision.php" class="btn btn-nav">&laquo; Anterior: Visión</a>
                        <a href="dashboard.php" class="btn btn-nav-outline">Volver al Índice</a>
                        <a href="objetivos.php" class="btn btn-save">Siguiente: Objetivos &raquo;</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (strpos($mensaje, 'alert-success') !== false): ?>
<script>
    // Recargar la página después de 3 segundos para mostrar cambios a otros colaboradores
    setTimeout(function() {
        window.location.reload();
    }, 3000);
</script>
<?php endif; ?>

<?php
require_once 'includes/footer.php';
$mysqli->close();
?>