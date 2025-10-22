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
$id_usuario_actual = $_SESSION['id_usuario'];
$mensaje = '';

// Helper: detectar columnas en la BD (para compatibilidad de esquemas)
function tableHasColumn($mysqli, $table, $column) {
    $res = $mysqli->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    return $res && $res->num_rows > 0;
}

$hasOrigenCol = tableHasColumn($mysqli, 'foda', 'origen');
$hasFechaCol = tableHasColumn($mysqli, 'foda', 'fecha_creacion');
$hasUserCol = tableHasColumn($mysqli, 'foda', 'id_usuario');
$hasPosCol = tableHasColumn($mysqli, 'foda', 'posicion');

// Array con las 25 preguntas del autodiagnóstico
$preguntas = [
    1 => "La empresa tiene una política sistematizada de cero defectos en la producción de productos/servicios.",
    2 => "La empresa emplea los medios productivos tecnológicamente más avanzados de su sector.",
    3 => "La empresa dispone de un sistema de información y control de gestión eficiente y eficaz.",
    4 => "Los medios técnicos y tecnológicos de la empresa están preparados para competir en un futuro a corto, medio y largo plazo.",
    5 => "La empresa es un referente en su sector en I+D+i.",
    6 => "La excelencia de los procedimientos de la empresa (en ISO, etc.) son una principal fuente de ventaja competitiva.",
    7 => "La empresa dispone de página web, y esta se emplea no sólo como escaparate virtual de productos/servicios, sino también para establecer relaciones con clientes y proveedores.",
    8 => "Los productos/servicios que desarrolla nuestra empresa llevan incorporada una tecnología difícil de imitar.",
    9 => "La empresa es referente en su sector en la optimización, en términos de coste, de su cadena de producción, siendo ésta una de sus principales ventajas competitivas.",
    10 => "La informatización de la empresa es una fuente de ventaja competitiva clara respecto a sus competidores.",
    11 => "Los canales de distribución de la empresa son una importante fuente de ventajas competitivas.",
    12 => "Los productos/servicios de la empresa son altamente, y diferencialmente, valorados por el cliente respecto a nuestros competidores.",
    13 => "La empresa dispone y ejecuta un sistemático plan de marketing y ventas.",
    14 => "La empresa tiene optimizada su gestión financiera.",
    15 => "La empresa busca continuamente el mejorar la relación con sus clientes cortando los plazos de ejecución, personalizando la oferta o mejorando las condiciones de entrega. Pero siempre partiendo de un plan previo.",
    16 => "La empresa es referente en su sector en el lanzamiento de innovadores productos y servicio de éxito demostrado en el mercado.",
    17 => "Los Recursos Humanos son especialmente responsables del éxito de la empresa, considerándolos incluso como el principal activo estratégico.",
    18 => "Se tiene una plantilla altamente motivada, que conoce con claridad las metas, objetivos y estrategias de la organización.",
    19 => "La empresa siempre trabaja conforme a una estrategia y objetivos claros.",
    20 => "La gestión del circulante está optimizada.",
    21 => "Se tiene definido claramente el posicionamiento estratégico de todos los productos de la empresa.",
    22 => "Se dispone de una política de marca basada en la reputación que la empresa genera, en la gestión de relación con el cliente y en el posicionamiento estratégico previamente definido.",
    23 => "La cartera de clientes de nuestra empresa está altamente fidelizada, ya que tenemos como principal propósito el deleitarlos día a día.",
    24 => "Nuestra política y equipo de ventas y marketing es una importante ventaja competitiva de nuestra empresa respecto al sector.",
    25 => "El servicio al cliente que prestamos es uno de nuestras principales ventajas competitivas respecto a nuestros competidores."
];

// Inicializar valores de sesión para cadena de valor
if (!isset($_SESSION['cadena_valor'])) {
    $_SESSION['cadena_valor'] = [];
}
$data = &$_SESSION['cadena_valor'];

