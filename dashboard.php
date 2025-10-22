<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit();
}

$pageStyles = ['css/dashboard.css', 'css/collaboration.css'];
require_once 'includes/db_connection.php';
require_once 'includes/header.php'; // Incluye Bootstrap CSS

$id_usuario_actual = $_SESSION['id_usuario'];
$nombre_usuario_actual = $_SESSION['nombre'];

// --- Lógica para seleccionar o crear empresa/proyecto ---

// Variable para el ID de la empresa actualmente seleccionada
$id_empresa_seleccionada = null;
if (isset($_SESSION['id_empresa_actual'])) {
    $id_empresa_seleccionada = $_SESSION['id_empresa_actual'];
}

// Obtener las empresas del usuario actual (propias y colaborativas)
$empresas = [];
$stmt = $mysqli->prepare("SELECT id, nombre_empresa FROM empresa WHERE id_usuario = ?");
$stmt->bind_param("i", $id_usuario_actual);
$stmt->execute();
$resultado_empresas = $stmt->get_result();
while ($fila = $resultado_empresas->fetch_assoc()) {
    $fila['tipo'] = 'propia';
    $empresas[] = $fila;
}
$stmt->close();

// Obtener empresas donde el usuario es colaborador
$stmt_colaborativas = $mysqli->prepare("
    SELECT e.id, e.nombre_empresa, u.nombre, u.apellido 
    FROM empresa e 
    JOIN colaboradores_empresa c ON e.id = c.id_empresa 
    JOIN usuario u ON e.id_usuario = u.id 
    WHERE c.id_usuario_colaborador = ? AND c.estado = 'activo'
");
$stmt_colaborativas->bind_param("i", $id_usuario_actual);
$stmt_colaborativas->execute();
$resultado_colaborativas = $stmt_colaborativas->get_result();
while ($fila = $resultado_colaborativas->fetch_assoc()) {
    $fila['tipo'] = 'colaborativa';
    $fila['propietario'] = $fila['nombre'] . ' ' . $fila['apellido'];
    $empresas[] = $fila;
}
$stmt_colaborativas->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['seleccionar_empresa_id'])) {
        $_SESSION['id_empresa_actual'] = $_POST['seleccionar_empresa_id'];
        // Redirigir para refrescar y cargar el dashboard con la empresa seleccionada
        header('Location: dashboard.php');
        exit();
    } elseif (isset($_POST['nueva_nombre_empresa'])) {
        $nueva_nombre_empresa = $_POST['nueva_nombre_empresa'];
        // Insertar la nueva empresa
        $stmt_insert = $mysqli->prepare("INSERT INTO empresa (id_usuario, nombre_empresa) VALUES (?, ?)");
        $stmt_insert->bind_param("is", $id_usuario_actual, $nueva_nombre_empresa);
        if ($stmt_insert->execute()) {
            $_SESSION['id_empresa_actual'] = $mysqli->insert_id; // Guardar el ID de la nueva empresa
            header('Location: dashboard.php'); // Redirigir al dashboard con la nueva empresa seleccionada
            exit();
        } else {
            echo '<div class="alert alert-danger">Error al crear la nueva empresa.</div>';
        }
        $stmt_insert->close();
    }
}

// Obtener el nombre de la empresa seleccionada y verificar si es propietario
$nombre_empresa_actual = "Ninguna seleccionada";
$es_propietario_empresa = false;
if ($id_empresa_seleccionada) {
    $stmt_nombre = $mysqli->prepare("SELECT nombre_empresa, id_usuario FROM empresa WHERE id = ?");
    $stmt_nombre->bind_param("i", $id_empresa_seleccionada);
    $stmt_nombre->execute();
    $stmt_nombre->bind_result($nombre_empresa_actual_db, $id_propietario_empresa);
    $stmt_nombre->fetch();
    $stmt_nombre->close();
    if ($nombre_empresa_actual_db) {
        $nombre_empresa_actual = $nombre_empresa_actual_db;
        $es_propietario_empresa = ($id_propietario_empresa == $id_usuario_actual);
    }
}
?>

