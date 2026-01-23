<?php
include_once "DataBase.php";

class CasosModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = DataBase::conectar();
    }

    public function crear($clienteId, $usuarioId, $titulo, $descripcion, $origenEncuestaId = null, $categoria = null, $severidad = 'Media', $resumen = null, $slaVencimiento = null, $responsableId = null, $sucursalId = null, $regionId = null)
    {
        try {
            $Sql = "INSERT INTO casos (cliente_id, usuario_id, titulo, descripcion, fecha_creacion, estado, origen_encuesta_id, categoria, severidad, resumen, sla_vencimiento, responsable_id, sucursal_id, region_id) 
                    VALUES (:cid, :uid, :titulo, :desc, NOW(), 'abierto', :eid, :cat, :sev, :res, :sla, :rid, :sid, :regid)";
            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->bindValue(':cid', $clienteId, PDO::PARAM_INT);
            $Stmt->bindValue(':uid', $usuarioId, PDO::PARAM_INT);
            $Stmt->bindValue(':titulo', $titulo);
            $Stmt->bindValue(':desc', $descripcion);
            $Stmt->bindValue(':eid', $origenEncuestaId, PDO::PARAM_INT);
            $Stmt->bindValue(':cat', $categoria);
            $Stmt->bindValue(':sev', $severidad);
            $Stmt->bindValue(':res', $resumen);
            $Stmt->bindValue(':sla', $slaVencimiento);
            $Stmt->bindValue(':rid', $responsableId, PDO::PARAM_INT);
            $Stmt->bindValue(':sid', $sucursalId, PDO::PARAM_INT);
            $Stmt->bindValue(':regid', $regionId, PDO::PARAM_INT);
            
            if ($Stmt->execute()) {
                return $this->pdo->lastInsertId();
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    public function guardarRespuestaIA($id, $respuesta)
    {
        try {
            $Sql = "UPDATE casos SET respuesta_ia = :resp WHERE id = :id";
            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->bindValue(':resp', $respuesta);
            $Stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $Stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    public function obtenerPorCliente($clienteId)
    {
        try {
            $Sql = "SELECT c.*, u.nombre as usuario_nombre, u.apellido as usuario_apellido, 
                           s.nombre as sucursal_nombre, r.nombre as region_nombre,
                           resp.nombre as responsable_nombre, resp.apellido as responsable_apellido
                    FROM casos c 
                    LEFT JOIN usuarios u ON c.usuario_id = u.id 
                    LEFT JOIN sucursales s ON c.sucursal_id = s.id
                    LEFT JOIN regiones r ON c.region_id = r.id
                    LEFT JOIN usuarios resp ON c.responsable_id = resp.id
                    WHERE c.cliente_id = :cid 
                    ORDER BY c.fecha_creacion DESC";
            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->bindValue(':cid', $clienteId, PDO::PARAM_INT);
            $Stmt->execute();
            return $Stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function obtenerPorId($id)
    {
        try {
            $Sql = "SELECT * FROM casos WHERE id = :id LIMIT 1";
            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $Stmt->execute();
            return $Stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return null;
        }
    }
}