// ---- LÓGICA PARA GUARDAR DATOS ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar si es guardado solo de FODA
    $soloFODA = isset($_POST['guardar_foda']) && $_POST['guardar_foda'] === '1';
    
    if (!$soloFODA) {
        // Actualizar datos en sesión solo si no es solo FODA
        $data['respuestas'] = [];
        foreach ($preguntas as $num => $texto) {
            if (isset($_POST['q' . $num])) {
                $data['respuestas'][$num] = intval($_POST['q' . $num]);
            }
        }
    }
    
    // Guardar fortalezas y debilidades en sesión
    $data['foda'] = [];
    if(isset($_POST['fortalezas']) && is_array($_POST['fortalezas'])) {
        $data['foda']['fortalezas'] = array_filter(array_map('trim', $_POST['fortalezas']));
    }
    if(isset($_POST['debilidades']) && is_array($_POST['debilidades'])) {
        $data['foda']['debilidades'] = array_filter(array_map('trim', $_POST['debilidades']));
    }

    // Guardar en base de datos
    $mysqli->begin_transaction();
    try {
        // Guardar respuestas del autodiagnóstico solo si no es solo FODA
        if (!$soloFODA) {
            $stmt_delete = $mysqli->prepare("DELETE FROM cadena_valor_respuestas WHERE id_empresa = ?");
            $stmt_delete->bind_param("i", $id_empresa_actual);
            $stmt_delete->execute();
            $stmt_delete->close();

            if (!empty($data['respuestas'])) {
                $stmt_insert = $mysqli->prepare("INSERT INTO cadena_valor_respuestas (id_empresa, pregunta_num, respuesta_valor) VALUES (?, ?, ?)");
                foreach ($data['respuestas'] as $num => $valor) {
                    $stmt_insert->bind_param("iii", $id_empresa_actual, $num, $valor);
                    $stmt_insert->execute();
                }
                $stmt_insert->close();
            }
        }

        // Limpiar FODA anterior de cadena de valor SOLO si existe columna 'origen' y hay nuevas entradas
        if ($hasOrigenCol && (!empty($data['foda']['fortalezas']) || !empty($data['foda']['debilidades']))) {
            if ($hasUserCol) {
                $stmt_delete_foda = $mysqli->prepare("DELETE FROM foda WHERE id_empresa = ? AND id_usuario = ? AND origen = 'cadena_valor'");
                $stmt_delete_foda->bind_param("ii", $id_empresa_actual, $id_usuario_actual);
            } else {
                $stmt_delete_foda = $mysqli->prepare("DELETE FROM foda WHERE id_empresa = ? AND origen = 'cadena_valor'");
                $stmt_delete_foda->bind_param("i", $id_empresa_actual);
            }
            $stmt_delete_foda->execute();
            $stmt_delete_foda->close();
        }

        // Guardar fortalezas y debilidades en la tabla FODA
        if (!empty($data['foda']['fortalezas']) || !empty($data['foda']['debilidades'])) {
            if ($hasOrigenCol && $hasPosCol) {
                if ($hasUserCol) {
                    $stmt_foda = $mysqli->prepare("INSERT INTO foda (id_empresa, id_usuario, tipo, descripcion, origen, posicion) VALUES (?, ?, ?, ?, 'cadena_valor', ?)");
                } else {
                    $stmt_foda = $mysqli->prepare("INSERT INTO foda (id_empresa, tipo, descripcion, origen, posicion) VALUES (?, ?, ?, 'cadena_valor', ?)");
                }
            } elseif ($hasPosCol) {
                if ($hasUserCol) {
                    $stmt_foda = $mysqli->prepare("INSERT INTO foda (id_empresa, id_usuario, tipo, descripcion, posicion) VALUES (?, ?, ?, ?, ?)");
                } else {
                    $stmt_foda = $mysqli->prepare("INSERT INTO foda (id_empresa, tipo, descripcion, posicion) VALUES (?, ?, ?, ?)");
                }
            } else {
                if ($hasOrigenCol) {
                    if ($hasUserCol) {
                        $stmt_foda = $mysqli->prepare("INSERT INTO foda (id_empresa, id_usuario, tipo, descripcion, origen) VALUES (?, ?, ?, ?, 'cadena_valor')");
                    } else {
                        $stmt_foda = $mysqli->prepare("INSERT INTO foda (id_empresa, tipo, descripcion, origen) VALUES (?, ?, ?, 'cadena_valor')");
                    }
                } else {
                    if ($hasUserCol) {
                        $stmt_foda = $mysqli->prepare("INSERT INTO foda (id_empresa, id_usuario, tipo, descripcion) VALUES (?, ?, ?, ?)");
                    } else {
                        // Fallback sin columna 'origen'
                        $stmt_foda = $mysqli->prepare("INSERT INTO foda (id_empresa, tipo, descripcion) VALUES (?, ?, ?)");
                    }
                }
            }

            // Fortalezas
            if (!empty($data['foda']['fortalezas'])) {
                $tipo = 'fortaleza';
                $pos = 1;
                foreach($data['foda']['fortalezas'] as $f) {
                    if(!empty($f)) {
                        if ($hasPosCol && $hasUserCol) {
                            $stmt_foda->bind_param("iissi", $id_empresa_actual, $id_usuario_actual, $tipo, $f, $pos);
                        } elseif ($hasPosCol && !$hasUserCol) {
                            $stmt_foda->bind_param("issi", $id_empresa_actual, $tipo, $f, $pos);
                        } elseif (!$hasPosCol && $hasUserCol) {
                            $stmt_foda->bind_param("iiss", $id_empresa_actual, $id_usuario_actual, $tipo, $f);
                        } else {
                            $stmt_foda->bind_param("iss", $id_empresa_actual, $tipo, $f);
                        }
                        $stmt_foda->execute();
                        $pos++;
                    }
                }
            }
            // Debilidades
            if (!empty($data['foda']['debilidades'])) {
                $tipo = 'debilidad';
                $pos = 1;
                foreach($data['foda']['debilidades'] as $d) {
                    if(!empty($d)) {
                        if ($hasPosCol && $hasUserCol) {
                            $stmt_foda->bind_param("iissi", $id_empresa_actual, $id_usuario_actual, $tipo, $d, $pos);
                        } elseif ($hasPosCol && !$hasUserCol) {
                            $stmt_foda->bind_param("issi", $id_empresa_actual, $tipo, $d, $pos);
                        } elseif (!$hasPosCol && $hasUserCol) {
                            $stmt_foda->bind_param("iiss", $id_empresa_actual, $id_usuario_actual, $tipo, $d);
                        } else {
                            $stmt_foda->bind_param("iss", $id_empresa_actual, $tipo, $d);
                        }
                        $stmt_foda->execute();
                        $pos++;
                    }
                }
            }
            $stmt_foda->close();
        }

        $mysqli->commit();
        if ($soloFODA) {
            $mensaje = '<div class="alert alert-success">Datos FODA guardados correctamente.</div>';
        } else {
            $mensaje = '<div class="alert alert-success">Diagnóstico y reflexiones guardadas correctamente.</div>';
        }
    } catch (mysqli_sql_exception $exception) {
        $mysqli->rollback();
        $mensaje = '<div class="alert alert-danger">Error al guardar los datos: ' . $exception->getMessage() . '</div>';
    }
}

