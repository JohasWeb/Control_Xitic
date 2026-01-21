USE appxitic_controlmaster;

ALTER TABLE encuestas
ADD COLUMN anonima TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0=Registro Requerido, 1=Anonima',
ADD COLUMN tiempo_estimado INT NOT NULL DEFAULT 5 COMMENT 'Tiempo en minutos';
