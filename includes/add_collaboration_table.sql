-- Script para agregar la tabla de colaboradores a la base de datos existente
-- Ejecutar este script si ya tienes la base de datos creada

USE proyectoti;

-- Crear tabla de colaboradores si no existe
CREATE TABLE IF NOT EXISTS `colaboradores_empresa` (
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

-- Verificar que la tabla se creó correctamente
SELECT 'Tabla colaboradores_empresa creada exitosamente' AS mensaje;
