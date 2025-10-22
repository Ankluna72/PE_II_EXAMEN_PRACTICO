
<?php
session_start();
require_once 'includes/db_connection.php';
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
$id_usuario_actual = $_SESSION['id_usuario'];

// --- FUNCIONES AUXILIARES ---
function obtenerFODA($mysqli, $id_empresa) {
    $stmt = $mysqli->prepare("SELECT tipo, descripcion, posicion FROM foda WHERE id_empresa = ? ORDER BY tipo, posicion ASC");
    $stmt->bind_param("i", $id_empresa);
    $stmt->execute();
    $result = $stmt->get_result();
    $foda = [
        'debilidad' => [],
        'amenaza' => [],
        'fortaleza' => [],
        'oportunidad' => []
    ];
    while ($row = $result->fetch_assoc()) {
        $foda[$row['tipo']][] = $row['descripcion'];
    }
    $stmt->close();
    return $foda;
}

function obtenerPuntajes($mysqli, $id_empresa) {
    $stmt = $mysqli->prepare("SELECT * FROM foda_puntaje WHERE id_empresa = ?");
    $stmt->bind_param("i", $id_empresa);
    $stmt->execute();
    $result = $stmt->get_result();
    $puntajes = [];
    while ($row = $result->fetch_assoc()) {
        $puntajes[$row['tipo_relacion']] = $row;
    }
    $stmt->close();
    return $puntajes;
}

function guardarPuntajes($mysqli, $id_empresa, $puntajes) {
    foreach ($puntajes as $tipo_relacion => $valor) {
        $stmt = $mysqli->prepare("REPLACE INTO foda_puntaje (id_empresa, tipo_relacion, puntaje) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $id_empresa, $tipo_relacion, $valor);
        $stmt->execute();
        $stmt->close();
    }
}

// --- PROCESAR FORMULARIO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_puntajes'])) {
    $puntajes = [
        'FO' => intval($_POST['FO'] ?? 0),
        'AF' => intval($_POST['AF'] ?? 0),
        'AD' => intval($_POST['AD'] ?? 0),
        'OD' => intval($_POST['OD'] ?? 0)
    ];
    guardarPuntajes($mysqli, $id_empresa_actual, $puntajes);
    $mensaje = "Puntajes guardados correctamente.";
}

// --- OBTENER DATOS PARA INTERFAZ ---
$foda = obtenerFODA($mysqli, $id_empresa_actual);
$puntajes = obtenerPuntajes($mysqli, $id_empresa_actual);

