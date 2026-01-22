USE appxitic_controlmaster;

-- JSON columns for advanced config and logic
-- (Using IF NOT EXISTS logic via procedure or ignoring errors in runner usually, but basic SQL here)

-- 1. Add columns if they don't exist (Runners usually handle this, but adding standard SQL)
-- Note: MySQL 5.7+ supports IF NOT EXISTS for ADD COLUMN but older versions don't.
-- We will rely on the previous commands having run or failing safely, but we add the MODIFY here.

ALTER TABLE encuestas_preguntas
ADD COLUMN logica_condicional JSON DEFAULT NULL COMMENT 'Logica para mostrar la pregunta',
ADD COLUMN configuracion_json JSON DEFAULT NULL COMMENT 'Configuraciones extra (max length, etc)';

-- 2. Expand question type column to allow 'botonera', 'nps', etc.
ALTER TABLE encuestas_preguntas 
MODIFY COLUMN tipo_pregunta VARCHAR(50) NOT NULL DEFAULT 'texto';
