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

$pageStyles = ['css/modules.css', 'css/objetivos.css'];
require_once 'includes/db_connection.php';
require_once 'includes/header.php';

$id_empresa_actual = $_SESSION['id_empresa_actual'];
$mensaje = '';

// Manejar mensajes de la sesión (patrón PRG)
if (isset($_SESSION['mensaje_objetivos'])) {
    $mensaje = $_SESSION['mensaje_objetivos'];
    unset($_SESSION['mensaje_objetivos']);
}

// Manejar la lógica de POST para añadir, eliminar o actualizar objetivos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Guardar Unidades Estratégicas
    if (isset($_POST['guardar_uen'])) {
        $unidades_estrategicas = trim($_POST['unidades_estrategicas']);
        $stmt = $mysqli->prepare("UPDATE empresa SET unidades_estrategicas = ? WHERE id = ?");
        $stmt->bind_param("si", $unidades_estrategicas, $id_empresa_actual);
        if ($stmt->execute()) {
            $_SESSION['mensaje_objetivos'] = '<div class="alert alert-success alert-success-auto">Unidades Estratégicas guardadas correctamente.</div>';
        } else {
            $_SESSION['mensaje_objetivos'] = '<div class="alert alert-danger">Error al guardar las Unidades Estratégicas.</div>';
        }
        $stmt->close();
        header('Location: objetivos.php');
        exit();
    }

    // Añadir un nuevo objetivo GENERAL
    if (isset($_POST['add_general'])) {
        $descripcion = trim($_POST['descripcion_general']);
        if (!empty($descripcion)) {
            $stmt = $mysqli->prepare("INSERT INTO objetivos_estrategicos (id_empresa, descripcion, tipo) VALUES (?, ?, 'general')");
            $stmt->bind_param("is", $id_empresa_actual, $descripcion);
            if ($stmt->execute()) {
                $_SESSION['mensaje_objetivos'] = '<div class="alert alert-success alert-success-auto">Objetivo general añadido correctamente.</div>';
            } else {
                $_SESSION['mensaje_objetivos'] = '<div class="alert alert-danger">Error al añadir el objetivo general.</div>';
            }
            $stmt->close();
        }
    }

    // Añadir un nuevo objetivo ESPECÍFICO
    if (isset($_POST['add_especifico'])) {
        $descripcion = trim($_POST['descripcion_especifico']);
        $id_padre = $_POST['id_padre'];
        if (!empty($descripcion) && !empty($id_padre)) {
            $stmt = $mysqli->prepare("INSERT INTO objetivos_estrategicos (id_empresa, descripcion, tipo, id_padre) VALUES (?, ?, 'especifico', ?)");
            $stmt->bind_param("isi", $id_empresa_actual, $descripcion, $id_padre);
            if ($stmt->execute()) {
                $_SESSION['mensaje_objetivos'] = '<div class="alert alert-success alert-success-auto">Objetivo específico añadido correctamente.</div>';
            } else {
                $_SESSION['mensaje_objetivos'] = '<div class="alert alert-danger">Error al añadir el objetivo específico.</div>';
            }
            $stmt->close();
        }
    }

    // Eliminar un objetivo
    if (isset($_POST['delete_objetivo'])) {
        $id_objetivo = $_POST['id_objetivo'];
        $stmt = $mysqli->prepare("DELETE FROM objetivos_estrategicos WHERE id = ? AND id_empresa = ?");
        $stmt->bind_param("ii", $id_objetivo, $id_empresa_actual);
        if ($stmt->execute()) {
            $_SESSION['mensaje_objetivos'] = '<div class="alert alert-info alert-success-auto">Objetivo eliminado correctamente.</div>';
        } else {
            $_SESSION['mensaje_objetivos'] = '<div class="alert alert-danger">Error al eliminar el objetivo.</div>';
        }
        $stmt->close();
    }

    // Actualizar un objetivo
    if (isset($_POST['update_objetivo'])) {
        $id_objetivo = $_POST['id_objetivo'];
        $descripcion = trim($_POST['descripcion']);
        if (!empty($descripcion)) {
            $stmt = $mysqli->prepare("UPDATE objetivos_estrategicos SET descripcion = ? WHERE id = ? AND id_empresa = ?");
            $stmt->bind_param("sii", $descripcion, $id_objetivo, $id_empresa_actual);
            if ($stmt->execute()) {
                $_SESSION['mensaje_objetivos'] = '<div class="alert alert-success alert-success-auto">Objetivo actualizado correctamente.</div>';
            } else {
                $_SESSION['mensaje_objetivos'] = '<div class="alert alert-danger">Error al actualizar el objetivo.</div>';
            }
            $stmt->close();
        }
    }
    
    // Redireccionar para evitar reenvío de POST
    header('Location: objetivos.php');
    exit();
}

