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
            <h2 class="module-title">7. ANÁLISIS INTERNO: MATRIZ DE CRECIMIENTO - PARTICIPACIÓN BCG</h2>
        </div>

        <div class="module-content">
            <div class="explanation-box p-4 mb-5">

                <p>Toda empresa debe analizar de forma periódica su cartera de productos y servicios.</p>

                <p>La <strong>Matriz de Crecimiento - Participación</strong>, conocida como <strong>Matriz BCG</strong>, es un método gráfico de análisis de cartera de negocios desarrollado por <em>The Boston Consulting Group</em> en la década de 1970.</p>

                <p>Su finalidad es ayudar a priorizar recursos entre distintas áreas de negocios o Unidades Estratégicas de Análisis (UEA), es decir, determinar en qué negocios se debe <strong>invertir, desinvertir o incluso abandonar</strong>.</p>

                <p>Se trata de una matriz con cuatro cuadrantes, cada uno de los cuales propone una estrategia diferente para una unidad de negocio. Cada cuadrante viene representado por una figura o ícono. El eje vertical indica el <strong>crecimiento del mercado</strong>, y el eje horizontal la <strong>participación relativa en el mercado</strong>.</p>

                <!-- 🔹 DIAGRAMA DE LA MATRIZ BCG -->
                <div class="matriz-container">
                    <div class="eje-y">CRECIMIENTO (+)</div>
                    <div class="cuadrante incognita">
                        <div class="icono">❓</div>
                        <p>INCÓGNITA</p>
                    </div>
                    <div class="cuadrante estrella">
                        <div class="icono">⭐</div>
                        <p>ESTRELLA</p>
                    </div>
                    <div class="cuadrante perro">
                        <div class="icono">🐶</div>
                        <p>PERRO</p>
                    </div>
                    <div class="cuadrante vaca">
                        <div class="icono">🐮</div>
                        <p>VACA</p>
                    </div>
                    <div class="eje-x">(-) PARTICIPACIÓN RELATIVA EN EL MERCADO (+)</div>
                </div>

                <hr class="my-5">

                <!-- 🔹 CUADRO RESUMEN -->
                <h4 class="text-center mb-3">CUADRO RESUMEN DE LAS PRINCIPALES CARACTERÍSTICAS</h4>

                <div class="table-responsive">
                    <table class="table table-bordered bcg-table">
                        <thead class="table-light text-center">
                            <tr>
                                <th>Características</th>
                                <th>Estrella ⭐</th>
                                <th>Incógnita ❓</th>
                                <th>Vaca 🐄</th>
                                <th>Perro 🐶</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Cuota de mercado</td>
                                <td>Alta</td>
                                <td>Baja</td>
                                <td>Alta</td>
                                <td>Baja</td>
                            </tr>
                            <tr>
                                <td>Crecimiento del mercado</td>
                                <td>Alto</td>
                                <td>Alto</td>
                                <td>Bajo</td>
                                <td>Bajo</td>
                            </tr>
                            <tr>
                                <td>Estrategia en función de la participación en el mercado</td>
                                <td>Crecer o mantenerse</td>
                                <td>Crecer</td>
                                <td>Mantenerse</td>
                                <td>Cosechar o desinvertir</td>
                            </tr>
                            <tr>
                                <td>Inversión requerida</td>
                                <td>Alta</td>
                                <td>Muy alta</td>
                                <td>Baja</td>
                                <td>Baja, desinvertir</td>
                            </tr>
                            <tr>
                                <td>Rentabilidad</td>
                                <td>Alta</td>
                                <td>Baja o negativa</td>
                                <td>Alta</td>
                                <td>Muy baja o negativa</td>
                            </tr>
                            <tr>
                                <td><strong>DECISIÓN ESTRATÉGICA</strong></td>
                                <td><strong>POTENCIAR</strong></td>
                                <td><strong>EVALUAR</strong></td>
                                <td><strong>MANTENER</strong></td>
                                <td><strong>REESTRUCTURAR o DESINVERTIR</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <p class="mt-4">La situación idónea es tener una cartera <strong>equilibrada</strong>, es decir, productos o servicios con diferentes niveles de crecimiento y participación en el mercado.</p>

            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="autodiagnostico_cadena_valor.php" class="btn btn-nav">&laquo; Anterior: Autodiagnóstico de la CVI</a>
                <a href="dashboard.php" class="btn btn-nav-outline">Volver al Índice</a>
                <a href="autodiagnostico_bdcg.php" class="btn btn-save">Siguiente: Autodiagnóstico BCG &raquo;</a>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
$mysqli->close();
?>
