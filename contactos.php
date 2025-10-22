<?php
$pageStyles = ['css/contactos.css'];
require_once 'includes/header.php';

$exito = false;
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mensaje = trim($_POST['mensaje'] ?? '');

    if ($nombre === '') { $errores[] = 'El nombre es obligatorio.'; }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errores[] = 'Email no válido.'; }
    if ($mensaje === '') { $errores[] = 'El mensaje es obligatorio.'; }

    if (empty($errores)) {
        $exito = true;
        // Aquí podrías enviar un correo o guardar en DB si lo deseas
    }
}
?>

 

<nav class="navbar navbar-expand-lg nav-minimal rounded px-3 py-3">
    <a class="navbar-brand" href="/NextStrategy-IT/">
        <img src="images/logo.png" alt="NexStrategy-IT">
    </a>
    <div class="ms-auto d-flex align-items-center gap-2">
        <a href="index.php" class="btn btn-outline-secondary">Inicio</a>
        <a href="login.php" class="btn btn-brand">Iniciar sesión</a>
    </div>
</nav>

<section class="mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-minimal">
                <div class="card-body p-4">
                    <h3 class="section-title mb-3">Contáctanos</h3>
                    <p class="text-muted">Escríbenos y te responderemos pronto.</p>
                    <?php if (!empty($errores)): ?>
                        <div class="alert alert-danger"><?php echo implode('<br>', $errores); ?></div>
                    <?php endif; ?>
                    <?php if ($exito): ?>
                        <div class="alert alert-success">Mensaje enviado correctamente.</div>
                    <?php endif; ?>
                    <form action="contactos.php" method="POST">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" value="" placeholder="Tu nombre">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="" placeholder="tucorreo@dominio.com">
                        </div>
                        <div class="mb-3">
                            <label for="mensaje" class="form-label">Mensaje</label>
                            <textarea class="form-control" id="mensaje" name="mensaje" rows="5" placeholder="Cuéntanos cómo podemos ayudarte..."></textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-brand">Enviar</button>
                            <a href="contactos.php" class="btn btn-outline-secondary">Limpiar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>


