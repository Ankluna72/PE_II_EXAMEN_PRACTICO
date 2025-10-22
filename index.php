<?php
$pageStyles = ['css/home.css'];
require_once 'includes/header.php';
?>

 

<nav class="navbar navbar-expand-lg nav-minimal rounded px-3 py-3">
    <a class="navbar-brand" href="/NextStrategy-IT/">
        <img src="images/logo.png" alt="NexStrategy-IT">
    </a>
    <div class="ms-auto d-flex align-items-center gap-2">
        <a href="contactos.php" class="btn btn-outline-brand">Contáctanos</a>
        <a href="register.php" class="btn btn-outline-brand">Unirse</a>
        <a href="login.php" class="btn btn-brand">Iniciar sesión</a>
    </div>
    <button class="navbar-toggler ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarHome" aria-controls="navbarHome" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
</nav>

<div id="homeCarousel" class="carousel slide mt-4" data-bs-ride="carousel">
    <div class="carousel-indicators">
        <button type="button" data-bs-target="#homeCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
        <button type="button" data-bs-target="#homeCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
        <button type="button" data-bs-target="#homeCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
    </div>
    <div class="carousel-inner rounded shadow">
        <div class="carousel-item active">
            <img src="https://images.unsplash.com/photo-1553877522-43269d4ea984?q=80&w=1600&auto=format&fit=crop" class="d-block w-100" alt="Gestión estratégica de TI">
            <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded p-3">
                <h5>Gestiona la estrategia de tu empresa</h5>
                <p>Centraliza la visión, misión, valores y objetivos en un solo lugar.</p>
            </div>
        </div>
        <div class="carousel-item">
            <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?q=80&w=1600&auto=format&fit=crop" class="d-block w-100" alt="Registro de empresas">
            <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded p-3">
                <h5>Registra tus empresas</h5>
                <p>Administra información clave y mantén actualizado tu portafolio empresarial.</p>
            </div>
        </div>
        <div class="carousel-item">
            <img src="https://images.unsplash.com/photo-1531297484001-80022131f5a1?q=80&w=1600&auto=format&fit=crop" class="d-block w-100" alt="Análisis estratégico">
            <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded p-3">
                <h5>Analiza y planifica</h5>
                <p>Usa módulos como FODA, PEST y más para sustentar tus decisiones.</p>
            </div>
        </div>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#homeCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Anterior</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#homeCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Siguiente</span>
    </button>
    
</div>

<section class="hero">
    <div class="row align-items-center g-4">
        <div class="col-lg-8">
            <span class="pill">Plataforma de planificación estratégica</span>
            <h2 class="mt-2 mb-3">Centraliza la identidad y la estrategia de tu empresa</h2>
            <p class="lead mb-3">
                Registra tus empresas y documenta sus pilares: Misión, Visión, Valores y Objetivos. 
                Complementa con análisis FODA y PEST para decisiones sólidas y un plan ejecutivo claro.
            </p>
            <a href="login.php" class="btn btn-brand btn-lg">Iniciar sesión</a>
        </div>
        <div class="col-lg-4">
            <div class="bg-soft p-3">
                <div class="row g-2">
                    <div class="col-6">
                        <div class="card card-minimal text-center h-100">
                            <div class="card-body">
                                <div class="icon-bullet"></div>
                                <div class="mt-2 small muted">Misión</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card card-minimal text-center h-100">
                            <div class="card-body">
                                <div class="icon-bullet"></div>
                                <div class="mt-2 small muted">Visión</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card card-minimal text-center h-100">
                            <div class="card-body">
                                <div class="icon-bullet"></div>
                                <div class="mt-2 small muted">Valores</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card card-minimal text-center h-100">
                            <div class="card-body">
                                <div class="icon-bullet"></div>
                                <div class="mt-2 small muted">Objetivos</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="divider"></div>

<section class="mt-4">
    <h3 class="section-title mb-3">Objetivos de la aplicación</h3>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card card-minimal h-100">
                <img class="feature-img" src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?q=80&w=1200&auto=format&fit=crop" alt="Registro centralizado">
                <div class="card-body">
                    <h5 class="card-title">Registro centralizado</h5>
                    <p class="card-text muted">Administra múltiples empresas y su información estratégica desde un solo lugar.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-minimal h-100">
                <img class="feature-img" src="https://images.unsplash.com/photo-1553877522-43269d4ea984?q=80&w=1200&auto=format&fit=crop" alt="Estructura guiada">
                <div class="card-body">
                    <h5 class="card-title">Estructura guiada</h5>
                    <p class="card-text muted">Completa Misión, Visión, Valores y Objetivos con un flujo sencillo y ordenado.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-minimal h-100">
                <img class="feature-img" src="https://images.unsplash.com/photo-1531297484001-80022131f5a1?q=80&w=1200&auto=format&fit=crop" alt="Análisis estratégico">
                <div class="card-body">
                    <h5 class="card-title">Análisis estratégico</h5>
                    <p class="card-text muted">Incorpora FODA y PEST para respaldar la toma de decisiones y el plan ejecutivo.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="mt-5">
    <h3 class="section-title mb-3">¿Por qué es importante la planeación estratégica?</h3>
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card card-minimal h-100">
                <div class="card-body">
                    <ul class="list-check">
                        <li>Alinea esfuerzos a una dirección clara y compartida.</li>
                        <li>Prioriza inversiones de TI con impacto en el negocio.</li>
                        <li>Reduce riesgos y anticipa cambios del entorno.</li>
                        <li>Mejora la comunicación interna y el compromiso del equipo.</li>
                        <li>Facilita la medición de resultados y mejora continua.</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <img class="w-100 rounded shadow img-260-cover" src="https://images.unsplash.com/photo-1542744173-05336fcc7ad4?q=80&w=1200&auto=format&fit=crop" alt="Planeación estratégica">
        </div>
    </div>