// --- LÓGICA PARA CARGAR DATOS EXISTENTES ---
// Cargar SIEMPRE las respuestas desde BD para que persistan entre recargas
$respuestas_tmp = [];
$stmt_select = $mysqli->prepare("SELECT pregunta_num, respuesta_valor FROM cadena_valor_respuestas WHERE id_empresa = ?");
$stmt_select->bind_param("i", $id_empresa_actual);
$stmt_select->execute();
$resultado = $stmt_select->get_result();
while ($fila = $resultado->fetch_assoc()) {
    $respuestas_tmp[$fila['pregunta_num']] = $fila['respuesta_valor'];
}
$stmt_select->close();
if (!empty($respuestas_tmp)) {
    $data['respuestas'] = $respuestas_tmp;
}
// removed duplicate $stmt_select->close();

// Cargar SIEMPRE FODA (solo fortalezas y debilidades)
$foda_tmp = ['fortalezas' => [], 'debilidades' => []];
if ($hasOrigenCol) {
    // Si existe 'origen', filtramos por cadena_valor
    $orderCol = $hasFechaCol ? "fecha_creacion" : "id";
    if ($hasUserCol) {
        $stmt_foda = $mysqli->prepare("SELECT tipo, descripcion FROM foda WHERE id_empresa = ? AND id_usuario = ? AND origen = 'cadena_valor' ORDER BY $orderCol");
        $stmt_foda->bind_param("ii", $id_empresa_actual, $id_usuario_actual);
    } else {
        $stmt_foda = $mysqli->prepare("SELECT tipo, descripcion FROM foda WHERE id_empresa = ? AND origen = 'cadena_valor' ORDER BY $orderCol");
        $stmt_foda->bind_param("i", $id_empresa_actual);
    }
} else {
    // Fallback: sin 'origen', traemos fortalezas y debilidades de la empresa
    if ($hasUserCol) {
        $stmt_foda = $mysqli->prepare("SELECT tipo, descripcion FROM foda WHERE id_empresa = ? AND id_usuario = ? AND (tipo='fortaleza' OR tipo='debilidad') ORDER BY id");
        $stmt_foda->bind_param("ii", $id_empresa_actual, $id_usuario_actual);
    } else {
        $stmt_foda = $mysqli->prepare("SELECT tipo, descripcion FROM foda WHERE id_empresa = ? AND (tipo='fortaleza' OR tipo='debilidad') ORDER BY id");
        $stmt_foda->bind_param("i", $id_empresa_actual);
    }
}
$stmt_foda->execute();
$resultado_foda = $stmt_foda->get_result();
while ($fila = $resultado_foda->fetch_assoc()) {
    if ($fila['tipo'] == 'fortaleza') {
        $foda_tmp['fortalezas'][] = $fila['descripcion'];
    } elseif ($fila['tipo'] == 'debilidad') {
        $foda_tmp['debilidades'][] = $fila['descripcion'];
    }
}
$stmt_foda->close();
if (!empty($foda_tmp['fortalezas']) || !empty($foda_tmp['debilidades'])) {
    $data['foda'] = $foda_tmp;
}

