-- SCRIPT DE ACTUALIZACIÓN PARA CENTRALIZACIÓN DE FODA Y CADENA DE VALOR
-- Usa este script si ya tienes usuarios y empresas registradas, pero aún no has cargado información de análisis.
-- Cada paso es seguro (IF NOT EXISTS) y no borra datos existentes.

-- PASO 0: RESPALDO (EJECUTA ANTES DE TODO)
-- Recomendado: realiza un respaldo de tu base de datos.
-- Ejemplo en Windows (ajusta usuario/BD):
-- mysqldump -u tu_usuario -p tu_base_de_datos > respaldo_antes_actualizacion.sql

-- PASO 1: ASEGURAR ESTRUCTURA DE TABLA FODA
-- Si no existe, se crea con la estructura esperada. Si existe, se mantiene.
CREATE TABLE IF NOT EXISTS foda (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT NOT NULL,
    id_usuario INT NULL,
    tipo VARCHAR(20) NOT NULL,
    descripcion TEXT NOT NULL,
    origen VARCHAR(50) NOT NULL DEFAULT 'cadena_valor',
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- PASO 2: AGREGAR COLUMNAS A FODA (SI NO EXISTEN)
ALTER TABLE foda
    ADD COLUMN IF NOT EXISTS origen VARCHAR(50) NOT NULL DEFAULT 'cadena_valor';

ALTER TABLE foda
    ADD COLUMN IF NOT EXISTS fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- Agregar columna opcional 'id_usuario' para centralización por usuario
ALTER TABLE foda
    ADD COLUMN IF NOT EXISTS id_usuario INT NULL;

-- PASO 3: ÍNDICES RECOMENDADOS PARA RENDIMIENTO EN FODA
CREATE INDEX IF NOT EXISTS idx_foda_empresa_origen ON foda (id_empresa, origen);
CREATE INDEX IF NOT EXISTS idx_foda_empresa_usuario_origen ON foda (id_empresa, id_usuario, origen);
CREATE INDEX IF NOT EXISTS idx_foda_empresa_tipo ON foda (id_empresa, tipo);

-- PASO 4: CREAR TABLA PARA RESPUESTAS DEL AUTODIAGNÓSTICO DE CADENA DE VALOR
CREATE TABLE IF NOT EXISTS cadena_valor_respuestas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT NOT NULL,
    pregunta_num INT NOT NULL,
    respuesta_valor INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX IF NOT EXISTS idx_cvr_empresa_pregunta ON cadena_valor_respuestas (id_empresa, pregunta_num);

-- PASO 5: MIGRACIÓN OPCIONAL DESDE `empresa_detalle` (Analisis FODA en JSON)
-- Si ya tenías datos en `empresa_detalle` y deseas migrarlos a `foda`, te recomiendo hacerlo con un script PHP
-- para asegurar compatibilidad con versiones de MySQL/MariaDB (algunas funciones JSON avanzadas no están en todas las versiones).
-- Si NO tienes información (como indicas), omite este paso y pasa al PASO 6.

-- PASO 6: ELIMINACIÓN OPCIONAL DE `empresa_detalle` SI NO SE USARÁ MÁS
-- Ejecuta SOLO si confirmas que no se usará en tu proyecto:
-- DROP TABLE IF EXISTS empresa_detalle;

-- PASO 7: VERIFICACIÓN BÁSICA
SELECT COUNT(*) AS total_foda FROM foda;
SELECT COUNT(*) AS total_cadena_valor_respuestas FROM cadena_valor_respuestas;