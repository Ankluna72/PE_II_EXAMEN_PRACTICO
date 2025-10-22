<?php
require_once 'includes/db_connection.php';
require_once 'includes/header.php';

$mensaje = ''; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $email = $_POST['email'];
    $password = $_POST['password']; 
    $pais = $_POST['pais'];
    $tipo_user = 1; 

    $stmt = $mysqli->prepare("INSERT INTO usuario (nombre, apellido, email, password, pais, tipo_user) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $nombre, $apellido, $email, $password, $pais, $tipo_user);

    if ($stmt->execute()) {
        $mensaje = '<div class="alert alert-success">¡Usuario registrado con éxito! Ya puedes iniciar sesión.</div>';
    } else {
        if ($mysqli->errno == 1062) {
             $mensaje = '<div class="alert alert-danger">Error: El correo electrónico ya está registrado.</div>';
        } else {
             $mensaje = '<div class="alert alert-danger">Error al registrar el usuario: ' . $stmt->error . '</div>';
        }
    }
    $stmt->close();
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3>Crear una Cuenta</h3>
            </div>
            <div class="card-body">
                
                <?php echo $mensaje; ?>

                <form action="registro.php" method="POST">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="apellido" class="form-label">Apellido</label>
                        <input type="text" class="form-control" id="apellido" name="apellido" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo Electrónico</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="pais" class="form-label">País</label>
                        <input type="text" class="form-control" id="pais" name="pais" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Registrarse</button>
                </form>
                <div class="text-center mt-3">
                    <a href="login.php">¿Ya tienes una cuenta? Inicia Sesión</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>