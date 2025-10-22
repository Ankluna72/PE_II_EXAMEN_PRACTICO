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

$mensaje_exito = '';
$mensaje_error = '';

// -------------------------------------------------------------
// Autodiagnostico BCG - editable, cálculos en JS y en PHP al guardar
// Estructura basada en la descripción del usuario (5 productos)
// Los datos se pueden editar en la tabla y calcular en el navegador
// o guardar en sesión para persistencia temporal.
// -------------------------------------------------------------

// Inicializar valores (por defecto 0 o provenientes de POST / SESSION)
if (!isset($_SESSION['autobcg'])) {
    $_SESSION['autobcg'] = [];
}
$data = &$_SESSION['autobcg'];

// Productos (fijos)
$productos = ['Producto 1','Producto 2','Producto 3','Producto 4','Producto 5'];
$numProd = count($productos);

// Años para la tabla de demanda
$anios = [2012,2013,2014,2015,2016];
$numYears = count($anios);

// Competidores: 9 filas + 'Mayor' calculado
$numCompetidores = 9;

// Si hay POST (guardar) tomar valores; si no, usar lo almacenado o 0
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ventas
    $data['ventas'] = array_fill(0,$numProd,'');
    for ($i=0;$i<$numProd;$i++){
        $key = "ventas_{$i}";
        $data['ventas'][$i] = isset($_POST[$key])? $_POST[$key] : '';
    }
    // Demanda por año -> esperamos campos demanda_year_product e.g. demanda_0_0
    $data['demanda'] = array_fill(0,$numYears, array_fill(0,$numProd,''));
    for ($y=0;$y<$numYears;$y++){
        for ($p=0;$p<$numProd;$p++){
            $key = "demanda_{$y}_{$p}";
            $data['demanda'][$y][$p] = isset($_POST[$key])? $_POST[$key] : '';
        }
    }
    // Demanda global sector
    $aniosDemanda = [2012, 2013, 2014, 2015, 2016, 2017];
    $data['demanda_global'] = array_fill(0,count($aniosDemanda), array_fill(0,$numProd,''));
    for ($y=0;$y<count($aniosDemanda);$y++){
        for ($p=0;$p<$numProd;$p++){
            $key = "demanda_global_{$y}_{$p}";
            $data['demanda_global'][$y][$p] = isset($_POST[$key])? $_POST[$key] : '';
        }
    }
    // Competidores
    $data['competidores'] = array_fill(0,$numProd, array_fill(0,$numCompetidores,''));
    for ($p=0;$p<$numProd;$p++){
        for ($c=0;$c<$numCompetidores;$c++){
            $key = "comp_{$p}_{$c}";
            $data['competidores'][$p][$c] = isset($_POST[$key])? $_POST[$key] : '';
        }
    }
    // FODA
    $data['foda'] = [
        'fortaleza_3' => $_POST['fortaleza_3'] ?? '',
        'fortaleza_4' => $_POST['fortaleza_4'] ?? '',
        'debilidad_3' => $_POST['debilidad_3'] ?? '',
        'debilidad_4' => $_POST['debilidad_4'] ?? ''
    ];
    
    // Guardar FODA en la base de datos si es la tabla que se está guardando
    if (isset($_POST['tabla_guardar']) && $_POST['tabla_guardar'] === 'foda') {
        $resultado = guardarFODAEnBD($data['foda'], $mysqli);
        if ($resultado) {
            $mensaje_exito = "Datos FODA guardados correctamente en la base de datos.";
        } else {
            $mensaje_error = "Error al guardar los datos FODA en la base de datos.";
        }
    }
    
    // Guardar Demanda Global en la base de datos si es la tabla que se está guardando
    if (isset($_POST['tabla_guardar']) && $_POST['tabla_guardar'] === 'demanda_global') {
        $resultado = guardarDemandaGlobalEnBD($data['demanda_global'], $mysqli);
        if ($resultado) {
            $mensaje_exito = "Datos de Demanda Global guardados correctamente en la base de datos.";
        } else {
            $mensaje_error = "Error al guardar los datos de Demanda Global en la base de datos.";
        }
    }
    // Guardar timestamp
    $data['updated_at'] = date('Y-m-d H:i:s');
} else {
    // Inicializar si no existe
    if (!isset($data['ventas'])) $data['ventas'] = array_fill(0,$numProd,'');
    if (!isset($data['demanda'])) $data['demanda'] = array_fill(0,$numYears, array_fill(0,$numProd,''));
    $aniosDemanda = [2012, 2013, 2014, 2015, 2016, 2017];
    if (!isset($data['demanda_global'])) $data['demanda_global'] = array_fill(0,count($aniosDemanda), array_fill(0,$numProd,''));
    if (!isset($data['competidores'])) $data['competidores'] = array_fill(0,$numProd, array_fill(0,$numCompetidores,''));
    if (!isset($data['foda'])) $data['foda'] = [
        'fortaleza_3' => '',
        'fortaleza_4' => '',
        'debilidad_3' => '',
        'debilidad_4' => ''
    ];
    
    // Cargar datos FODA existentes desde la base de datos
    $stmt_foda_load = $mysqli->prepare("SELECT tipo, descripcion, posicion FROM foda WHERE id_empresa = ? AND origen = 'bcg'");
    $stmt_foda_load->bind_param("i", $id_empresa_actual);
    $stmt_foda_load->execute();
    $result_foda_load = $stmt_foda_load->get_result();
    while ($row = $result_foda_load->fetch_assoc()) {
        if ($row['tipo'] == 'fortaleza' && $row['posicion'] == 3) {
            $data['foda']['fortaleza_3'] = $row['descripcion'];
        } elseif ($row['tipo'] == 'fortaleza' && $row['posicion'] == 4) {
            $data['foda']['fortaleza_4'] = $row['descripcion'];
        } elseif ($row['tipo'] == 'debilidad' && $row['posicion'] == 3) {
            $data['foda']['debilidad_3'] = $row['descripcion'];
        } elseif ($row['tipo'] == 'debilidad' && $row['posicion'] == 4) {
            $data['foda']['debilidad_4'] = $row['descripcion'];
        }
    }
    $stmt_foda_load->close();
}