// Calcular resultado para mostrarlo si ya está guardado
$potencial_mejora = null;
if (!empty($data['respuestas']) && count($data['respuestas']) == count($preguntas)) {
    $suma_puntos = array_sum($data['respuestas']);
    $max_puntos = count($preguntas) * 4;
    $potencial_mejora = (1 - ($suma_puntos / $max_puntos)) * 100;
}

?>

<div class="container mt-4">
    <div class="module-container">
        <div class="module-header">
            <h2 class="module-title">Autodiagnóstico de la Cadena de Valor Interna</h2>
        </div>
        <div class="module-content">
            <p>A continuación, valore su empresa de 0 a 4 en función de cada una de las afirmaciones para conocer porcentualmente el potencial de mejora de la cadena de valor.</p>
            <p><strong>Valoración:</strong> 0= En total desacuerdo; 1= No está de acuerdo; 2= Está de acuerdo; 3= Está bastante de acuerdo; 4= En total acuerdo.</p>
            
            <?php echo $mensaje; ?>

            <form action="autodiagnostico_cadena_valor.php" method="POST">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>AUTODIAGNÓSTICO DE LA CADENA DE VALOR INTERNA</th>
                                <th class="text-center" style="width: 5%;">0</th>
                                <th class="text-center" style="width: 5%;">1</th>
                                <th class="text-center" style="width: 5%;">2</th>
                                <th class="text-center" style="width: 5%;">3</th>
                                <th class="text-center" style="width: 5%;">4</th>
                            </tr>
                        </thead>
                        <tbody id="diagnostic-table-body">
                            <?php foreach ($preguntas as $num => $texto): ?>
                            <tr>
                                <td><?php echo "<strong>$num.</strong> " . htmlspecialchars($texto); ?></td>
                                <?php for ($i = 0; $i <= 4; $i++): ?>
                                <td class="text-center">
                                    <input class="form-check-input" type="radio" 
                                           name="q<?php echo $num; ?>" 
                                           value="<?php echo $i; ?>"
                                           <?php echo (isset($data['respuestas'][$num]) && $data['respuestas'][$num] == $i) ? 'checked' : ''; ?>
                                           required>
                                </td>
                                <?php endfor; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="card my-4" id="resultado-diagnostico" 
                     style="<?php echo ($potencial_mejora === null) ? 'display: none;' : ''; ?> background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border: none; border-radius: 1rem; box-shadow: 0 8px 25px rgba(0,0,0,.1);">
                    <div class="card-header text-center" style="background: linear-gradient(135deg, var(--brand-blue), var(--brand-dark)); color: white; border-radius: 1rem 1rem 0 0; padding: 1.5rem;">
                        <h4 class="mb-0" style="font-weight: 700; font-size: 1.5rem;">
                            <i class="fas fa-chart-line me-2"></i>Resultado del Diagnóstico
                        </h4>
                    </div>
                    <div class="card-body text-center" style="padding: 2rem;">
                        <h5 class="card-title mb-4" style="color: var(--brand-blue); font-weight: 600; font-size: 1.3rem;">
                            Potencial de Mejora de la Cadena de Valor Interna
                        </h5>
                        <div class="position-relative d-inline-block mb-4">
                            <div class="display-1 fw-bold" id="potencial-porcentaje" 
                                 style="color: var(--brand-green); text-shadow: 0 2px 4px rgba(0,0,0,.1); font-size: 4rem; line-height: 1;">
                                <?php echo ($potencial_mejora !== null) ? number_format($potencial_mejora, 1) . '%' : '#¡REF!'; ?>
                            </div>
                            <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center">
                                <div class="spinner-border text-primary" role="status" id="loading-spinner" style="display: none;">
                                    <span class="visually-hidden">Calculando...</span>
                                </div>
                            </div>
                        </div>
                        <div class="row justify-content-center">
                            <div class="col-md-8">
                                <div class="alert alert-info border-0" style="background: linear-gradient(135deg, rgba(15,47,70,.1), rgba(24,179,107,.1)); border-left: 4px solid var(--brand-blue);">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Interpretación:</strong> Un valor más alto indica una mayor área de oportunidad para mejorar su cadena de valor interna.
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="progress" style="height: 8px; border-radius: 4px; background: rgba(15,47,70,.1);">
                                <div class="progress-bar" id="progress-bar" 
                                     style="background: linear-gradient(90deg, var(--brand-green), var(--brand-blue)); border-radius: 4px; transition: width 0.5s ease;"
                                     role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botón de guardar diagnóstico -->
                <div class="d-grid gap-2 mt-4 mb-5">
                    <button type="submit" class="btn btn-save btn-lg">
                        <i class="fas fa-save me-2"></i>Guardar Diagnóstico
                    </button>
                </div>

                <div class="card mt-5">
                    <div class="card-header">
                        Reflexión y Conclusiones
                    </div>
                    <div class="card-body">
                        <p>Reflexione sobre el resultado obtenido. Anote aquellas observaciones que puedan ser de su interés e identifique sus fortalezas y debilidades respecto a su cadena de valor. Éstas se añadirán a su análisis FODA.</p>
                        
                        <div class="explanation-box p-3 mb-4">
                            
                            <p>Complete las fortalezas y debilidades más significativas identificadas en su cadena de valor.</p>

                            <div class="table-responsive">
                                <table class="table table-bordered" id="tablaFODA" style="background: linear-gradient(135deg, #f5faff 0%, #eef6fb 100%); border-radius: 0.75rem; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,.05);">
                                    <!-- FORTALEZAS -->
                                    <thead>
                                        <tr>
                                            <th colspan="2" style="background: #D2B48C; color: #000; font-weight: 700; padding: 1rem; text-align: center;">FORTALEZAS</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td style="background: #D2B48C; color: #000; font-weight: 600; padding: 1rem; vertical-align: middle; width: 15%;">F1:</td>
                                            <td style="background: white; padding: 1rem;">
                                                <input type="text" name="fortalezas[]" class="form-control" value="<?php echo htmlspecialchars($data['foda']['fortalezas'][0] ?? ''); ?>" placeholder="Ingrese la primera fortaleza" style="border: 1px solid rgba(15,47,70,.2); border-radius: 0.5rem; padding: 0.75rem;">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="background: #D2B48C; color: #000; font-weight: 600; padding: 1rem; vertical-align: middle;">F2:</td>
                                            <td style="background: white; padding: 1rem;">
                                                <input type="text" name="fortalezas[]" class="form-control" value="<?php echo htmlspecialchars($data['foda']['fortalezas'][1] ?? ''); ?>" placeholder="Ingrese la segunda fortaleza" style="border: 1px solid rgba(15,47,70,.2); border-radius: 0.5rem; padding: 0.75rem;">
                                            </td>
                                        </tr>
                                    </tbody>
                                    
                                    <!-- DEBILIDADES -->
                                    <thead>
                                        <tr>
                                            <th colspan="2" style="background: #90EE90; color: #000; font-weight: 700; padding: 1rem; text-align: center;">DEBILIDADES</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td style="background: #90EE90; color: #000; font-weight: 600; padding: 1rem; vertical-align: middle;">D1:</td>
                                            <td style="background: white; padding: 1rem;">
                                                <input type="text" name="debilidades[]" class="form-control" value="<?php echo htmlspecialchars($data['foda']['debilidades'][0] ?? ''); ?>" placeholder="Ingrese la primera debilidad" style="border: 1px solid rgba(15,47,70,.2); border-radius: 0.5rem; padding: 0.75rem;">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="background: #90EE90; color: #000; font-weight: 600; padding: 1rem; vertical-align: middle;">D2:</td>
                                            <td style="background: white; padding: 1rem;">
                                                <input type="text" name="debilidades[]" class="form-control" value="<?php echo htmlspecialchars($data['foda']['debilidades'][1] ?? ''); ?>" placeholder="Ingrese la segunda debilidad" style="border: 1px solid rgba(15,47,70,.2); border-radius: 0.5rem; padding: 0.75rem;">
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-center mt-3">
                                <button type="button" class="btn btn-save" onclick="guardarFODA()">Guardar FODA</button>
                            </div>
                        </div>
                    </div>

                </div>
            </form>

            <div class="d-flex justify-content-between mt-4">
                <a href="cadena_valor.php" class="btn btn-nav">&laquo; Anterior: Cadena de Valor</a>
                <a href="dashboard.php" class="btn btn-nav-outline">Volver al Índice</a>
                <a href="matriz_bcg.php" class="btn btn-nav">Siguiente: Matriz BCG &raquo;</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const totalPreguntas = <?php echo count($preguntas); ?>;
    const radios = document.querySelectorAll('#diagnostic-table-body input[type="radio"]');
    const resultadoContainer = document.getElementById('resultado-diagnostico');
    const porcentajeSpan = document.getElementById('potencial-porcentaje');

    function calcularPotencial() {
        const respuestasSeleccionadas = document.querySelectorAll('#diagnostic-table-body input[type="radio"]:checked');
        const loadingSpinner = document.getElementById('loading-spinner');
        const progressBar = document.getElementById('progress-bar');
        
        if (respuestasSeleccionadas.length < totalPreguntas) {
            resultadoContainer.style.display = 'none';
            porcentajeSpan.textContent = 'Termine de marcar para ver el resultado'; 
            return;
        }

        // Mostrar spinner de carga
        loadingSpinner.style.display = 'block';
        porcentajeSpan.style.opacity = '0.3';
        
        // Simular cálculo con delay para efecto visual
        setTimeout(() => {
            let sumaPuntos = 0;
            respuestasSeleccionadas.forEach(radio => {
                sumaPuntos += parseInt(radio.value, 10);
            });

            const maxPuntos = totalPreguntas * 4;
            const potencialMejora = (1 - (sumaPuntos / maxPuntos)) * 100;
            
            // Actualizar porcentaje
            porcentajeSpan.textContent = potencialMejora.toFixed(1) + '%';
            porcentajeSpan.style.opacity = '1';
            
            // Actualizar barra de progreso
            progressBar.style.width = potencialMejora + '%';
            progressBar.setAttribute('aria-valuenow', potencialMejora);
            
            // Ocultar spinner y mostrar resultado
            loadingSpinner.style.display = 'none';
            resultadoContainer.style.display = 'block';
            
            // Efecto de animación
            resultadoContainer.style.animation = 'fadeInUp 0.6s ease-out';
        }, 500);
    }

    radios.forEach(radio => {
        radio.addEventListener('change', calcularPotencial);
    });
});