// Obtener todos los objetivos de la empresa y organizarlos
$objetivos = [];
$stmt = $mysqli->prepare("SELECT id, descripcion, tipo, id_padre FROM objetivos_estrategicos WHERE id_empresa = ? ORDER BY id_padre ASC, id ASC");
$stmt->bind_param("i", $id_empresa_actual);
$stmt->execute();
$resultado = $stmt->get_result();

$objetivos_generales = [];
$objetivos_especificos = [];

while ($row = $resultado->fetch_assoc()) {
    if ($row['tipo'] == 'general') {
        $objetivos_generales[$row['id']] = $row;
        $objetivos_generales[$row['id']]['especificos'] = [];
    } else {
        $objetivos_especificos[] = $row;
    }
}

foreach ($objetivos_especificos as $especifico) {
    if (isset($objetivos_generales[$especifico['id_padre']])) {
        $objetivos_generales[$especifico['id_padre']]['especificos'][] = $especifico;
    }
}
$stmt->close();

?>
<!-- Estilos movidos a css/objetivos.css -->

<div class="container mt-4">
    <div class="module-container">
        <div class="module-header">
            <h2 class="module-title">4. OBJETIVOS ESTRATÉGICOS</h2>
        </div>
        <div class="module-content">
            <?php echo $mensaje; ?>

<!-- Introducción sobre los Objetivos Estratégicos -->
<!-- Estilos movidos a css/objetivos.css -->
<!-- Estilos movidos a css/objetivos.css -->




<div class="intro-card">
    <div class="intro-header">
        <h4 class="mb-0">Introducción a los Objetivos Estratégicos</h4>
    </div>
    <div class="intro-body">
        <p>El siguiente paso es establecer los objetivos de una empresa en relación al sector al que pertenece.</p>

        <p><strong>Un OBJETIVO ESTRATÉGICO</strong> es un fin deseado, clave para la organización y para la consecución de su visión. 
        Para una correcta planificación construya los objetivos formando una pirámide. 
        Los objetivos de cada nivel indican qué es lo que quiere lograrse, siendo la estructura de objetivos que está en el nivel 
        inmediatamente inferior la que indica el <em>cómo</em>. Por tanto, cada objetivo es un fin en sí mismo, pero también a la vez un medio 
        para el logro de los objetivos del nivel superior.</p>

        <!-- Pirámide de objetivos -->
        <div class="text-center">
            <div class="pyramid">
                <div class="pyramid-level level-top">Misión, visión y valores</div>
                <div class="pyramid-level level-middle">Objetivos estratégicos o generales</div>
                <div class="pyramid-level level-bottom">Objetivos específicos</div>
            </div>
            <p class="text-muted"><small>Figura: Estructura jerárquica de los objetivos empresariales.</small></p>
        </div>

        <div class="subsection">
            <h5>Tipos de Objetivos</h5>
            <ul>
                <li><strong>Objetivos estratégicos:</strong> Concretan el contenido de la misión. Suelen referirse al crecimiento, rentabilidad y sostenibilidad de la empresa. Su horizonte es de 3 a 5 años.</li>
                <li><strong>Objetivos operativos:</strong> Son la concreción anual de los objetivos estratégicos. Han de ser claros, concisos y medibles.</li>
            </ul>

            <p>Se pueden distinguir dos tipos de objetivos específicos:</p>
            <ol>
                <li><strong>Funcionales:</strong> formulados por áreas o departamentos.</li>
                <li><strong>Operativos:</strong> centrados en operaciones y acciones concretas.</li>
            </ol>
        </div>

        <div class="subsection">
            <h5>Atributos de los Objetivos (Regla M.E.T.A.S.)</h5>
            <table class="metas-table">
                <tr><th>M</th><td><strong>MEDIBLES:</strong> que se les pueda asignar indicadores cuantitativos</td></tr>
                <tr><th>E</th><td><strong>ESPECÍFICOS:</strong> que sean enunciados de forma clara, breve y comprensible</td></tr>
                <tr><th>T</th><td><strong>TRAZABLES:</strong> que permita un registro de seguimiento y control</td></tr>
                <tr><th>A</th><td><strong>ALCANZABLES:</strong> realistas y motivadores</td></tr>
                <tr><th>S</th><td><strong>SENSATOS:</strong> lógicos y consecuentes con los recursos disponibles</td></tr>
            </table>
        </div>

        <div class="subsection">
            <h5>Ejemplos de Objetivos</h5>
            <ul class="example-list">
                <li>Alcanzar los niveles de ventas previstos para los nuevos productos.</li>
                <li>Reducir la rotación del personal del almacén.</li>
                <li>Reducir el plazo de cobro de los clientes.</li>
                <li>Reducir la siniestralidad al nivel fijado.</li>
                <li>Alcanzar los objetivos de beneficios previstos.</li>
                <li>Mejorar la calidad de entrega de los productos en el plazo previsto.</li>
            </ul>
        </div>

        <div class="subsection">
            <h5>Unidades Estratégicas de Negocio (UEN)</h5>
            <p>En empresas de gran tamaño, se pueden formular los objetivos estratégicos en función de sus diferentes <strong>Unidades Estratégicas de Negocio (UEN)</strong>. 
            Estas se hacen especialmente necesarias en empresas diversificadas o con multiactividad, donde la heterogeneidad de los distintos negocios 
            hace inviable un tratamiento estratégico conjunto.</p>

            <p>Se entiende por <strong>Unidad Estratégica de Negocio (UEN)</strong> un conjunto homogéneo de actividades o negocios, desde el punto de vista estratégico, 
            para el cual es posible formular una estrategia común y diferente a la de otras unidades. La estrategia de cada unidad es autónoma, 
            pero se integra en la estrategia general de la empresa.</p>

            <h6>¿Cómo podemos identificar a las UEN?</h6>
            <ul>
                <li><strong>Grupos de clientes:</strong> tipo de clientela a la que va destinado el producto o servicio.</li>
                <li><strong>Funciones:</strong> necesidades cubiertas por el producto o servicio.</li>
                <li><strong>Tecnología:</strong> forma en la cual la empresa cubre la necesidad del cliente a través del producto o servicio.</li>
            </ul>
        </div>
    </div>