// Función para obtener la clase CSS de un producto
function getProductoClass($index) {
    return 'producto-' . ($index + 1);
}

// Función para guardar Demanda Global en la base de datos
function guardarDemandaGlobalEnBD($demandaGlobalData, $mysqli) {
    // Obtener el ID de la empresa del usuario actual
    $id_empresa = $_SESSION['id_empresa_actual'] ?? null;
    
    if (!$id_empresa) {
        error_log("DEBUG DEMANDA GLOBAL: Error - No hay ID de empresa");
        return false;
    }
    
    try {
        // Iniciar transacción
        $mysqli->begin_transaction();
        
        // Actualizar los datos de demanda global
        $stmt_update = $mysqli->prepare("UPDATE demanda_global_sector SET 
            anio_2012 = ?, anio_2013 = ?, anio_2014 = ?, anio_2015 = ?, 
            anio_2016 = ?, anio_2017 = ? 
            WHERE id_empresa = ? AND producto = ?");
        
        $aniosDemanda = [2012, 2013, 2014, 2015, 2016, 2017];
        $productos = ['Producto 1','Producto 2','Producto 3','Producto 4','Producto 5'];
        $numProd = count($productos);
        
        $actualizados = 0;
        
        for ($p = 0; $p < $numProd; $p++) {
            // Preparar los valores para cada año
            $valores = [];
            for ($y = 0; $y < count($aniosDemanda); $y++) {
                // Convertir a NULL si está vacío, o a float si tiene valor
                $valor = $demandaGlobalData[$y][$p];
                $valores[] = ($valor === '' || $valor === null) ? null : floatval($valor);
            }
            
            // Agregar id_empresa y producto al final de los parámetros
            $valores[] = $id_empresa;
            $valores[] = $productos[$p];
            
            // Vincular parámetros
            $stmt_update->bind_param("ddddddis", 
                $valores[0], $valores[1], $valores[2], 
                $valores[3], $valores[4], $valores[5],
                $valores[6], $valores[7]
            );
            
            $stmt_update->execute();
            $actualizados += $stmt_update->affected_rows;
        }
        
        $stmt_update->close();
        
        // Confirmar transacción
        $mysqli->commit();
        
        error_log("DEBUG DEMANDA GLOBAL: Datos actualizados: $actualizados productos");
        return true;
        
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $mysqli->rollback();
        error_log("DEBUG DEMANDA GLOBAL: Error - " . $e->getMessage());
        return false;
    }
}

// Función para guardar FODA en la base de datos
function guardarFODAEnBD($fodaData, $mysqli) {
    // Obtener el ID de la empresa del usuario actual
    $id_empresa = $_SESSION['id_empresa_actual'] ?? null;
    $id_usuario = $_SESSION['id_usuario'] ?? null;
    
    // Debug: Log de entrada
    error_log("DEBUG FODA: Iniciando guardado - ID Empresa: $id_empresa, ID Usuario: $id_usuario");
    error_log("DEBUG FODA: Datos recibidos: " . print_r($fodaData, true));
    
    if (!$id_empresa) {
        error_log("DEBUG FODA: Error - No hay ID de empresa");
        return false;
    }
    
    try {
        // Limpiar FODA anterior de BCG
        $stmt_delete = $mysqli->prepare("DELETE FROM foda WHERE id_empresa = ? AND origen = 'bcg'");
        $stmt_delete->bind_param("i", $id_empresa);
        $stmt_delete->execute();
        $stmt_delete->close();
        error_log("DEBUG FODA: Datos anteriores eliminados");
        
        // Insertar nuevas fortalezas y debilidades
        $stmt_insert = $mysqli->prepare("INSERT INTO foda (id_empresa, id_usuario, tipo, descripcion, origen, posicion) VALUES (?, ?, ?, ?, 'bcg', ?)");
        
        $guardados = 0;
        $tipo_fortaleza = 'fortaleza';
        $tipo_debilidad = 'debilidad';
        $origen = 'bcg';
        
        // Fortalezas
        if (!empty($fodaData['fortaleza_3'])) {
            $posicion = 3;
            $stmt_insert->bind_param("iissi", $id_empresa, $id_usuario, $tipo_fortaleza, $fodaData['fortaleza_3'], $posicion);
            $stmt_insert->execute();
            $guardados++;
            error_log("DEBUG FODA: Fortaleza 3 guardada: " . $fodaData['fortaleza_3']);
        }
        if (!empty($fodaData['fortaleza_4'])) {
            $posicion = 4;
            $stmt_insert->bind_param("iissi", $id_empresa, $id_usuario, $tipo_fortaleza, $fodaData['fortaleza_4'], $posicion);
            $stmt_insert->execute();
            $guardados++;
            error_log("DEBUG FODA: Fortaleza 4 guardada: " . $fodaData['fortaleza_4']);
        }
        
        // Debilidades
        if (!empty($fodaData['debilidad_3'])) {
            $posicion = 3;
            $stmt_insert->bind_param("iissi", $id_empresa, $id_usuario, $tipo_debilidad, $fodaData['debilidad_3'], $posicion);
            $stmt_insert->execute();
            $guardados++;
            error_log("DEBUG FODA: Debilidad 3 guardada: " . $fodaData['debilidad_3']);
        }
        if (!empty($fodaData['debilidad_4'])) {
            $posicion = 4;
            $stmt_insert->bind_param("iissi", $id_empresa, $id_usuario, $tipo_debilidad, $fodaData['debilidad_4'], $posicion);
            $stmt_insert->execute();
            $guardados++;
            error_log("DEBUG FODA: Debilidad 4 guardada: " . $fodaData['debilidad_4']);
        }
        
        $stmt_insert->close();
        error_log("DEBUG FODA: Total guardados: $guardados registros");
        return true;
    } catch (mysqli_sql_exception $e) {
        error_log("DEBUG FODA: Error guardando FODA: " . $e->getMessage());
        return false;
    }
}

// Función para obtener el color de un producto
function getProductoColor($index) {
    $colors = ['#18b36b', '#0f2f46', '#e74c3c', '#f39c12', '#9b59b6'];
    return $colors[$index] ?? '#666666';
}

// Funciones de cálculo en PHP (para mostrar resultados al guardar)
function calc_totales_y_porcentajes($ventas){
    // Convertir cadenas vacías a 0 para evitar errores de tipo
    $ventas_numericas = array_map(function($v) {
        return ($v === '' || $v === null) ? 0.0 : floatval($v);
    }, $ventas);
    
    $total = array_sum($ventas_numericas);
    $porc = [];
    foreach ($ventas_numericas as $v) $porc[] = ($total>0)?($v/$total*100):0.0;
    return [$total,$porc];
}

function calc_prm_php($ventas, $competidores){
    // PRM por producto = ventas_producto / max(ventas_competidor) o referencia
    // Convertir cadenas vacías a 0 para evitar errores de tipo
    $ventas_numericas = array_map(function($v) {
        return ($v === '' || $v === null) ? 0.0 : floatval($v);
    }, $ventas);
    
    $max_global = (count($ventas_numericas)>0)? max($ventas_numericas) : 0.0;
    $prm = [];
    foreach ($ventas_numericas as $i=>$v){
        $maxcomp = 0.0;
        if (isset($competidores[$i]) && count($competidores[$i])>0) {
            // También convertir competidores a números
            $comp_numericos = array_map(function($c) {
                return ($c === '' || $c === null) ? 0.0 : floatval($c);
            }, $competidores[$i]);
            $maxcomp = max($comp_numericos);
        }
        $ref = ($maxcomp>0)? $maxcomp : ($max_global>0? $max_global : 1.0);
        $prm[] = ($ref>0)? ($v/$ref) : 0.0;
    }
    return $prm;
}

function calc_tcm_php($demanda, $anios){
    // Fórmula Excel: =SI(SUMA(D23:D27)/5>0.2,0.2,SUMA(D23:D27)/5)
    // Sumamos los porcentajes por columna de producto y promediamos
    $nYears = count($anios);
    $numProd = count($demanda[0]);
    $tcms = array_fill(0,$numProd,0.0);
    
    for ($p=0;$p<$numProd;$p++){
        $suma = 0.0;
        $count = 0;
        
        // Sumar todos los valores de la columna del producto
        for ($y=0;$y<$nYears;$y++){
            $valor = floatval($demanda[$y][$p]);
            $suma += $valor; // Sumar todos los valores, incluso los 0
            $count++;
        }
        
        if ($count > 0) {
            $promedio = $suma / $count;
            // Aplicar la fórmula: si promedio > 20%, entonces 20%, sino el promedio
            $tcms[$p] = ($promedio > 20.0) ? 20.0 : $promedio;
        } else {
            $tcms[$p] = 0.0;
        }
    }
    return $tcms;
}

list($totalVentas,$porcVentas) = calc_totales_y_porcentajes($data['ventas']);
$prm_php = calc_prm_php($data['ventas'],$data['competidores']);
$tcm_php = calc_tcm_php($data['demanda'],$anios);
$growth_promedio = (count($tcm_php)>0)? array_sum($tcm_php)/count($tcm_php) : 0.0;

// Clasificación BCG según reglas
$clasif_php = [];
for ($i=0;$i<$numProd;$i++){
    $g = $tcm_php[$i];
    $r = $prm_php[$i];
    if ($g > $growth_promedio && $r >= 1) $clasif_php[$i] = 'Estrella';
    elseif ($g > $growth_promedio && $r < 1) $clasif_php[$i] = 'Incógnita';
    elseif ($g <= $growth_promedio && $r >= 1) $clasif_php[$i] = 'Vaca';
    else $clasif_php[$i] = 'Perro';
}

// ----------------------
// HTML / Formulario
// ----------------------
?>

<div class="container mt-4">
    <div class="module-container">
        <div class="module-header">
            <h2 class="module-title">Autodiagnóstico BCG - Interactivo</h2>
        </div>

        <div class="module-content">
            <?php if (!empty($mensaje_exito)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($mensaje_exito); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($mensaje_error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($mensaje_error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="explanation-box p-3 mb-4">
                <p>A continuación analice su cartera de productos y /o servicios e intente clasificarlos calcular el posicionamiento de su cartera de productos en la matriz B.C.G. Para ello rellene las siguientes tablas con la mayor precisión posible.</p>
                <p>Las celdas sombreadas en colores no hay que modificarlas. Sólo debe introducir los datos en las celdas con fondo color blanco y fuente color azul. Podrá consultar los comentarios aclaratorios en algunas de las celdas.</p>
            </div>

            <form id="formBCG" method="post">
                <!-- Tabla 1: Previsión de Ventas -->
                <div class="explanation-box p-3 mb-4">
                    <h4 style="color: var(--brand-green); text-align: center; font-weight: 700; margin-bottom: 1.5rem;">PREVISIÓN DE VENTAS</h4>
                    <p>Ingrese las ventas por producto. Los cálculos se actualizan automáticamente al escribir. Los datos se guardan automáticamente en la sesión.</p>

                    <!-- Leyenda de colores -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="d-flex flex-wrap justify-content-center gap-3">
                                <?php for ($i=0;$i<$numProd;$i++): ?>
                                <div class="d-flex align-items-center">
                                    <div class="<?php echo getProductoClass($i); ?>" style="width: 20px; height: 20px; border-radius: 50%; margin-right: 8px; border: 2px solid rgba(255,255,255,0.3);"></div>
                                    <span style="font-weight: 600; color: var(--brand-blue);"><?php echo $productos[$i]; ?></span>
                                </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered" id="tablaVentas" style="background: linear-gradient(135deg, #f5faff 0%, #eef6fb 100%); border-radius: 0.75rem; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,.05);">
                            <thead style="background: linear-gradient(135deg, var(--brand-blue), var(--brand-dark)); color: white;">
                                <tr>
                                    <th style="text-align: left; font-weight: 600; padding: 1rem;">PRODUCTOS</th>
                                    <th style="text-align: center; font-weight: 600; padding: 1rem;">VENTAS</th>
                                    <th style="text-align: center; font-weight: 600; padding: 1rem;">% S/ TOTAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                for ($i=0;$i<$numProd;$i++): 
                                ?>
                                <tr style="transition: all 0.3s ease;">
                                    <td class="<?php echo getProductoClass($i); ?>" style="padding: 1rem; font-weight: 700; font-size: 1.1rem; color: inherit;"><?php echo $productos[$i]; ?></td>
                                    <td style="text-align: center; padding: 1rem; background: white;">
                                        <input type="text" name="ventas_<?php echo $i; ?>" class="form-control venta-input" value="<?php echo htmlspecialchars($data['ventas'][$i]); ?>" style="color: var(--brand-blue); text-align: center; font-weight: 600; border: 1px solid rgba(15,47,70,.2); border-radius: 0.5rem; padding: 0.75rem; transition: all 0.3s ease;" placeholder="0.00" pattern="[0-9]+(\.[0-9]{1,2})?" title="Ingrese un número con máximo 2 decimales" oninput="this.value = this.value.replace(/[^0-9.]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');">
                                    </td>
                                    <td style="text-align: center; padding: 1rem; font-weight: 600; background: white;" class="pct-venta">0.00%</td>
                                </tr>
                                <?php endfor; ?>
                                <tr style="background: linear-gradient(135deg, var(--brand-green), var(--brand-green-600)); color: white; font-weight: 700;">
                                    <td style="padding: 1rem;"><strong>TOTAL</strong></td>
                                    <td style="text-align: center; padding: 1rem;"><strong id="totalVentas"><?php echo number_format($totalVentas,2); ?></strong></td>
                                    <td style="text-align: center; padding: 1rem;"><strong id="totalPorcentaje">100.00%</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    <div class="text-center mt-3">
                        <button type="button" class="btn btn-save" onclick="guardarTabla('ventas')">Guardar Ventas</button>
                    </div>
                </div>

                <!-- Tabla 2: TCM por periodos -->
                <div class="explanation-box p-3 mb-4">
                    <h4 style="color: var(--brand-blue); text-align: center; font-weight: 700; margin-bottom: 1.5rem;">TASAS DE CRECIMIENTO DEL MERCADO (TCM)</h4>
                    <p>Complete las tasas de crecimiento por producto para cada período (en porcentaje con 2 decimales).</p>

                    <div class="table-responsive">
                        <table class="table table-bordered" id="tablaDemanda" style="background: linear-gradient(135deg, #f5faff 0%, #eef6fb 100%); border-radius: 0.75rem; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,.05);">
                            <thead>
                                <tr>
                                    <th style="background: linear-gradient(135deg, var(--brand-blue), var(--brand-dark)); color: white; font-weight: 600; padding: 1rem; text-align: left; font-style: italic;">PERIODOS</th>
                                    <th colspan="<?php echo $numProd; ?>" style="background: linear-gradient(135deg, var(--brand-blue), var(--brand-dark)); color: white; font-weight: 600; padding: 1rem; text-align: center; font-style: italic;">MERCADOS</th>
                                </tr>
                                <tr>
                                    <th style="background: linear-gradient(135deg, var(--brand-blue), var(--brand-dark)); color: white; font-weight: 600; padding: 1rem;"></th>
                                    <?php for ($i=0;$i<$numProd;$i++): ?>
                                    <th class="<?php echo getProductoClass($i); ?>" style="font-weight: 700; padding: 1rem; text-align: center; color: inherit;"><?php echo $productos[$i]; ?></th>
                                    <?php endfor; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $periodos = [
                                    ['2012', '2013'],
                                    ['2013', '2014'], 
                                    ['2014', '2015'],
                                    ['2015', '2016'],
                                    ['2016', '2016']
                                ];
                                for ($y=0;$y<$numYears;$y++): 
                                ?>
                                <tr>
                                    <td style="padding: 1rem; font-weight: 600; background: rgba(15,47,70,.05);"><?php echo $periodos[$y][0]; ?> <?php echo $periodos[$y][1]; ?></td>
                                    <?php for ($p=0;$p<$numProd;$p++): ?>
                                    <td style="padding: 0.5rem; background: white;">
                                        <input type="text" name="demanda_<?php echo $y; ?>_<?php echo $p; ?>" class="form-control demanda-input" value="<?php echo htmlspecialchars($data['demanda'][$y][$p]); ?>" style="border: 1px solid rgba(15,47,70,.2); border-radius: 0.5rem; padding: 0.5rem; text-align: center; color: var(--brand-blue);" placeholder="0.00" pattern="[0-9]+(\.[0-9]{1,2})?" title="Ingrese un número con máximo 2 decimales" oninput="this.value = this.value.replace(/[^0-9.]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');">
                                    </td>
                                    <?php endfor; ?>
                                </tr>
                                <?php endfor; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <button type="button" class="btn btn-save" onclick="guardarTabla('tcm')">Guardar TCM</button>
                    </div>
                </div>

                <!-- Tabla 3: Resultados BCG -->
                 <div class="explanation-box p-3 mb-4">
                    <h4 style="color: var(--brand-blue); text-align: center; font-weight: 700; margin-bottom: 1.5rem;">RESUMEN BCG</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered text-center" id="tablaResultados">
                            <thead>
                                <tr>
                                    <th style="background: linear-gradient(135deg, var(--brand-blue), var(--brand-dark)); color: white; font-weight: 600; padding: 1rem;">BCG</th>
                                    <?php for ($i=0;$i<$numProd;$i++): ?>
                                    <th class="<?php echo getProductoClass($i); ?>" style="font-weight: 700; padding: 1rem; text-align: center; color: inherit;"><?php echo $productos[$i]; ?></th>
                                    <?php endfor; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>TCM (%)</strong></td>
                                    <?php for ($p=0;$p<$numProd;$p++): ?><td class="res-tcm"><?php echo number_format($tcm_php[$p],2); ?> %</td><?php endfor; ?>
                                </tr>
                                <tr>
                                    <td><strong>PRM</strong></td>
                                    <?php for ($p=0;$p<$numProd;$p++): ?><td class="res-prm"><?php echo number_format($prm_php[$p],3); ?></td><?php endfor; ?>
                                </tr>
                                <tr>
                                    <td><strong>% s/VTAS</strong></td>
                                    <?php for ($p=0;$p<$numProd;$p++): ?><td class="res-porc"><?php echo number_format($porcVentas[$p],2); ?> %</td><?php endfor; ?>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tabla 4: Evolución de la Demanda Global Sector -->
                <div class="explanation-box p-3 mb-4">
                    <h4 style="color: var(--brand-blue); text-align: center; font-weight: 700; margin-bottom: 1.5rem;">EVOLUCIÓN DE LA DEMANDA GLOBAL SECTOR (en miles de soles)</h4>
                    <p>Complete los valores de demanda global por producto para cada año (en porcentaje con 2 decimales).</p>

                    <div class="table-responsive">
                        <table class="table table-bordered" id="tablaDemandaGlobal" style="background: linear-gradient(135deg, #f5faff 0%, #eef6fb 100%); border-radius: 0.75rem; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,.05);">
                            <thead>
                                <tr>
                                    <th style="background: linear-gradient(135deg, var(--brand-blue), var(--brand-dark)); color: white; font-weight: 600; padding: 1rem; text-align: left;">AÑOS</th>
                                    <th colspan="<?php echo $numProd; ?>" style="background: linear-gradient(135deg, var(--brand-blue), var(--brand-dark)); color: white; font-weight: 600; padding: 1rem; text-align: center;">MERCADOS</th>
                                </tr>
                                <tr>
                                    <th style="background: linear-gradient(135deg, var(--brand-blue), var(--brand-dark)); color: white; font-weight: 600; padding: 1rem;"></th>
                                    <?php for ($i=0;$i<$numProd;$i++): ?>
                                    <th class="<?php echo getProductoClass($i); ?>" style="font-weight: 700; padding: 1rem; text-align: center; color: inherit;"><?php echo $productos[$i]; ?></th>
                                    <?php endfor; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $aniosDemanda = [2012, 2013, 2014, 2015, 2016, 2017];
                                for ($y=0;$y<count($aniosDemanda);$y++): 
                                ?>
                                <tr>
                                    <td style="padding: 1rem; font-weight: 600; background: rgba(15,47,70,.05);"><?php echo $aniosDemanda[$y]; ?></td>
                                    <?php for ($p=0;$p<$numProd;$p++): ?>
                                    <td style="padding: 0.5rem; background: white;">
                                        <input type="text" name="demanda_global_<?php echo $y; ?>_<?php echo $p; ?>" class="form-control demanda-global-input" value="<?php echo htmlspecialchars($data['demanda_global'][$y][$p] ?? ''); ?>" style="border: 1px solid rgba(15,47,70,.2); border-radius: 0.5rem; padding: 0.5rem; text-align: center; color: var(--brand-blue);" placeholder="0.00" pattern="[0-9]+(\.[0-9]{1,2})?" title="Ingrese un número con máximo 2 decimales" oninput="this.value = this.value.replace(/[^0-9.]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');">
                                    </td>
                                    <?php endfor; ?>
                                </tr>
                                <?php endfor; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <button type="button" class="btn btn-save" onclick="guardarTabla('demanda_global')">Guardar Demanda Global</button>
                    </div>
                </div>

                <!-- Tabla 5: Niveles de venta competidores -->
                <div class="explanation-box p-3 mb-4">
                    <h4 style="color: var(--brand-green); text-align: center; font-weight: 700; margin-bottom: 1.5rem;">NIVELES DE VENTA DE LOS COMPETIDORES DE CADA PRODUCTO</h4>
                    <p>Ingrese los valores de ventas de los competidores para cada producto. La fila <strong>Mayor</strong> se calcula automáticamente.</p>

                    <div class="table-responsive">
                        <table class="table table-bordered" id="tablaCompetidores" style="background: linear-gradient(135deg, #f5faff 0%, #eef6fb 100%); border-radius: 0.75rem; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,.05);">
                            <thead>
                                <tr>
                                    <th style="background: linear-gradient(135deg, var(--brand-blue), var(--brand-dark)); color: white; font-weight: 600; padding: 1rem; text-align: center;">COMPETIDOR</th>
                                    <?php for ($i=0;$i<$numProd;$i++): ?>
                                    <th class="<?php echo getProductoClass($i); ?>" style="font-weight: 700; padding: 1rem; text-align: center; color: inherit;"><?php echo $productos[$i]; ?></th>
                                    <?php endfor; ?>
                                </tr>
                                <tr>
                                    <th style="background: linear-gradient(135deg, var(--brand-blue), var(--brand-dark)); color: white; font-weight: 600; padding: 1rem; text-align: center;"></th>
                                    <?php for ($i=0;$i<$numProd;$i++): ?>
                                    <th class="<?php echo getProductoClass($i); ?>" style="font-weight: 700; padding: 1rem; text-align: center; color: inherit;">
                                        <div style="margin-bottom: 0.5rem;"><strong>EMPRESA</strong></div>
                                        <div style="font-size: 1.2rem; color: var(--brand-blue);" class="empresa-venta" data-prod="<?php echo $i; ?>"><?php echo htmlspecialchars($data['ventas'][$i] ?: '0'); ?></div>
                                    </th>
                                    <?php endfor; ?>
                                </tr>
                                <tr>
                                    <th style="background: linear-gradient(135deg, var(--brand-blue), var(--brand-dark)); color: white; font-weight: 600; padding: 1rem; text-align: center;"></th>
                                    <?php for ($i=0;$i<$numProd;$i++): ?>
                                    <th class="<?php echo getProductoClass($i); ?>" style="font-weight: 600; padding: 1rem; text-align: center; color: inherit; font-style: italic;">
                                        <div style="margin-bottom: 0.5rem;">Competidor</div>
                                        <div style="font-style: italic;">Ventas</div>
                                    </th>
                                    <?php endfor; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php for ($c=0;$c<$numCompetidores;$c++): ?>
                                <tr>
                                    <td style="padding: 1rem; font-weight: 600; background: rgba(15,47,70,.05);">CP<?php echo $c+1; ?></td>
                                    <?php for ($p=0;$p<$numProd;$p++): ?>
                                    <td style="padding: 0.5rem; background: white;">
                                        <input type="text" name="comp_<?php echo $p; ?>_<?php echo $c; ?>" class="form-control comp-input" value="<?php echo htmlspecialchars($data['competidores'][$p][$c]); ?>" placeholder="0.00" pattern="[0-9]+(\.[0-9]{1,2})?" title="Ingrese un número con máximo 2 decimales" style="border: 1px solid rgba(15,47,70,.2); border-radius: 0.5rem; padding: 0.5rem; text-align: center; color: var(--brand-blue);" oninput="this.value = this.value.replace(/[^0-9.]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');">
                                    </td>
                                    <?php endfor; ?>
                                </tr>
                                <?php endfor; ?>
                                <tr style="background: linear-gradient(135deg, var(--brand-green), var(--brand-green-600)); color: white; font-weight: 700;">
                                    <td style="padding: 1rem;"><strong>Mayor</strong></td>
                                    <?php for ($p=0;$p<$numProd;$p++): ?><td style="padding: 1rem; text-align: center;" class="mayor-comp" data-prod="<?php echo $p; ?>">0.00</td><?php endfor; ?>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <button type="button" class="btn btn-save" onclick="guardarTabla('competidores')">Guardar Competidores</button>
                    </div>
                </div>

                <!-- Texto de reflexión -->
                <div class="explanation-box p-4 mb-4" style="background: linear-gradient(135deg, rgba(24,179,107,.05), rgba(15,47,70,.05)); border-left: 4px solid var(--brand-green);">
                    <p style="color: var(--brand-blue); font-weight: 600; margin-bottom: 1rem; text-align: justify;">
                        Cómo puede observar, cada producto y/o servicio, representado a través de una bola y color tiene un posicionamiento determinado
                    </p>
                    <p style="color: var(--brand-blue); font-weight: 600; text-align: justify;">
                        Realice una reflexión general sobre sus productos y servicios e identifique las fortalezas y amenazas más significativas de su empresa. La información aportada servirá para completar la matriz FODA.
                    </p>
                </div>

                <!-- Tabla FODA - Fortalezas y Debilidades -->
                <div class="explanation-box p-3 mb-4">
                    
                    <p>Complete las fortalezas y debilidades más significativas identificadas en su análisis BCG.</p>

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
                                    <td style="background: #D2B48C; color: #000; font-weight: 600; padding: 1rem; vertical-align: middle; width: 15%;">F3:</td>
                                    <td style="background: white; padding: 1rem;">
                                        <input type="text" name="fortaleza_3" class="form-control" value="<?php echo htmlspecialchars($data['foda']['fortaleza_3'] ?? ''); ?>" placeholder="Ingrese la tercera fortaleza" style="border: 1px solid rgba(15,47,70,.2); border-radius: 0.5rem; padding: 0.75rem;">
                                    </td>
                                </tr>
                                <tr>
                                    <td style="background: #D2B48C; color: #000; font-weight: 600; padding: 1rem; vertical-align: middle;">F4:</td>
                                    <td style="background: white; padding: 1rem;">
                                        <input type="text" name="fortaleza_4" class="form-control" value="<?php echo htmlspecialchars($data['foda']['fortaleza_4'] ?? ''); ?>" placeholder="Ingrese la cuarta fortaleza" style="border: 1px solid rgba(15,47,70,.2); border-radius: 0.5rem; padding: 0.75rem;">
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
                                    <td style="background: #90EE90; color: #000; font-weight: 600; padding: 1rem; vertical-align: middle;">D3:</td>
                                    <td style="background: white; padding: 1rem;">
                                        <input type="text" name="debilidad_3" class="form-control" value="<?php echo htmlspecialchars($data['foda']['debilidad_3'] ?? ''); ?>" placeholder="Ingrese la tercera debilidad" style="border: 1px solid rgba(15,47,70,.2); border-radius: 0.5rem; padding: 0.75rem;">
                                    </td>
                                </tr>
                                <tr>
                                    <td style="background: #90EE90; color: #000; font-weight: 600; padding: 1rem; vertical-align: middle;">D4:</td>
                                    <td style="background: white; padding: 1rem;">
                                        <input type="text" name="debilidad_4" class="form-control" value="<?php echo htmlspecialchars($data['foda']['debilidad_4'] ?? ''); ?>" placeholder="Ingrese la cuarta debilidad" style="border: 1px solid rgba(15,47,70,.2); border-radius: 0.5rem; padding: 0.75rem;">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <button type="button" class="btn btn-save" onclick="guardarTabla('foda')">Guardar FODA</button>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="matriz_bcg.php" class="btn btn-nav">&laquo; Anterior: BCG</a>
                    <a href="dashboard.php" class="btn btn-nav-outline">Volver al Índice</a>
                    <a href="autodiagnostico_bdcg.php" class="btn btn-save">Siguiente: Análisis Porter &raquo;</a>
                </div>

            </form>

            <div class="card mb-4">
                <div class="card-body small text-muted">
                    <strong>Nota:</strong> Los cálculos de TCM (CAGR) se realizan entre 2012 y 2017 si hay datos completos. Las tasas periodicas por intervalo se usan como respaldo si faltan extremos.
                    Al guardar, los datos se almacenan en la sesión de PHP (persistencia temporal). Para almacenamiento permanente debería implementarse guardado en la base de datos.
                </div>
            </div>

        </div>
    </div>
</div>


<script>
// Funciones JS para cálculos interactivos
function fmt(n,dec=2){ return Number.isFinite(n)? n.toFixed(dec) : '0.00'; }

function calcularEnJS(){
    const numProd = <?php echo $numProd; ?>;
    const numYears = <?php echo $numYears; ?>;
    // Ventas
    const ventasEls = document.querySelectorAll('.venta-input');
    let ventas = Array.from(ventasEls).map(e=>parseFloat(e.value||0)||0);
    const total = ventas.reduce((a,b)=>a+b,0);
    document.getElementById('totalVentas').innerText = fmt(total,2);
    
    // Efecto visual para el total
    document.getElementById('totalVentas').classList.add('total-updated');
    setTimeout(() => {
        document.getElementById('totalVentas').classList.remove('total-updated');
    }, 500);
    
    // % sobre total
    document.querySelectorAll('.pct-venta').forEach((td,i)=>{ td.innerText = (total>0? (ventas[i]/total*100).toFixed(2) : '0.00') + '%'; });
    
    // Actualizar el porcentaje total
    document.getElementById('totalPorcentaje').innerText = '100.00%';

    // Demanda matrix
    let demanda = [];
    for (let y=0;y<numYears;y++){
        demanda[y]=[];
        for (let p=0;p<numProd;p++){
            const el = document.querySelector("input[name='demanda_"+y+"_"+p+"']");
            demanda[y][p] = parseFloat(el.value||0);
        }
    }

    // TCM por producto usando fórmula Excel: =SI(SUMA(D23:D27)/5>0.2,0.2,SUMA(D23:D27)/5)
    let tcms = [];
    for (let p=0;p<numProd;p++){
        let suma = 0;
        let count = 0;
        
        // Sumar todos los valores de la columna del producto
        for (let y=0;y<numYears;y++){
            const valor = parseFloat(demanda[y][p] || 0);
            suma += valor; // Sumar todos los valores, incluso los 0
            count++;
        }
        
        if (count > 0) {
            const promedio = suma / count;
            // Aplicar la fórmula: si promedio > 20%, entonces 20%, sino el promedio
            tcms[p] = (promedio > 20.0) ? 20.0 : promedio;
        } else {
            tcms[p] = 0;
        }
    }

    // Actualizar valores de EMPRESA
    document.querySelectorAll('.empresa-venta').forEach((el, i) => {
        el.innerText = ventas[i] || '0';
    });

    // Competidores
    const numComp = <?php echo $numCompetidores; ?>;
    let competidores = [];
    for (let p=0;p<numProd;p++){
        competidores[p]=[];
        for (let c=0;c<numComp;c++){
            const el = document.querySelector("input[name='comp_"+p+"_"+c+"']");
            const v = el? parseFloat(el.value||0) : 0;
            competidores[p].push(v);
        }
    }
    // Mayor por producto - calcular MAX de las filas de competidores
    for (let p=0;p<numProd;p++){
        const maxc = Math.max(...competidores[p],0);
        document.querySelector('.mayor-comp[data-prod="'+p+'"]').innerText = fmt(maxc,2);
    }

    // PRM
    const maxGlobal = Math.max(...ventas,0);
    let prms = [];
    for (let p=0;p<numProd;p++){
        const maxc = Math.max(...competidores[p],0);
        const ref = maxc>0? maxc : (maxGlobal>0? maxGlobal : 1);
        prms[p] = ref>0? ventas[p]/ref : 0;
    }

    // growth promedio
    const growth_prom = tcms.reduce((a,b)=>a+b,0)/tcms.length;

    // Clasificacion
    let clasif = [];
    for (let p=0;p<numProd;p++){
        const g = tcms[p];
        const r = prms[p];
        let cls = 'Perro';
        if (g > growth_prom && r >= 1) cls = 'Estrella';
        else if (g > growth_prom && r < 1) cls = 'Incógnita';
        else if (g <= growth_prom && r >= 1) cls = 'Vaca';
        clasif[p]=cls;
    }

    // Mostrar en tabla resultados
    document.querySelectorAll('.res-tcm').forEach((td,i)=> td.innerText = fmt(tcms[i],2)+' %');
    document.querySelectorAll('.res-prm').forEach((td,i)=> td.innerText = fmt(prms[i],3));
    document.querySelectorAll('.res-porc').forEach((td,i)=> td.innerText = fmt((total>0? ventas[i]/total*100 : 0),2)+' %');

}

// Eventos
// Calcular automáticamente cuando se cambie cualquier input de ventas
document.querySelectorAll('.venta-input').forEach(function(input) {
    input.addEventListener('input', function() {
        calcularEnJS();
    });
});

// Calcular automáticamente cuando se cambie cualquier input de demanda
document.querySelectorAll('.demanda-input').forEach(function(input) {
    input.addEventListener('input', function() {
        calcularEnJS();
    });
});

// Guardar automáticamente cuando se cambie cualquier input de demanda global
document.querySelectorAll('.demanda-global-input').forEach(function(input) {
    input.addEventListener('input', function() {
        // Los datos se guardan automáticamente en la sesión
    });
});

// Calcular automáticamente cuando se cambie cualquier input de competidores
document.querySelectorAll('.comp-input').forEach(function(input) {
    input.addEventListener('input', function() {
        calcularEnJS();
    });
});

// Función para guardar cada tabla individualmente
function guardarTabla(tabla) {
    const form = document.getElementById('formBCG');
    const formData = new FormData(form);
    
    // Agregar parámetro para identificar qué tabla guardar
    formData.append('tabla_guardar', tabla);
    
    fetch('autodiagnostico_bdcg.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        // Mostrar mensaje de éxito y recargar la página para ver los mensajes del servidor
        mostrarMensaje('Datos de ' + tabla + ' guardados correctamente', 'success');
        setTimeout(() => {
            location.reload();
        }, 2000);
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarMensaje('Error al guardar los datos', 'error');
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

// Calcular al cargar para mostrar valores guardados
window.addEventListener('load', function(){ calcularEnJS(); });

</script>

<?php
require_once 'includes/footer.php';
$mysqli->close();
?>