</section>

<section class="mt-5">
    <h3 class="section-title mb-3">Componentes clave y qué escribir</h3>
    <div class="row g-4">
        <div class="col-md-6 col-lg-3">
            <div class="card card-minimal h-100">
                <img class="feature-img" src="https://images.unsplash.com/photo-1531482615713-2afd69097998?q=80&w=1200&auto=format&fit=crop" alt="Misión">
                <div class="card-body">
                    <h5 class="card-title">Misión</h5>
                    <p class="card-text muted">Propósito actual de la empresa y a quién sirve. Describe qué haces, para quién y cómo generas valor hoy.</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card card-minimal h-100">
                <img class="feature-img" src="https://images.unsplash.com/photo-1483728642387-6c3bdd6c93e5?q=80&w=1200&auto=format&fit=crop" alt="Visión">
                <div class="card-body">
                    <h5 class="card-title">Visión</h5>
                    <p class="card-text muted">Futuro deseado. Expón hacia dónde se dirige la organización y el impacto que quiere lograr a mediano-largo plazo.</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card card-minimal h-100">
                <img class="feature-img" src="https://images.unsplash.com/photo-1506784242126-2a0b0b89c56a?q=80&w=1200&auto=format&fit=crop" alt="Valores">
                <div class="card-body">
                    <h5 class="card-title">Valores</h5>
                    <p class="card-text muted">Principios que guían decisiones y comportamientos. Enumera de 3 a 7 valores con breves descripciones.</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card card-minimal h-100">
                <img class="feature-img" src="https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?q=80&w=1200&auto=format&fit=crop" alt="Objetivos">
                <div class="card-body">
                    <h5 class="card-title">Objetivos</h5>
                    <p class="card-text muted">Resultados medibles y con plazo. Usa verbos de acción y, de ser posible, criterios SMART.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="mt-5">
    <h3 class="section-title mb-3">¿Cómo funciona?</h3>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card card-minimal h-100">
                <div class="card-body">
                    <h5 class="card-title mb-2">1) Crea tu cuenta</h5>
                    <p class="muted mb-0">Inicia sesión y accede a tu panel para gestionar empresas y proyectos.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-minimal h-100">
                <div class="card-body">
                    <h5 class="card-title mb-2">2) Registra tu empresa</h5>
                    <p class="muted mb-0">Agrega la información básica y selecciona el proyecto activo a trabajar.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-minimal h-100">
                <div class="card-body">
                    <h5 class="card-title mb-2">3) Completa los módulos</h5>
                    <p class="muted mb-0">Define Misión, Visión, Valores y Objetivos; luego realiza FODA y PEST para generar tu plan ejecutivo.</p>
                </div>
            </div>
        </div>
    </div>
    <div class="text-end mt-3">
        <a href="login.php" class="btn btn-brand">Comenzar ahora</a>
    </div>
    
    <div class="divider"></div>
    
    <h3 class="section-title mb-3">Preguntas frecuentes</h3>
    <div class="accordion" id="faqAccordion">
        <div class="accordion-item card-minimal">
            <h2 class="accordion-header" id="faq1">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1c" aria-expanded="true" aria-controls="faq1c">
                    ¿Necesito completar todos los módulos?
                </button>
            </h2>
            <div id="faq1c" class="accordion-collapse collapse show" aria-labelledby="faq1" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    No es obligatorio, pero completar Misión, Visión, Valores y Objetivos brinda una base sólida para el resto del análisis.
                </div>
            </div>
        </div>
        <div class="accordion-item card-minimal mt-2">
            <h2 class="accordion-header" id="faq2">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2c" aria-expanded="false" aria-controls="faq2c">
                    ¿Puedo gestionar varias empresas?
                </button>
            </h2>
            <div id="faq2c" class="accordion-collapse collapse" aria-labelledby="faq2" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    Sí, puedes registrar y alternar entre múltiples empresas desde tu panel.
                </div>
            </div>
        </div>
        <div class="accordion-item card-minimal mt-2">
            <h2 class="accordion-header" id="faq3">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3c" aria-expanded="false" aria-controls="faq3c">
                    ¿Qué obtengo al finalizar?
                </button>
            </h2>
            <div id="faq3c" class="accordion-collapse collapse" aria-labelledby="faq3" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    Un resumen ejecutivo coherente que integra identidad, análisis y prioridades para ejecutar la estrategia.
                </div>
            </div>
        </div>
    </div>
</section>

<?php
require_once 'includes/footer.php';
?>


