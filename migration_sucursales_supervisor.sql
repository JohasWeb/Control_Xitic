USE appxitic_controlmaster;

ALTER TABLE sucursales
ADD COLUMN usuario_id INT(11) NULL AFTER cliente_id,
ADD CONSTRAINT fk_sucursal_usuario
FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
ON DELETE SET NULL ON UPDATE CASCADE;
