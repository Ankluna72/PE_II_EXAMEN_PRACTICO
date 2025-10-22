<?php
session_start();

$errors = [];
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once 'includes/db_connection.php'; // Mover la conexión aquí para evitar abrirla innecesariamente

    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $pais = trim($_POST['pais']);

    if (empty($nombre)) {
        $errors[] = "El nombre es obligatorio.";
    }
    if (empty($apellido)) {
        $errors[] = "El apellido es obligatorio.";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "El correo electrónico no es válido.";
    }
    if (empty($password) || strlen($password) < 8) {
        $errors[] = "La contraseña debe tener al menos 8 caracteres.";
    }
    if (empty($pais)) {
        $errors[] = "El país es obligatorio.";
    }

    // Verificar si el email ya existe
    if (empty($errors)) {
        $stmt = $mysqli->prepare("SELECT id FROM usuario WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "El correo electrónico ya está registrado.";
        }
        $stmt->close();
    }

    if (empty($errors)) {
        $plaintext_password = $password; // Guardar contraseña como texto plano
        $tipo_user = 2; // Usuario normal

        $stmt = $mysqli->prepare("INSERT INTO usuario (nombre, apellido, email, password, pais, tipo_user) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssi", $nombre, $apellido, $email, $plaintext_password, $pais, $tipo_user);
        
        if ($stmt->execute()) {
            $_SESSION['registration_success'] = "¡Registro exitoso! Ahora puedes iniciar sesión.";
            $stmt->close();
            $mysqli->close();
            header("Location: login.php");
            exit;
        } else {
            $errors[] = "Error al registrar el usuario: " . $stmt->error;
        }
        $stmt->close();
    }
    
    // Cerrar la conexión si se abrió
    if (isset($mysqli)) {
        $mysqli->close();
    }
}

$pageStyles = ['css/register.css'];
require_once 'includes/header.php';
?>

<div class="login-container">
    <div class="login-card">
        <div class="text-center mb-4">
            <a href="/NextStrategy-IT/">
                <img src="images/logo.png" alt="Logo" class="logo">
            </a>
            <h2 class="mt-2">Crear una cuenta</h2>
            <p class="text-muted">Únete para empezar a planificar la estrategia de tu empresa.</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST" novalidate>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="apellido" class="form-label">Apellido</label>
                    <input type="text" class="form-control" id="apellido" name="apellido" required value="<?php echo isset($_POST['apellido']) ? htmlspecialchars($_POST['apellido']) : ''; ?>">
                </div>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Correo electrónico</label>
                <input type="email" class="form-control" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="password" name="password" required>
                 <div class="form-text">La contraseña debe tener al menos 8 caracteres.</div>
            </div>
            <div class="mb-3">
                <label for="pais" class="form-label">País</label>
                <select class="form-select" id="pais" name="pais" required>
                    <?php
                    $countries = ["Afganistán", "Albania", "Alemania", "Andorra", "Angola", "Anguila", "Antártida", "Antigua y Barbuda", "Arabia Saudita", "Argelia", "Argentina", "Armenia", "Aruba", "Australia", "Austria", "Azerbaiyán", "Bahamas", "Bahréin", "Bangladés", "Barbados", "Bélgica", "Belice", "Benín", "Bermudas", "Bielorrusia", "Birmania", "Bolivia", "Bosnia y Herzegovina", "Botsuana", "Brasil", "Brunéi", "Bulgaria", "Burkina Faso", "Burundi", "Bután", "Cabo Verde", "Camboya", "Camerún", "Canadá", "Chad", "Chile", "China", "Chipre", "Ciudad del Vaticano", "Colombia", "Comoras", "Corea del Norte", "Corea del Sur", "Costa Rica", "Costa de Marfil", "Croacia", "Cuba", "Dinamarca", "Dominica", "Ecuador", "Egipto", "El Salvador", "Emiratos Árabes Unidos", "Eritrea", "Eslovaquia", "Eslovenia", "España", "Estados Unidos", "Estonia", "Etiopía", "Filipinas", "Finlandia", "Fiyi", "Francia", "Gabón", "Gambia", "Georgia", "Ghana", "Gibraltar", "Granada", "Grecia", "Groenlandia", "Guadalupe", "Guam", "Guatemala", "Guayana Francesa", "Guinea", "Guinea Ecuatorial", "Guinea-Bisáu", "Guyana", "Haití", "Honduras", "Hungría", "India", "Indonesia", "Irak", "Irán", "Irlanda", "Islandia", "Israel", "Italia", "Jamaica", "Japón", "Jordania", "Kazajistán", "Kenia", "Kirguistán", "Kiribati", "Kuwait", "Laos", "Lesoto", "Letonia", "Líbano", "Liberia", "Libia", "Liechtenstein", "Lituania", "Luxemburgo", "Macedonia del Norte", "Madagascar", "Malasia", "Malaui", "Maldivas", "Malí", "Malta", "Marruecos", "Martinica", "Mauricio", "Mauritania", "México", "Micronesia", "Moldavia", "Mónaco", "Mongolia", "Montenegro", "Mozambique", "Namibia", "Nauru", "Nepal", "Nicaragua", "Níger", "Nigeria", "Noruega", "Nueva Zelanda", "Omán", "Países Bajos", "Pakistán", "Palaos", "Panamá", "Papúa Nueva Guinea", "Paraguay", "Perú", "Pitcairn", "Polinesia Francesa", "Polonia", "Portugal", "Puerto Rico", "Catar", "Reino Unido", "República Centroafricana", "República Checa", "República Democrática del Congo", "República Dominicana", "República del Congo", "Reunión", "Ruanda", "Rumania", "Rusia", "Sahara Occidental", "Samoa", "Samoa Americana", "San Marino", "San Cristóbal y Nieves", "San Pedro y Miquelón", "San Vicente y las Granadinas", "Santa Helena", "Santa Lucía", "Santo Tomé y Príncipe", "Senegal", "Serbia", "Seychelles", "Sierra Leona", "Singapur", "Siria", "Somalia", "Sri Lanka", "Suazilandia", "Sudáfrica", "Sudán", "Sudán del Sur", "Suecia", "Suiza", "Surinam", "Tailandia", "Taiwán", "Tanzania", "Tayikistán", "Territorio Británico del Océano Índico", "Territorios Palestinos", "Timor Oriental", "Togo", "Tokelau", "Tonga", "Trinidad y Tobago", "Túnez", "Turkmenistán", "Turquía", "Tuvalu", "Ucrania", "Uganda", "Uruguay", "Uzbekistán", "Vanuatu", "Venezuela", "Vietnam", "Yemen", "Yibuti", "Zambia", "Zimbabue"];
                    $selected_country = isset($_POST['pais']) ? $_POST['pais'] : '';
                    echo "<option value=\"\">Seleccionar país...</option>";
                    foreach ($countries as $country) {
                        $selected = ($country == $selected_country) ? 'selected' : '';
                        echo "<option value=\"$country\" $selected>" . htmlspecialchars($country) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-brand">Crear cuenta</button>
            </div>
        </form>
        <div class="text-center mt-3">
            <p class="text-muted">¿Ya tienes una cuenta? <a href="login.php">Inicia sesión</a></p>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
