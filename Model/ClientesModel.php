<?php
include_once "DataBase.php";

class ClientesModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = DataBase::conectar();
    }

    public function obtenerTodos()
    {
        try {
            $Sql = "SELECT * FROM clientes ORDER BY nombre_comercial ASC";
            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->execute();
            return $Stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return array();
        }
    }

    public function obtenerPorId($id)
    {
        try {
            $Sql = "SELECT * FROM clientes WHERE id = :id LIMIT 1";
            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $Stmt->execute();
            return $Stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return null;
        }
    }

    public function crear($NombreComercial, $RazonSocial, $Comentarios, $LogoUrl = null)
    {
        try {
            $Sql = "INSERT INTO clientes (nombre_comercial, razon_social, comentarios, logo_url, activo) VALUES (:nombre, :razon, :comentarios, :logo, 1)";
            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->bindValue(':nombre', trim($NombreComercial));
            $Stmt->bindValue(':razon', trim($RazonSocial));
            $Stmt->bindValue(':comentarios', trim($Comentarios));
            $Stmt->bindValue(':logo', $LogoUrl);
            
            if ($Stmt->execute()) {
                return $this->pdo->lastInsertId();
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    public function actualizar($id, $NombreComercial, $RazonSocial, $Comentarios, $LogoUrl = null)
    {
        try {
            $Sql = "UPDATE clientes SET nombre_comercial = :nombre, razon_social = :razon, comentarios = :comentarios";
            
            // Solo actualizamos el logo si viene uno nuevo
            if ($LogoUrl !== null) {
                $Sql .= ", logo_url = :logo";
            }
            
            $Sql .= " WHERE id = :id";
            
            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->bindValue(':nombre', trim($NombreComercial));
            $Stmt->bindValue(':razon', trim($RazonSocial));
            $Stmt->bindValue(':comentarios', trim($Comentarios));
            $Stmt->bindValue(':id', $id, PDO::PARAM_INT);
            
            if ($LogoUrl !== null) {
                $Stmt->bindValue(':logo', $LogoUrl);
            }
            
            return $Stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    public function toggleActivo($id, $EstadoActual)
    {
        try {
            $NuevoEstado = ($EstadoActual == 1) ? 0 : 1;
            $Sql = "UPDATE clientes SET activo = :estado WHERE id = :id";
            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->bindValue(':estado', $NuevoEstado, PDO::PARAM_INT);
            $Stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $Stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }
}
