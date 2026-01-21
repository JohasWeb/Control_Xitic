<?php
include_once "DataBase.php";

class RegionesModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = DataBase::conectar();
    }

    public function obtenerPorCliente($ClienteId)
    {
        try {
            $Sql = "SELECT r.*, 
                           CONCAT(u.nombre, ' ', u.apellido) as supervisor_nombre,
                           u.email as supervisor_email,
                           u.nombre as supervisor_n,
                           u.apellido as supervisor_a
                    FROM regiones r 
                    LEFT JOIN usuarios u ON r.supervisor_id = u.id
                    WHERE r.cliente_id = :cliente_id AND r.activo = 1 
                    ORDER BY r.nombre ASC";
            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->bindValue(':cliente_id', $ClienteId, PDO::PARAM_INT);
            $Stmt->execute();
            return $Stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    public function obtenerPorId($id, $ClienteId)
    {
        try {
            $Sql = "SELECT * FROM regiones WHERE id = :id AND cliente_id = :cliente_id LIMIT 1";
            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $Stmt->bindValue(':cliente_id', $ClienteId, PDO::PARAM_INT);
            $Stmt->execute();
            return $Stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return null;
        }
    }

    public function crear($ClienteId, $Nombre, $SupervisorId = null)
    {
        try {
            $Sql = "INSERT INTO regiones (cliente_id, nombre, supervisor_id, activo) VALUES (:cliente_id, :nombre, :supervisor_id, 1)";
            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->bindValue(':cliente_id', $ClienteId, PDO::PARAM_INT);
            $Stmt->bindValue(':nombre', trim($Nombre));
            
            // Bind SupervisorId (NULL if <= 0 or empty)
            if ($SupervisorId > 0) {
                 $Stmt->bindValue(':supervisor_id', $SupervisorId, PDO::PARAM_INT);
            } else {
                 $Stmt->bindValue(':supervisor_id', null, PDO::PARAM_NULL);
            }
            
            if ($Stmt->execute()) {
                return $this->pdo->lastInsertId();
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    public function actualizar($id, $ClienteId, $Nombre, $SupervisorId = null)
    {
        try {
            $Sql = "UPDATE regiones SET nombre = :nombre, supervisor_id = :supervisor_id WHERE id = :id AND cliente_id = :cliente_id";
            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->bindValue(':nombre', trim($Nombre));
            $Stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $Stmt->bindValue(':cliente_id', $ClienteId, PDO::PARAM_INT);
            
             // Bind SupervisorId (NULL if <= 0 or empty)
            if ($SupervisorId > 0) {
                 $Stmt->bindValue(':supervisor_id', $SupervisorId, PDO::PARAM_INT);
            } else {
                 $Stmt->bindValue(':supervisor_id', null, PDO::PARAM_NULL);
            }

            return $Stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    public function eliminar($id, $ClienteId)
    {
        try {
            // Eliminación lógica
            $Sql = "UPDATE regiones SET activo = 0 WHERE id = :id AND cliente_id = :cliente_id";
            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $Stmt->bindValue(':cliente_id', $ClienteId, PDO::PARAM_INT);
            return $Stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }
}