</div>

            <!-- Formulario para Unidades Estratégicas -->
            <div class="uen-form mb-4">
                <h4><i class="fas fa-sitemap me-2"></i>UNIDADES ESTRATÉGICAS</h4>
                <p>En su caso, comente en este apartado las distintas UEN que tiene su empresa:</p>
                <form method="POST" action="">
                    <div class="form-group">
                        <textarea class="form-control" name="unidades_estrategicas" rows="5"><?php 
                            // Obtener las unidades estratégicas de la empresa
                            $stmt = $mysqli->prepare("SELECT unidades_estrategicas FROM empresa WHERE id = ?");
                            $stmt->bind_param("i", $id_empresa_actual);
                            $stmt->execute();
                            $stmt->bind_result($unidades_estrategicas);
                            $stmt->fetch();
                            $stmt->close();
                            echo htmlspecialchars($unidades_estrategicas ?? '');
                        ?></textarea>
                    </div>
                    <div class="uen-example">
                        <p><strong>Ejemplo:</strong> IBM se enfoca en mejorar las experiencias de los consumidores y optimizar la rentabilidad para empresas, utilizando nuevas tecnologías. La atención al cliente es una función clave en IBM, gestionando las relaciones con los clientes durante el proceso de compra. IBM proporciona productos y servicios para ayudar a los clientes con consultas y problemas. Siendo los clientes, grandes empresas y corporaciones que utilizan sus servicios y tecnologías para la transformación digital y la mejora de sus operaciones</p>
                    </div>
                    <button type="submit" name="guardar_uen" class="btn btn-save mt-3"><i class="fas fa-save me-2"></i>Guardar Unidades Estratégicas</button>
                </form>
            </div>

            <!-- Formulario para añadir Objetivo General -->
            <div class="card mb-4">
                <div class="card-header bg-gradient-primary text-white">
                    <i class="fas fa-plus-circle me-2"></i> Añadir Nuevo Objetivo General
                </div>
                <div class="card-body">
                    <form action="objetivos.php" method="POST">
                        <div class="mb-3">
                            <textarea class="form-control" name="descripcion_general" rows="3" placeholder="Describe aquí el objetivo general o estratégico..."></textarea>
                        </div>
                        <button type="submit" name="add_general" class="btn btn-save">Guardar Objetivo General</button>
                    </form>
                </div>
            </div>

            <!-- Lista de Objetivos -->
            <div class="card mb-4">
                <div class="card-header bg-gradient-primary text-white">
                    <i class="fas fa-bullseye me-2"></i> Mis Objetivos
                </div>
                <div class="card-body">
                    <div id="lista-objetivos">
                        <?php if (empty($objetivos_generales)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i> Aún no has añadido ningún objetivo general.
                            </div>
                        <?php else: ?>
                            <?php foreach ($objetivos_generales as $general): ?>
                                <div class="objetivo-general-card mb-4">
                                    <div class="objetivo-general-header bg-light p-3 rounded">
                                        <div class="objetivo-descripcion" id="descripcion-general-<?php echo $general['id']; ?>">
                                            <h5 class="objetivo-general-title mb-0">
                                                <i class="fas fa-flag me-2 text-primary"></i>
                                                <?php echo htmlspecialchars($general['descripcion']); ?>
                                            </h5>
                                        </div>
                                        <div class="objetivo-actions">
                                            <button class="btn btn-sm btn-outline-primary btn-edit" data-id="<?php echo $general['id']; ?>" data-tipo="general">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="objetivos.php" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este objetivo general y todos sus objetivos específicos asociados?');" style="display: inline;">
                                                <input type="hidden" name="id_objetivo" value="<?php echo $general['id']; ?>">
                                                <button type="submit" name="delete_objetivo" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    
                                    <div class="card mt-3">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0"><i class="fas fa-tasks me-2 text-secondary"></i> Objetivos Específicos</h6>
                                        </div>
                                        <div class="card-body">
                                            <?php if (empty($general['especificos'])): ?>
                                                <p class="text-muted"><small><i class="fas fa-info-circle me-1"></i> Aún no hay objetivos específicos para este objetivo general.</small></p>
                                            <?php else: ?>
                                                <div class="table-responsive">
                                                    <table class="table table-hover table-striped">
                                                        <tbody>
                                                            <?php foreach ($general['especificos'] as $especifico): ?>
                                                                <tr>
                                                                    <td width="80%">
                                                                        <span class="objetivo-descripcion" id="descripcion-especifico-<?php echo $especifico['id']; ?>">
                                                                            <i class="fas fa-angle-right me-2 text-secondary"></i>
                                                                            <?php echo htmlspecialchars($especifico['descripcion']); ?>
                                                                        </span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <div class="objetivo-actions">
                                                                            <button class="btn btn-sm btn-outline-primary btn-edit" data-id="<?php echo $especifico['id']; ?>" data-tipo="especifico">
                                                                                <i class="fas fa-edit"></i>
                                                                            </button>
                                                                            <form action="objetivos.php" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este objetivo específico?');" style="display: inline;">
                                                                                <input type="hidden" name="id_objetivo" value="<?php echo $especifico['id']; ?>">
                                                                                <button type="submit" name="delete_objetivo" class="btn btn-danger btn-sm">
                                                                                    <i class="fas fa-trash"></i>
                                                                                </button>
                                                                            </form>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <!-- Formulario para añadir Objetivo Específico -->
                                            <form action="objetivos.php" method="POST" class="form-add-especifico mt-3">
                                                <div class="input-group">
                                                    <input type="hidden" name="id_padre" value="<?php echo $general['id']; ?>">
                                                    <input type="text" class="form-control" name="descripcion_especifico" placeholder="Añadir un objetivo específico..." required>
                                                    <button type="submit" name="add_especifico" class="btn btn-save">
                                                        <i class="fas fa-plus me-1"></i> Añadir
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
             <div class="d-flex justify-content-between mt-4">
                <a href="valores.php" class="btn btn-nav">&laquo; Anterior: Valores</a>
                <a href="dashboard.php" class="btn btn-nav-outline">Volver al Índice</a>
                <a href="analisis_info.php" class="btn btn-save">Siguiente: Análisis Interno y Externo &raquo;</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.btn-edit');

    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            const tipo = this.dataset.tipo;
            const descripcionElement = document.getElementById(`descripcion-${tipo}-${id}`);
            const currentDescription = descripcionElement.innerText;

            const input = document.createElement('input');
            input.type = 'text';
            input.className = 'form-control';
            input.value = currentDescription;

            const saveButton = document.createElement('button');
            saveButton.className = 'btn btn-sm btn-outline-success';
            saveButton.innerHTML = '<i class="fas fa-save"></i>';

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'objetivos.php';
            form.style.display = 'inline';

            const hiddenId = document.createElement('input');
            hiddenId.type = 'hidden';
            hiddenId.name = 'id_objetivo';
            hiddenId.value = id;

            const hiddenDesc = document.createElement('input');
            hiddenDesc.type = 'hidden';
            hiddenDesc.name = 'descripcion';

            const hiddenUpdate = document.createElement('input');
            hiddenUpdate.type = 'hidden';
            hiddenUpdate.name = 'update_objetivo';
            hiddenUpdate.value = '1';

            form.appendChild(hiddenId);
            form.appendChild(hiddenDesc);
            form.appendChild(hiddenUpdate);
            form.appendChild(saveButton);

            descripcionElement.innerHTML = '';
            descripcionElement.appendChild(input);
            
            const actionsContainer = this.parentElement;
            actionsContainer.innerHTML = '';
            actionsContainer.appendChild(form);

            saveButton.addEventListener('click', function(e) {
                e.preventDefault();
                hiddenDesc.value = input.value;
                form.submit();
            });
        });
    });
});
</script>

<?php if (strpos($mensaje, 'alert-success') !== false || strpos($mensaje, 'alert-info') !== false): ?>
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