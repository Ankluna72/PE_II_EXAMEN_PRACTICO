-- Script para crear tabla de Evolución de la Demanda Global Sector
-- Ejecutar después de crear la base de datos principal

USE proyectoti;

-- Crear tabla para almacenar la evolución de la demanda global sector
CREATE TABLE `demanda_global_sector` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_empresa` INT(11) NOT NULL,
  `producto` ENUM('Producto 1', 'Producto 2', 'Producto 3', 'Producto 4', 'Producto 5') NOT NULL,
  `anio_2012` DECIMAL(5,2) DEFAULT NULL,
  `anio_2013` DECIMAL(5,2) DEFAULT NULL,
  `anio_2014` DECIMAL(5,2) DEFAULT NULL,
  `anio_2015` DECIMAL(5,2) DEFAULT NULL,
  `anio_2016` DECIMAL(5,2) DEFAULT NULL,
  `anio_2017` DECIMAL(5,2) DEFAULT NULL,
  `fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_empresa`) REFERENCES `empresa`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  UNIQUE KEY `unique_empresa_producto` (`id_empresa`, `producto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar registros iniciales para cada producto de cada empresa
-- Esto se ejecutará cuando se cree una nueva empresa
DELIMITER $$

CREATE PROCEDURE InsertarDemandaGlobalInicial(IN empresa_id INT)
BEGIN
    INSERT INTO `demanda_global_sector` (`id_empresa`, `producto`, `anio_2012`, `anio_2013`, `anio_2014`, `anio_2015`, `anio_2016`, `anio_2017`) VALUES
    (empresa_id, 'Producto 1', NULL, NULL, NULL, NULL, NULL, NULL),
    (empresa_id, 'Producto 2', NULL, NULL, NULL, NULL, NULL, NULL),
    (empresa_id, 'Producto 3', NULL, NULL, NULL, NULL, NULL, NULL),
    (empresa_id, 'Producto 4', NULL, NULL, NULL, NULL, NULL, NULL),
    (empresa_id, 'Producto 5', NULL, NULL, NULL, NULL, NULL, NULL);
END$$

DELIMITER ;

-- Crear trigger para insertar automáticamente los registros de demanda global cuando se crea una empresa
DELIMITER $$

CREATE TRIGGER `tr_empresa_demanda_global` 
AFTER INSERT ON `empresa` 
FOR EACH ROW 
BEGIN
    CALL InsertarDemandaGlobalInicial(NEW.id);
END$$

DELIMITER ;

-- Insertar datos para la empresa existente (ID 1)
CALL InsertarDemandaGlobalInicial(1);
