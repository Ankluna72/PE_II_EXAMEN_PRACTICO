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

?>
<div class="container mt-4">
    <div class="module-container">
        <div class="module-header">
            <h2 class="module-title">5. ANÁLISIS INTERNO Y EXTERNO</h2>
        </div>
        <div class="module-content">
            <div class="row">
                <div class="col-lg-12">
                    <div class="explanation-box p-4 mb-4">
                        <p><strong>Una vez fijados los objetivos estratégicos, se deben analizar las distintas estrategias para lograrlos.</strong> Las estrategias son los caminos o enfoques que responden a la pregunta: <strong>¿cómo lo haremos?</strong></p>
                        <p>Este análisis nos permitirá llevar a cabo un estudio interno y externo de la empresa para obtener una matriz cruzada (FODA) e identificar la estrategia más conveniente. Por un lado, detectaremos los <strong>factores de éxito (fortalezas y oportunidades)</strong> y, por otro, las <strong>debilidades y amenazas</strong> que se deben gestionar.</p>
                    </div>

                    <div class="text-center mb-5">
                        <img src="https://encrypted-tbn3.gstatic.com/images?q=tbn:ANd9GcR505hV9kY2OcOu9Tus0SnghFajvp9TT9P9eQaUo4YthdU-1MKt" alt="Diagrama de Análisis FODA" class="img-fluid rounded">
                    </div>

                </div>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header text-white" style="background-color: #28a745;">
                            <h4>Análisis Externo</h4>
                        </div>
                        <div class="card-body">
                            <p>Consiste en identificar y analizar las <strong>Oportunidades</strong> y <strong>Amenazas</strong> del entorno que rodea a la empresa. Estos factores no son controlables por la organización.</p>
                            <hr>
                            
                            <h5 class="text-success">Oportunidades</h5>
                            <p>Son aquellos aspectos que pueden presentar una posibilidad para mejorar la rentabilidad de la empresa, aumentar la cifra de negocio y fortalecer la ventaja competitiva.</p>
                            <p><strong>Ejemplos:</strong> Fuerte crecimiento, desarrollo de la externalización, nuevas tecnologías, seguridad de la distribución, atender a grupos adicionales de clientes, crecimiento rápido del mercado, etc.</p>
                            <br>

                            <h5 class="text-danger">Amenazas</h5>
                            <p>Son fuerzas y presiones del mercado-entorno que pueden impedir o dificultar el crecimiento de la empresa, la ejecución de la estrategia, reducir su eficacia o incrementar los riesgos.</p>
                            <p><strong>Ejemplos:</strong> Competencia en el mercado, aparición de nuevos competidores, reglamentación, monopolio en una materia prima, cambio en las necesidades de los consumidores, etc.</p>
                            <hr>
                            <p class="mt-3"><strong>Instrumentos propuestos:</strong></p>
                            <ul>
                                <li>Análisis PEST (Macroentorno)</li>
                                <li>Las 5 Fuerzas de Porter (Microentorno)</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header text-white" style="background-color: #007bff;">
                            <h4>Análisis Interno</h4>
                        </div>
                        <div class="card-body">
                            <p>Consiste en identificar y analizar las <strong>Fortalezas</strong> y <strong>Debilidades</strong> propias de la organización. Estos factores sí son controlables por la empresa.</p>
                            <hr>

                            <h5 class="text-primary">Fortalezas</h5>
                            <p>Son capacidades, recursos, posiciones alcanzadas y ventajas competitivas que posee la empresa y que le ayudarán a aprovechar las oportunidades del mercado.</p>
                            <p><strong>Ejemplos:</strong> Buena implantación en el territorio, notoriedad de la marca, capacidad de innovación, recursos financieros adecuados, ventajas en costes, líder en el mercado, etc.</p>
                            <br>

                            <h5 class="text-warning">Debilidades</h5>
                            <p>Son todos aquellos aspectos que limitan o reducen la capacidad de desarrollo de la empresa. Constituyen dificultades para la organización y deben ser controladas y superadas.</p>
                            <p><strong>Ejemplos:</strong> Precios elevados, productos en el final de su ciclo de vida, deficiente control de los riesgos, recursos humanos poco cualificados, débil imagen en el mercado, etc.</p>
                             <hr>
                            <p class="mt-3"><strong>Instrumentos propuestos:</strong></p>
                            <ul>
                                <li>Cadena de Valor</li>
                                <li>Matriz de Participación - Crecimiento (BCG)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="objetivos.php" class="btn btn-nav">&laquo; Anterior: Objetivos</a>
                <a href="dashboard.php" class="btn btn-nav-outline">Volver al Índice</a>
                <a href="cadena_valor.php" class="btn btn-save">Siguiente: Cadena de Valor &raquo;</a>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
$mysqli->close();
?>