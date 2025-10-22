<?php
session_start();

if (isset($_SESSION['id_usuario'])) {
    header('Location: dashboard.php');
    exit();
}

$pageStyles = ['css/login.css'];
require_once 'includes/db_connection.php'; 
require_once 'includes/header.php';

$mensaje = '';
$email = '';

// Muestra el mensaje de éxito si existe
if (isset($_SESSION['registration_success'])) {
    $mensaje = '<div class="alert alert-success">' . $_SESSION['registration_success'] . '</div>';
    unset($_SESSION['registration_success']);
}

$debug = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if ($email === '' || $password === '') {
        $mensaje = '<div class="alert alert-warning">Por favor completa todos los campos.</div>';
    } else {
        $sql = "SELECT id, nombre, password FROM usuario WHERE email = ? LIMIT 1";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $resultado = $stmt->get_result();

            if ($resultado && $resultado->num_rows === 1) {
                $usuario = $resultado->fetch_assoc();

                if ($password === $usuario['password']) {
                    $_SESSION['id_usuario'] = $usuario['id'];
                    $_SESSION['nombre'] = $usuario['nombre'];

                    $stmt->close();
                    header('Location: dashboard.php');
                    exit();
                } else {
                    $mensaje = '<div class="alert alert-danger">Contraseña incorrecta.</div>';
                }
            } else {
                $mensaje = '<div class="alert alert-danger">No se encontró un usuario con ese correo electrónico.</div>';
            }

            $stmt->close();
        } else {
            $mensaje = '<div class="alert alert-danger">Error en la consulta. Contacta al administrador.</div>';
            if ($debug) {
                $mensaje .= '<div class="alert alert-info">Error MySQL: ' . htmlspecialchars($mysqli->error) . '</div>';
            }
        }
    }
}
?>

<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <h3>Iniciar Sesión</h3>
            <p class="mb-0">Accede a tu cuenta para gestionar tu estrategia</p>
        </div>
        <div class="login-body">
            <?php echo $mensaje; ?>
            <form action="login.php" method="POST" autocomplete="off">
                <div class="mb-3">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        class="form-control"
                        required
                        value="<?php echo htmlspecialchars($email); ?>"
                        autofocus
                        placeholder="tu@email.com"
                    >
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Contraseña</label>
                    <input
                        type="password"
                        name="password"
                        id="password"
                        class="form-control"
                        required
                        placeholder="Tu contraseña"
                    >
                </div>
                <button type="submit" class="btn btn-login">Ingresar</button>
            </form>
            <div class="text-center mt-3">
                <a href="index.php" class="btn btn-outline-brand">Volver al inicio</a>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
