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

$pageStyles = ['css/modules.css']; // invoca los estilos del módulo
require_once 'includes/db_connection.php';
require_once 'includes/header.php';
?>

<div class="container mt-4">
    <div class="module-container">
        <div class="module-header">
            <h2 class="module-title">6. ANÁLISIS INTERNO: LA CADENA DE VALOR</h2>
        </div>

        <div class="module-content">

            <div class="explanation-box p-4 mb-5">
                <p>Todas las actividades de una empresa forman la <strong>cadena de valor</strong>.</p>

                <p>La <strong>Cadena de Valor</strong> es una herramienta que permite a la empresa identificar aquellas actividades o fases que pueden aportarle un mayor valor añadido al producto final. 
                Intenta buscar fuentes de <strong>ventaja competitiva</strong>.</p>

                <p>La empresa está formada por una secuencia de actividades diseñadas para añadir valor al producto o servicio según las distintas fases, hasta que se llega al cliente final.</p>

                <p>Una cadena de valor genérica está constituida por tres elementos básicos:</p>

                <!-- 🔹 DIAGRAMA CONCEPTUAL -->
                <div class="value-chain-diagram">
                    <div class="value-row">
                        <div class="value-box">Actividades primarias</div>
                        <div class="value-arrow">➜</div>
                        <div class="value-description">Transformación de inputs y relación con el cliente</div>
                    </div>
                    <div class="value-row">
                        <div class="value-box">Actividades de apoyo</div>
                        <div class="value-arrow">➜</div>
                        <div class="value-description">Estructura de la empresa para desarrollar todo el proceso</div>
                    </div>
                    <div class="value-row">
                        <div class="value-box">Margen</div>
                        <div class="value-arrow">➜</div>
                        <div class="value-description">Valor obtenido por la empresa en relación a los costos</div>
                    </div>
                </div>

                <hr>

                <h4>Actividades Primarias</h4>
                <p>Son aquellas que tienen que ver con el producto/servicio, su producción, logística, comercialización, etc.</p>

                <ul>
                    <li><strong>Logística de entrada:</strong> recepción, almacenamiento, manipulación de materiales, inspección interna, devoluciones, inventarios…</li>
                    <li><strong>Operaciones:</strong> proceso de fabricación, ensamblaje, mantenimiento de equipos, mecanización, embalaje…</li>
                    <li><strong>Logística de salida:</strong> gestión de pedidos, honorarios, almacenamiento de producto terminado, transporte…</li>
                    <li><strong>Marketing y ventas:</strong> comercialización, selección del canal de distribución, publicidad, promoción, política de precios…</li>
                    <li><strong>Servicios:</strong> reparaciones, instalación, mantenimiento, postventa, reclamaciones, reajustes del producto…</li>
                </ul>

                <h4>Actividades de Soporte o Apoyo</h4>
                <p>Apoyan a las actividades primarias. Incluyen:</p>

                <ul>
                    <li><strong>Infraestructura empresarial:</strong> administración, finanzas, contabilidad, calidad, relaciones públicas, asesoría legal, gerencia…</li>
                    <li><strong>Gestión de recursos humanos:</strong> selección, contratación, formación, incentivos…</li>
                    <li><strong>Desarrollo tecnológico:</strong> telecomunicaciones, automatización, ingeniería, diseño, saber hacer, I+D…</li>
                    <li><strong>Abastecimiento:</strong> compras de materias primas, consumibles, equipamientos y servicios…</li>
                </ul>

                <h4>El Margen</h4>
                <p>Es la diferencia entre el valor total obtenido y los costes incurridos por la empresa para desempeñar las actividades generadoras de valor.</p>

                <!-- 🔹 DIAGRAMA ESTRUCTURAL DE LA CADENA (como en tu imagen) -->
                <div class="cadena-valor-estructura mt-4">
                    <div class="columna-apoyo">
                        <div class="texto-vertical">ACTIVIDADES DE APOYO</div>
                    </div>

                    <div class="cuerpo-cadena">
                        <div class="fila-apoyo infraestructura">INFRAESTRUCTURA DE LA EMPRESA</div>
                        <div class="fila-apoyo recursos">GESTIÓN DE RECURSOS HUMANOS</div>
                        <div class="fila-apoyo compras">COMPRAS</div>
                        <div class="fila-apoyo desarrollo">DESARROLLO DE TECNOLOGÍAS</div>

                        <div class="actividades-primarias">
                            <div class="caja">Logística Interna</div>
                            <div class="caja">Operaciones</div>
                            <div class="caja">Logística Externa</div>
                            <div class="caja">Marketing y Ventas</div>
                            <div class="caja">Servicios</div>
                        </div>

                        <!-- >>> ETIQUETA AGREGADA: ACTIVIDADES PRIMARIAS <<< -->
                        <div class="label-primarias">ACTIVIDADES PRIMARIAS</div>
                    </div>

                    <div class="columna-margen">
                        <span>M<br>a<br>r<br>g<br>e<br>n</span>
                    </div>
                </div>

                <p class="mt-3">Cada eslabón de la cadena puede ser fuente de <strong>ventaja competitiva</strong>, ya sea porque se optimice (excelencia en la ejecución de una actividad) y/o mejore su coordinación con otra actividad.</p>

                <p class="mt-3"><em>A continuación se propone un autodiagnóstico de la cadena de valor interna para conocer porcentualmente el potencial de mejora de la cadena de valor.</em></p>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="analisis_info.php" class="btn btn-nav">&laquo; Análisis Interno y Externo</a>
                <a href="dashboard.php" class="btn btn-nav-outline">Volver al Índice</a>
                <a href="autodiagnostico_cadena_valor.php" class="btn btn-save">Siguiente: Autodiagnóstico de la CVI &raquo;</a>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
$mysqli->close();
?>