<style>
    body {
        background-color: #1a1a2e; /* Fondo oscuro */
        color: #e0e0e0; /* Texto claro */
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .navbar {
        background: linear-gradient(to right, #f8f9fa, #0f3460) !important;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
    }
    .navbar .navbar-brand {
        color: #16213e !important;
        font-weight: 700;
        text-shadow: none !important;
    }
    .navbar .navbar-text {
        color: #f0f0f0 !important;
    }
    .navbar .navbar-text strong {
        color: #ffffff !important;
    }
    .card {
        background-color: #16213e; /* Fondo de tarjeta más oscuro */
        border: 1px solid #0f3460;
        box-shadow: 0 0 15px rgba(255,255,255,0.05);
    }
    .card-header {
        background-color: #0f3460;
        color: #ffffffff; 
        font-weight: bold;
        border-bottom: 1px solid #1a1a2e;
    }
    .btn-primary, .btn-outline-primary {
        background-color: #e94560;
        border-color: #e94560;
        color: white;
    }
    .btn-primary:hover, .btn-outline-primary:hover {
        background-color: #ff6a80;
        border-color: #ff6a80;
    }
    .intro-section {
        background: linear-gradient(45deg, #0f3460, #1a1a2e);
        padding: 4rem 2rem;
        border-radius: 10px;
        margin-bottom: 3rem;
        text-align: center;
        box-shadow: 0 8px 16px rgba(0,0,0,0.4);
    }
    .intro-section h1 {
        color: #a4d6ffff;
        font-weight: 700;
        margin-bottom: 1rem;
    }
    .intro-section p {
        font-size: 1.1rem;
        max-width: 800px;
        margin: 0 auto;
        line-height: 1.8;
    }
    .module-button {
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        height: 120px; /* Tamaño fijo para los botones */
        font-size: 1.1rem;
        font-weight: bold;
        color: #e0e0e0;
        background-color: #2a3a5e;
        border: 1px solid #0f3460;
        border-radius: 8px;
        transition: all 0.3s ease;
        text-decoration: none; /* Quitar subrayado del link */
    }
    .module-button:hover {
        background-color: #0f3460;
        color: #a4d6ffff;
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.5);
    }
    .sticky-top-actions {
        position: sticky;
        top: 0;
        z-index: 1020; /* Asegura que esté por encima del contenido */
        background-color: #1a1a2e; /* Fondo para que no se vea a través */
        padding-top: 1rem;
        padding-bottom: 1rem;
        margin-bottom: 1rem;
        border-bottom: 1px solid #0f3460;
    }
</style>

<nav class="navbar navbar-expand-lg navbar-dashboard mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">
            <img src="images/logo.png" alt="NexStrategy-IT" style="height: 40px; margin-right: 10px;">
            Plan Estratégico de TI
        </a>
        <div class="d-flex">
            <span class="navbar-text me-3">
                Bienvenido, <strong><?php echo htmlspecialchars($nombre_usuario_actual); ?></strong>
            </span>
            <a href="logout.php" class="btn btn-logout btn-sm">Cerrar Sesión</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="intro-section">
        <h1>Generador de Plan Estratégico de TI</h1>
        <p>Bienvenido al módulo interactivo para construir y gestionar el Plan Estratégico de TI de tu organización. Aquí podrás definir, analizar y consolidar los pilares que impulsarán el éxito tecnológico de tu empresa.</p>
        <p>A través de un proceso guiado, estructurarás la visión, misión, valores, objetivos, y realizarás análisis clave como FODA, PEST, Cadena de Valor y Matriz CAME, culminando en un resumen ejecutivo integral. ¡Comencemos a trazar el futuro digital de tu empresa!</p>
    </div>

    <div class="row mb-4 align-items-center sticky-top-actions">
        <div class="col-md-6">
            <h4 class="mb-0 text-white-50">Empresa Actual: <span class="company-current"><?php echo htmlspecialchars($nombre_empresa_actual); ?></span></h4>
        </div>
        <div class="col-md-6 text-end">
            <?php if ($es_propietario_empresa && $id_empresa_seleccionada): ?>
                <a href="gestionar_colaboradores.php" class="btn-invite-collaborators me-2">
                    <i class="fas fa-user-plus"></i> Invitar Colaboradores
                </a>
            <?php endif; ?>
            <button class="btn btn-outline-light me-2" data-bs-toggle="modal" data-bs-target="#seleccionarEmpresaModal">
                Cambiar Proyecto
            </button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#crearEmpresaModal">
                <i class="fas fa-plus-circle"></i> Crear Nuevo Proyecto
            </button>
        </div>
    </div>

    <h2 class="text-white-50 mb-3">INFORMACIÓN DE LA EMPRESA</h2>
    
    <!-- Sección de imagen de la empresa -->
    <?php if ($es_propietario_empresa && $id_empresa_seleccionada): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="empresa-image-section">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="text-white mb-2">
                            <i class="fas fa-image me-2"></i>Logo/Imagen de la Empresa
                        </h5>
                        <p class="text-white-50 mb-0">Sube una imagen representativa de tu empresa para el plan ejecutivo</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <button class="btn btn-outline-light btn-sm" data-bs-toggle="modal" data-bs-target="#uploadImageModal">
                            <i class="fas fa-upload me-2"></i>Subir Imagen
                        </button>
                    </div>
                </div>
                
                <!-- Mostrar imagen actual si existe -->
                <?php 
                $stmt_img = $mysqli->prepare("SELECT imagen FROM empresa WHERE id = ?");
                $stmt_img->bind_param("i", $id_empresa_seleccionada);
                $stmt_img->execute();
                $stmt_img->bind_result($imagen_actual);
                $stmt_img->fetch();
                $stmt_img->close();
                
                if (!empty($imagen_actual)): ?>
                <div class="current-image-preview mt-3">
                    <img src="uploads/empresa_images/<?php echo htmlspecialchars($imagen_actual); ?>" 
                         alt="Imagen actual" class="current-empresa-image">
                    <div class="image-actions mt-2">
                        <button class="btn btn-danger btn-sm" onclick="deleteImage()">
                            <i class="fas fa-trash me-1"></i>Eliminar
                        </button>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <a href="mision.php" class="module-button">1. MISIÓN</a>
        </div>
        <div class="col-md-3">
            <a href="vision.php" class="module-button">2. VISIÓN</a>
        </div>
        <div class="col-md-3">
            <a href="valores.php" class="module-button">3. VALORES</a>
        </div>
        <div class="col-md-3">
            <a href="objetivos.php" class="module-button">4. OBJETIVOS</a>
        </div>
    </div>

    <h2 class="text-white-50 mb-3">ANÁLISIS ESTRATÉGICO</h2>
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <a href="analisis_info.php" class="module-button">5. ANÁLISIS INTERNO Y EXTERNO (FODA)</a>
        </div>
        <div class="col-md-4">
            <a href="cadena_valor.php" class="module-button">6. CADENA DE VALOR</a>
        </div>
        <div class="col-md-4">
            <a href="matriz_bcg.php" class="module-button">7. MATRIZ BCG (Participación)</a>
        </div>
        <div class="col-md-4">
            <a href="porter_5fuerzas.php" class="module-button">8. LAS 5 FUERZAS DE PORTER</a>
        </div>
        <div class="col-md-4">
            <a href="analisis_pest.php" class="module-button">9. PEST</a>
        </div>
        <div class="col-md-4">
            <a href="identificacion_estrategia.php" class="module-button">10. IDENTIFICACIÓN ESTRATEGIA</a>
        </div>
        <div class="col-md-4">
            <a href="matriz_came.php" class="module-button">11. MATRIZ CAME</a>
        </div>
    </div>

    <h2 class="text-white-50 mb-3">PLAN EJECUTIVO</h2>
    <div class="row g-4 mb-5">
        <div class="col-12">
            <a href="resumen_plan.php" class="module-button btn-success">RESUMEN DEL PLAN EJECUTIVO</a>
        </div>
    </div>
</div>

<div class="modal fade" id="seleccionarEmpresaModal" tabindex="-1" aria-labelledby="seleccionarEmpresaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header">
                <h5 class="modal-title" id="seleccionarEmpresaModalLabel">Seleccionar Empresa/Proyecto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if (empty($empresas)): ?>
                    <p>No tienes empresas creadas. Por favor, crea una primero.</p>
                <?php else: ?>
                    <form action="dashboard.php" method="POST">
                        <div class="mb-3">
                            <label for="empresaSelect" class="form-label">Elige una empresa:</label>
                            <select class="form-select" id="empresaSelect" name="seleccionar_empresa_id" required>
                                <?php foreach ($empresas as $emp): ?>
                                    <option value="<?php echo htmlspecialchars($emp['id']); ?>"
                                        <?php echo ($emp['id'] == $id_empresa_seleccionada) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($emp['nombre_empresa']); ?>
                                        <?php if ($emp['tipo'] === 'colaborativa'): ?>
                                            (Colaboración - <?php echo htmlspecialchars($emp['propietario']); ?>)
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Seleccionar</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="crearEmpresaModal" tabindex="-1" aria-labelledby="crearEmpresaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header">
                <h5 class="modal-title" id="crearEmpresaModalLabel">Crear Nuevo Proyecto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="dashboard.php" method="POST">
                    <div class="mb-3">
                        <label for="nuevaEmpresaNombre" class="form-label">Nombre del Nuevo Proyecto:</label>
                        <input type="text" class="form-control" id="nuevaEmpresaNombre" name="nueva_nombre_empresa" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Crear Proyecto</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php'; 
$mysqli->close();
?>

<!-- Modal para subir imagen -->
<div class="modal fade" id="uploadImageModal" tabindex="-1" aria-labelledby="uploadImageModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadImageModalLabel">
                    <i class="fas fa-image me-2"></i>Subir Imagen de la Empresa
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="uploadImageForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="empresa_image" class="form-label">Seleccionar imagen</label>
                        <input type="file" class="form-control" id="empresa_image" name="empresa_image" 
                               accept="image/*" required>
                        <div class="form-text text-white-50">
                            Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 5MB
                        </div>
                    </div>
                    
                    <!-- Preview de la imagen -->
                    <div id="imagePreview" class="mt-3" style="display: none;">
                        <img id="previewImg" src="" alt="Preview" class="img-preview">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload me-2"></i>Subir Imagen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Preview de imagen
document.getElementById('empresa_image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('imagePreview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});

// Subir imagen
document.getElementById('uploadImageForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData();
    const fileInput = document.getElementById('empresa_image');
    
    if (fileInput.files.length === 0) {
        alert('Por favor selecciona una imagen');
        return;
    }
    
    formData.append('empresa_image', fileInput.files[0]);
    formData.append('id_empresa', <?php echo $id_empresa_seleccionada ?? 0; ?>);
    
    fetch('upload_empresa_image.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Imagen subida correctamente');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al subir la imagen');
    });
});

// Eliminar imagen
function deleteImage() {
    if (confirm('¿Estás seguro de que quieres eliminar la imagen?')) {
        fetch('delete_empresa_image.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                id_empresa: <?php echo $id_empresa_seleccionada ?? 0; ?>
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Imagen eliminada correctamente');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}
</script>