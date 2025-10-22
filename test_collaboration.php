<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit();
}

$pageStyles = ['css/collaboration.css'];
require_once 'includes/db_connection.php';
require_once 'includes/access_control.php';
require_once 'includes/header.php';

$id_usuario_actual = $_SESSION['id_usuario'];
?>

<div class="container mt-4">
    <div class="module-container">
        <div class="module-header">
            <h2 class="module-title">Test del Sistema de Colaboración</h2>
        </div>
        <div class="module-content">
            <div class="row">
                <div class="col-md-6">
                    <h5>Empresas Propias</h5>
                    <?php
                    $stmt_propias = $mysqli->prepare("SELECT id, nombre_empresa FROM empresa WHERE id_usuario = ?");
                    $stmt_propias->bind_param("i", $id_usuario_actual);
                    $stmt_propias->execute();
                    $empresas_propias = $stmt_propias->get_result()->fetch_all(MYSQLI_ASSOC);
                    $stmt_propias->close();
                    
                    if (empty($empresas_propias)) {
                        echo '<p class="text-muted">No tienes empresas propias.</p>';
                    } else {
                        foreach ($empresas_propias as $empresa) {
                            echo '<div class="collaborative-enterprise-item">';
                            echo '<div class="enterprise-info">';
                            echo '<h6>' . htmlspecialchars($empresa['nombre_empresa']) . '</h6>';
                            echo '<small>Propietario</small>';
                            echo '</div>';
                            echo '<a href="gestionar_colaboradores.php" class="btn btn-sm btn-primary">Gestionar</a>';
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
                
                <div class="col-md-6">
                    <h5>Empresas Colaborativas</h5>
                    <?php
                    $stmt_colaborativas = $mysqli->prepare("
                        SELECT e.id, e.nombre_empresa, u.nombre, u.apellido 
                        FROM empresa e 
                        JOIN colaboradores_empresa c ON e.id = c.id_empresa 
                        JOIN usuario u ON e.id_usuario = u.id 
                        WHERE c.id_usuario_colaborador = ? AND c.estado = 'activo'
                    ");
                    $stmt_colaborativas->bind_param("i", $id_usuario_actual);
                    $stmt_colaborativas->execute();
                    $empresas_colaborativas = $stmt_colaborativas->get_result()->fetch_all(MYSQLI_ASSOC);
                    $stmt_colaborativas->close();
                    
                    if (empty($empresas_colaborativas)) {
                        echo '<p class="text-muted">No eres colaborador de ninguna empresa.</p>';
                    } else {
                        foreach ($empresas_colaborativas as $empresa) {
                            echo '<div class="collaborative-enterprise-item">';
                            echo '<div class="enterprise-info">';
                            echo '<h6>' . htmlspecialchars($empresa['nombre_empresa']) . '</h6>';
                            echo '<small>Colaboración con ' . htmlspecialchars($empresa['nombre'] . ' ' . $empresa['apellido']) . '</small>';
                            echo '</div>';
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
            </div>
            
            <div class="mt-4">
                <a href="dashboard.php" class="btn btn-primary">Volver al Dashboard</a>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
$mysqli->close();
?>