// Función para guardar FODA individualmente
function guardarFODA() {
    const form = document.querySelector('form');
    const formData = new FormData(form);
    
    // Agregar parámetro para identificar que es guardado de FODA
    formData.append('guardar_foda', '1');
    
    fetch('autodiagnostico_cadena_valor.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        // Mostrar mensaje de éxito
        mostrarMensaje('Datos FODA guardados correctamente', 'success');
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarMensaje('Error al guardar los datos FODA', 'error');
    });
}

// Función para mostrar mensajes
function mostrarMensaje(mensaje, tipo) {
    const alertClass = tipo === 'success' ? 'alert-success' : 'alert-danger';
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert ${alertClass} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${mensaje}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insertar al inicio del contenido
    const content = document.querySelector('.module-content');
    content.insertBefore(alertDiv, content.firstChild);
    
    // Remover después de 4 segundos
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.parentNode.removeChild(alertDiv);
        }
    }, 4000);
}

// Agregar animaciones CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .card {
        transition: all 0.3s ease;
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 35px rgba(0,0,0,.15) !important;
    }
    
    .progress-bar {
        position: relative;
        overflow: hidden;
    }
    
    .progress-bar::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        right: 0;
        background: linear-gradient(
            45deg,
            transparent 33%,
            rgba(255,255,255,.2) 33%,
            rgba(255,255,255,.2) 66%,
            transparent 66%
        );
        background-size: 20px 20px;
        animation: move 1s linear infinite;
    }
    
    @keyframes move {
        0% { background-position: 0 0; }
        100% { background-position: 20px 20px; }
    }
`;
document.head.appendChild(style);
</script>

<?php
require_once 'includes/footer.php';
$mysqli->close();
?>
