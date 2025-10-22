CREATE DATABASE proyectoti

USE proyectoti


CREATE TABLE `usuario` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `tipo_user` INT(11) DEFAULT NULL,
  `nombre` VARCHAR(50) DEFAULT NULL,
  `apellido` VARCHAR(50) DEFAULT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `password` TEXT DEFAULT NULL,
  `pais` VARCHAR(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_unico` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Estructura de la tabla `empresa`
--
CREATE TABLE `empresa` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` INT(11) NOT NULL,
  `nombre_empresa` VARCHAR(255) DEFAULT NULL,
  `mision` TEXT DEFAULT NULL,
  `vision` TEXT DEFAULT NULL,
  `valores` TEXT DEFAULT NULL,
  `objetivos` TEXT DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_usuario`) REFERENCES `usuario`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Estructura de la tabla `empresa_detalle`
--
CREATE TABLE `empresa_detalle` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_empresa` INT(11) NOT NULL,
  `tipo_analisis` VARCHAR(100) NOT NULL,
  `contenido` JSON DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_empresa`) REFERENCES `empresa`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--AGREGAR A LA BASE DE DATOS    
-- Estructura de la tabla `colaboradores_empresa`
--
CREATE TABLE `colaboradores_empresa` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_empresa` INT(11) NOT NULL,
  `id_usuario_colaborador` INT(11) NOT NULL,
  `fecha_invitacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `estado` ENUM('activo', 'inactivo') DEFAULT 'activo',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_empresa`) REFERENCES `empresa`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`id_usuario_colaborador`) REFERENCES `usuario`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  UNIQUE KEY `unique_collaboration` (`id_empresa`, `id_usuario_colaborador`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Inserciones de datos de ejemplo
-- --------------------------------------------------------

--
-- Inserta un usuario: Jefferson Rosas
--
INSERT INTO `usuario` (`id`, `tipo_user`, `nombre`, `apellido`, `email`, `password`, `pais`) VALUES
(1, 1, 'Jefferson', 'Rosas', 'jefferson.rosas@ejemplo.com', 'hashed_password_123', 'Per�');

--
-- Inserta la informaci�n general de la empresa de Jefferson
--
INSERT INTO `empresa` (`id_usuario`, `nombre_empresa`, `mision`, `vision`, `valores`, `objetivos`) VALUES
(1, 'Tech Solutions', 'Ofrecer soluciones tecnol�gicas innovadoras para optimizar la gesti�n empresarial.', 'Ser l�deres en el mercado de desarrollo de software en Am�rica Latina para 2030.', 'Innovaci�n, Integridad, Orientaci�n al cliente.', 'Incrementar la cuota de mercado en un 15% el pr�ximo a�o y expandir operaciones a 3 nuevos pa�ses.');

--
-- Inserta el an�lisis FODA de la empresa
--
INSERT INTO `empresa_detalle` (`id_empresa`, `tipo_analisis`, `contenido`) VALUES
(1, 'Analisis FODA', '{
  "fortalezas": [
    "Equipo de desarrollo altamente calificado.",
    "Amplia cartera de clientes leales.",
    "R�pida adaptaci�n a nuevas tecnolog�as."
  ],
  "oportunidades": [
    "Crecimiento del mercado de soluciones SaaS.",
    "Necesidad de digitalizaci�n en peque�as empresas.",
    "Subsidios gubernamentales para tecnolog�a."
  ],
  "debilidades": [
    "Falta de presencia en mercados internacionales.",
    "Dependencia de pocos clientes grandes.",
    "Costo elevado de adquisici�n de nuevos clientes."
  ],
  "amenazas": [
    "Competencia de grandes empresas del sector.",
    "Cambios r�pidos en las regulaciones de datos.",
    "Inestabilidad econ�mica regional."
  ]
}');

--
-- Inserta la cadena de valor de la empresa
--
INSERT INTO `empresa_detalle` (`id_empresa`, `tipo_analisis`, `contenido`) VALUES
(1, 'Cadena de Valor', '{
  "actividades_primarias": {
    "logistica_entrada": "Procesos de adquisici�n de licencias de software y hardware.",
    "operaciones": "Desarrollo y pruebas de software �giles.",
    "logistica_salida": "Implementaci�n y despliegue de soluciones en la nube.",
    "marketing_ventas": "Marketing digital y estrategias de venta consultiva.",
    "servicios": "Soporte t�cnico 24/7 y formaci�n continua."
  },
  "actividades_apoyo": {
    "infraestructura": "Servidores y data centers seguros y de alto rendimiento.",
    "recursos_humanos": "Programas de retenci�n de talento y formaci�n especializada.",
    "desarrollo_tecnologico": "Investigaci�n en Inteligencia Artificial y Machine Learning.",
    "abastecimiento": "Negociaci�n de contratos con proveedores de nube."
  }
}');

--
-- Inserta la matriz BCG de la empresa
--
INSERT INTO `empresa_detalle` (`id_empresa`, `tipo_analisis`, `contenido`) VALUES
(1, 'Matriz BCG', '{
  "productos": [
    {
      "nombre": "Software de Contabilidad",
      "clasificacion": "Vaca",
      "descripcion": "Bajo crecimiento del mercado, pero alta cuota de mercado."
    },
    {
      "nombre": "Plataforma de IA",
      "clasificacion": "Estrella",
      "descripcion": "Alto crecimiento y alta cuota de mercado."
    },
    {
      "nombre": "App de Gesti�n de Proyectos",
      "clasificacion": "Interrogante",
      "descripcion": "Alto crecimiento, pero baja cuota de mercado."
    }
  ]
}');

--
-- Inserta el an�lisis PEST de la empresa
--
INSERT INTO `empresa_detalle` (`id_empresa`, `tipo_analisis`, `contenido`) VALUES
(1, 'Analisis PEST', '{
  "politicos": [
    "Nuevas pol�ticas de protecci�n de datos.",
    "Incentivos fiscales para empresas tecnol�gicas."
  ],
  "economicos": [
    "Inflaci�n que afecta los costos operativos.",
    "Crecimiento del PIB regional."
  ],
  "sociales": [
    "Aumento del teletrabajo y la demanda de herramientas digitales.",
    "Mayor conciencia sobre la ciberseguridad."
  ],
  "tecnologicos": [
    "Avances en la computaci�n cu�ntica.",
    "Democratizaci�n de la inteligencia artificial."
  ]
}');

--
-- Inserta la matriz CAME de la empresa
--
INSERT INTO `empresa_detalle` (`id_empresa`, `tipo_analisis`, `contenido`) VALUES
(1, 'Matriz CAME', '{
  "corregir_debilidades": [
    "Desarrollar una estrategia de marketing de entrada a nuevos mercados.",
    "Implementar un CRM para fortalecer la relaci�n con clientes existentes."
  ],
  "afrontar_amenazas": [
    "Invertir en I+D para mantener la competitividad.",
    "Contratar a un experto en regulaciones de datos."
  ],
  "mantener_fortalezas": [
    "Continuar con la formaci�n del equipo de desarrollo.",
    "Promocionar la marca como experta en innovaci�n."
  ],
  "explotar_oportunidades": [
    "Crear un nuevo producto para el mercado de peque�as empresas.",
    "Participar en programas de subsidios gubernamentales."
  ]
}');