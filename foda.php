<?php
session_start();
require_once 'includes/db_connection.php';
$pageStyles = ['css/modules.css'];
require_once 'includes/header.php';

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit();
}
if (!isset($_SESSION['id_empresa_actual'])) {
    header('Location: dashboard.php');
    exit();
}

$id_empresa_actual = $_SESSION['id_empresa_actual'];

// Procesar formulario de nuevo FODA
// Agregar nuevo FODA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tipo'], $_POST['descripcion']) && empty($_POST['delete_id'])) {
    $tipo = $_POST['tipo'];
    $descripcion = trim($_POST['descripcion']);
    if (in_array($tipo, ['fortaleza','oportunidad','debilidad','amenaza']) && $descripcion !== '') {
        $stmt = $mysqli->prepare("INSERT INTO foda (id_empresa, tipo, descripcion) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $id_empresa_actual, $tipo, $descripcion);
        $stmt->execute();
        $stmt->close();
        header('Location: cavalor.php');
        exit();
    }
}

// Eliminar FODA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id']) && is_numeric($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);
    $stmt = $mysqli->prepare("DELETE FROM foda WHERE id = ? AND id_empresa = ?");
    $stmt->bind_param("ii", $delete_id, $id_empresa_actual);
    $stmt->execute();
    $stmt->close();
    header('Location: cavalor.php');
    exit();
}

// Obtener FODA actual
$stmt = $mysqli->prepare("SELECT id, tipo, descripcion FROM foda WHERE id_empresa = ? ORDER BY tipo, id DESC");
$stmt->bind_param("i", $id_empresa_actual);
$stmt->execute();
$result = $stmt->get_result();
$foda_items = [];
while ($row = $result->fetch_assoc()) {
    $foda_items[$row['tipo']][] = $row;
}
$stmt->close();

?>
<div class="module-container mt-5 mb-5">
    <div class="module-header">
        <h2 class="module-title">Análisis Interno y Externo</h2>
        <p>Agrega y gestiona las Fortalezas, Debilidades, Oportunidades y Amenazas de tu empresa.</p>
    </div>
    <div class="module-content">
        <!-- Análisis Interno -->
        <div class="card card-minimal mb-4 w-100" style="max-width:900px;margin:auto;">
            <div class="card-body">
                <h4 class="card-title mb-3">Análisis Interno</h4>
                <form method="POST" class="row g-2 mb-3">
                    <div class="col-md-3">
                        <select name="tipo" class="form-select" required>
                            <option value="">Tipo</option>
                            <option value="fortaleza">Fortaleza</option>
                            <option value="debilidad">Debilidad</option>
                        </select>
                    </div>
                    <div class="col-md-7">
                        <input type="text" name="descripcion" class="form-control" placeholder="Describe el elemento" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-brand w-100">Agregar</button>
                    </div>
                </form>
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="card-title fortaleza">Fortalezas</h5>
                        <ul class="list-group list-group-flush">
                        <?php if (!empty($foda_items['fortaleza'])): foreach($foda_items['fortaleza'] as $item): ?>
                            <li class="list-group-item fortaleza d-flex justify-content-between align-items-center">
                                <?php echo htmlspecialchars($item['descripcion']); ?>
                                <form method="POST" style="display:inline">
                                    <input type="hidden" name="delete_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">&times;</button>
                                </form>
                            </li>
                        <?php endforeach; else: ?>
                            <li class="list-group-item text-muted">Sin registros</li>
                        <?php endif; ?>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h5 class="card-title debilidad">Debilidades</h5>
                        <ul class="list-group list-group-flush">
                        <?php if (!empty($foda_items['debilidad'])): foreach($foda_items['debilidad'] as $item): ?>
                            <li class="list-group-item debilidad d-flex justify-content-between align-items-center">
                                <?php echo htmlspecialchars($item['descripcion']); ?>
                                <form method="POST" style="display:inline">
                                    <input type="hidden" name="delete_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">&times;</button>
                                </form>
                            </li>
                        <?php endforeach; else: ?>
                            <li class="list-group-item text-muted">Sin registros</li>
                        <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!-- Análisis Externo -->
        <div class="card card-minimal mb-4 w-100" style="max-width:900px;margin:auto;">
            <div class="card-body">
                <h4 class="card-title mb-3">Análisis Externo</h4>
                <form method="POST" class="row g-2 mb-3">
                    <div class="col-md-3">
                        <select name="tipo" class="form-select" required>
                            <option value="">Tipo</option>
                            <option value="oportunidad">Oportunidad</option>
                            <option value="amenaza">Amenaza</option>
                        </select>
                    </div>
                    <div class="col-md-7">
                        <input type="text" name="descripcion" class="form-control" placeholder="Describe el elemento" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-brand w-100">Agregar</button>
                    </div>
                </form>
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="card-title oportunidad">Oportunidades</h5>
                        <ul class="list-group list-group-flush">
                        <?php if (!empty($foda_items['oportunidad'])): foreach($foda_items['oportunidad'] as $item): ?>
                            <li class="list-group-item oportunidad d-flex justify-content-between align-items-center">
                                <?php echo htmlspecialchars($item['descripcion']); ?>
                                <form method="POST" style="display:inline">
                                    <input type="hidden" name="delete_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">&times;</button>
                                </form>
                            </li>
                        <?php endforeach; else: ?>
                            <li class="list-group-item text-muted">Sin registros</li>
                        <?php endif; ?>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h5 class="card-title amenaza">Amenazas</h5>
                        <ul class="list-group list-group-flush">
                        <?php if (!empty($foda_items['amenaza'])): foreach($foda_items['amenaza'] as $item): ?>
                            <li class="list-group-item amenaza d-flex justify-content-between align-items-center">
                                <?php echo htmlspecialchars($item['descripcion']); ?>
                                <form method="POST" style="display:inline">
                                    <input type="hidden" name="delete_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">&times;</button>
                                </form>
                            </li>
                        <?php endforeach; else: ?>
                            <li class="list-group-item text-muted">Sin registros</li>
                        <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-between mt-4" style="max-width:900px;margin:auto;">
            <a href="analisis_info.php" class="btn btn-nav">&laquo; Atrás: Info. Análisis</a>
            <a href="dashboard.php" class="btn btn-nav-outline">Volver al Índice</a>
            <a href="cadena_valor.php" class="btn btn-save">Siguiente: Cadena de Valor &raquo;</a>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
