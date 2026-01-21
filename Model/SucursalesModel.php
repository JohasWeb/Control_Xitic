<?php
include_once "DataBase.php";

class SucursalesModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = DataBase::conectar();
    }

    public function obtenerPorCliente($ClienteId)
    {
        try {
            $Sql = "SELECT s.*, m.nombre as marca_nombre,
                           u.nombre as supervisor_nombre, u.apellido as supervisor_apellido, u.email as supervisor_email
                    FROM sucursales s 
                    LEFT JOIN marcas m ON s.marca_id = m.id 
                    LEFT JOIN usuarios u ON s.usuario_id = u.id
                    WHERE s.cliente_id = :cliente_id 
                    ORDER BY s.nombre ASC";
            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->bindValue(':cliente_id', $ClienteId, PDO::PARAM_INT);
            $Stmt->execute();
            return $Stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function contarPorCliente($ClienteId)
    {
        try {
            $Sql = "SELECT COUNT(*) FROM sucursales WHERE cliente_id = :cliente_id AND activo = 1";
            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->bindValue(':cliente_id', $ClienteId, PDO::PARAM_INT);
            $Stmt->execute();
            return (int)$Stmt->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }

    public function obtenerPorId($id, $ClienteId)
    {
        try {
            $Sql = "SELECT * FROM sucursales WHERE id = :id AND cliente_id = :cliente_id LIMIT 1";
            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $Stmt->bindValue(':cliente_id', $ClienteId, PDO::PARAM_INT);
            $Stmt->execute();
            return $Stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return null;
        }
    }

    public function crear($ClienteId, $Nombre, $Region, $Direccion, $MarcaId = null, $UsuarioId = null)
    {
        try {
            $Sql = "INSERT INTO sucursales (cliente_id, nombre, region, direccion, marca_id, usuario_id, activo) 
                    VALUES (:cliente_id, :nombre, :region, :direccion, :marca_id, :usuario_id, 1)";
            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->bindValue(':cliente_id', $ClienteId, PDO::PARAM_INT);
            $Stmt->bindValue(':nombre', trim($Nombre));
            $Stmt->bindValue(':region', trim($Region ?? ''));
            $Stmt->bindValue(':direccion', trim($Direccion ?? ''));
            $Stmt->bindValue(':marca_id', $MarcaId); // Puede ser null
            $Stmt->bindValue(':usuario_id', $UsuarioId); // Puede ser null

            if ($Stmt->execute()) {
                return $this->pdo->lastInsertId();
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    public function actualizar($id, $ClienteId, $Nombre, $Region, $Direccion, $MarcaId = null, $UsuarioId = null)
    {
        try {
            $Sql = "UPDATE sucursales 
                    SET nombre = :nombre, region = :region, direccion = :direccion, marca_id = :marca_id, usuario_id = :usuario_id 
                    WHERE id = :id AND cliente_id = :cliente_id";
            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->bindValue(':nombre', trim($Nombre));
            $Stmt->bindValue(':region', trim($Region ?? ''));
            $Stmt->bindValue(':direccion', trim($Direccion ?? ''));
            $Stmt->bindValue(':marca_id', $MarcaId);
            $Stmt->bindValue(':usuario_id', $UsuarioId);
            $Stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $Stmt->bindValue(':cliente_id', $ClienteId, PDO::PARAM_INT);
            
            return $Stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    public function eliminar($id, $ClienteId)
    {
        // Eliminación lógica (desactivar) o física. Por ahora física para simplificar o lógica?
        // La tabla tiene 'activo', usaremos eliminación lógica (toggle)
        // Pero el plan decía eliminar/activar. Haremos toggle.
        return false; // Implementar toggle si se requiere, por ahora nos centraremos en crear/editar
    }

    public function toggleActivo($id, $ClienteId)
    {
        try {
            // Obtener estado actual
            $Sucursal = $this->obtenerPorId($id, $ClienteId);
            if (!$Sucursal) return false;

            $NuevoEstado = ($Sucursal['activo'] == 1) ? 0 : 1;
            
            $Sql = "UPDATE sucursales SET activo = :estado WHERE id = :id AND cliente_id = :cliente_id";
            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->bindValue(':estado', $NuevoEstado, PDO::PARAM_INT);
            $Stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $Stmt->bindValue(':cliente_id', $ClienteId, PDO::PARAM_INT);
            return $Stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }
}
