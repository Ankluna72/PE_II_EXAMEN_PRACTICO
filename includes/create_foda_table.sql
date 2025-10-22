-- Tabla para almacenar el análisis FODA (fortalezas, debilidades, oportunidades, amenazas)
CREATE TABLE IF NOT EXISTS `foda` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_empresa` INT(11) NOT NULL,
  `id_usuario` INT(11) NULL,
  `tipo` ENUM('fortaleza', 'debilidad', 'oportunidad', 'amenaza') NOT NULL,
  `descripcion` TEXT NOT NULL,
  `origen` VARCHAR(50) NOT NULL DEFAULT 'manual' COMMENT 'Origen del análisis: cadena_valor, bcg, etc.',
  `posicion` INT(11) NULL COMMENT 'Posición 1-4 por tipo',
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_empresa`) REFERENCES `empresa`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`id_usuario`) REFERENCES `usuario`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  UNIQUE KEY `uniq_foda_slot` (`id_empresa`, `id_usuario`, `origen`, `tipo`, `posicion`),
  KEY `idx_foda_empresa_origen` (`id_empresa`, `origen`),
  KEY `idx_foda_empresa_usuario_origen` (`id_empresa`, `id_usuario`, `origen`),
  KEY `idx_foda_empresa_tipo` (`id_empresa`, `tipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