?>
<link rel="stylesheet" href="css/modules.css">
<link rel="stylesheet" href="css/style.css">
<div class="container" style="max-width:1100px;">
    <div class="module-container mt-5 mb-5">
        <div class="module-header">
            <h2 class="module-title">Identificación de Estrategia del Plan Estratégico</h2>
        </div>
        <div class="module-content">
            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-success"><?php echo $mensaje; ?></div>
            <?php endif; ?>
            <form method="POST" class="mb-4">
                <h4 class="section-title mb-3">Matriz FODA</h4>
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card-minimal mb-4">
                            <div class="card-body">
                                <div class="d-flex flex-column gap-2 mb-2">
                                    <span class="fw-bold" style="color:#18b36b;font-size:1.15rem;">Debilidades</span>
                                    <span class="fw-bold" style="color:#0fd2ff;font-size:1.15rem;">Amenazas</span>
                                </div>
                                <ul class="list-group mb-3">
                                    <?php foreach ($foda['debilidad'] as $d): ?>
                                        <li class="list-group-item"> <?php echo htmlspecialchars($d); ?> </li>
                                    <?php endforeach; ?>
                                </ul>
                                <ul class="list-group mb-3">
                                    <?php foreach ($foda['amenaza'] as $a): ?>
                                        <li class="list-group-item"> <?php echo htmlspecialchars($a); ?> </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card-minimal mb-4">
                            <div class="card-body">
                                <div class="d-flex flex-column gap-2 mb-2">
                                    <span class="fw-bold" style="color:#007bff;font-size:1.15rem;">Fortalezas</span>
                                    <span class="fw-bold" style="color:#ffc107;font-size:1.15rem;">Oportunidades</span>
                                </div>
                                <ul class="list-group mb-3">
                                    <?php foreach ($foda['fortaleza'] as $f): ?>
                                        <li class="list-group-item"> <?php echo htmlspecialchars($f); ?> </li>
                                    <?php endforeach; ?>
                                </ul>
                                <ul class="list-group mb-3">
                                    <?php foreach ($foda['oportunidad'] as $o): ?>
                                        <li class="list-group-item"> <?php echo htmlspecialchars($o); ?> </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <h4 class="section-title mt-4 mb-3">Puntaje de Estrategias</h4>
                <div class="table-responsive">
                    <table class="table table-bordered text-center card-minimal">
                        <thead class="table-light">
                            <tr>
                                <th>Relaciones</th>
                                <th>Tipología de estrategia</th>
                                <th>Puntaje</th>
                                <th>Descripción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="pill">FO</span></td>
                                <td>Estrategia Ofensiva</td>
                                <td><input type="number" name="FO" min="0" max="100" value="<?php echo $puntajes['FO']['puntaje'] ?? 0; ?>" class="form-control" required></td>
                                <td>Deberá adoptar estrategias de crecimiento</td>
                            </tr>
                            <tr>
                                <td><span class="pill">AF</span></td>
                                <td>Estrategia Defensiva</td>
                                <td><input type="number" name="AF" min="0" max="100" value="<?php echo $puntajes['AF']['puntaje'] ?? 0; ?>" class="form-control" required></td>
                                <td>La empresa está preparada para enfrentarse a las amenazas</td>
                            </tr>
                            <tr>
                                <td><span class="pill">AD</span></td>
                                <td>Estrategia de Supervivencia</td>
                                <td><input type="number" name="AD" min="0" max="100" value="<?php echo $puntajes['AD']['puntaje'] ?? 0; ?>" class="form-control" required></td>
                                <td>Se enfrenta a amenazas externas sin las fortalezas necesarias para luchar con la competencia</td>
                            </tr>
                            <tr>
                                <td><span class="pill">OD</span></td>
                                <td>Estrategia de Reorientación</td>
                                <td><input type="number" name="OD" min="0" max="100" value="<?php echo $puntajes['OD']['puntaje'] ?? 0; ?>" class="form-control" required></td>
                                <td>No puede aprovechar las oportunidades porque carece de preparación adecuada</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex mt-4">
                    <button type="submit" name="guardar_puntajes" class="btn btn-brand me-2">Guardar puntajes</button>
                    <a href="?ver_resultados=1" class="btn btn-outline-brand">Ver resultados</a>
                </div>
            </form>

            <?php if (isset($_GET['ver_resultados'])): ?>
                <div class="card-minimal p-4 mt-4">
                    <h4 class="section-title mb-3">Síntesis de Resultados</h4>
                    <table class="table table-bordered text-center">
                        <thead class="table-light">
                            <tr>
                                <th>Relaciones</th>
                                <th>Tipología de estrategia</th>
                                <th>Puntaje</th>
                                <th>Descripción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="pill">FO</span></td>
                                <td>Estrategia Ofensiva</td>
                                <td><?php echo $puntajes['FO']['puntaje'] ?? 0; ?></td>
                                <td>Deberá adoptar estrategias de crecimiento</td>
                            </tr>
                            <tr>
                                <td><span class="pill">AF</span></td>
                                <td>Estrategia Defensiva</td>
                                <td><?php echo $puntajes['AF']['puntaje'] ?? 0; ?></td>
                                <td>La empresa está preparada para enfrentarse a las amenazas</td>
                            </tr>
                            <tr>
                                <td><span class="pill">AD</span></td>
                                <td>Estrategia de Supervivencia</td>
                                <td><?php echo $puntajes['AD']['puntaje'] ?? 0; ?></td>
                                <td>Se enfrenta a amenazas externas sin las fortalezas necesarias para luchar con la competencia</td>
                            </tr>
                            <tr>
                                <td><span class="pill">OD</span></td>
                                <td>Estrategia de Reorientación</td>
                                <td><?php echo $puntajes['OD']['puntaje'] ?? 0; ?></td>
                                <td>No puede aprovechar las oportunidades porque carece de preparación adecuada</td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="mt-3">La puntuación mayor le indica la estrategia que deberá llevar a cabo.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
