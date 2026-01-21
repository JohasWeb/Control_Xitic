<?php
include_once "DataBase.php";

class ClientesModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = DataBase::conectar();
    }

    public function listarClientes()
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

    public function obtenerCliente($id)
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

    public function crearCliente($NombreComercial, $RazonSocial, $Rfc)
    {
        try {
            $Sql = "INSERT INTO clientes (nombre_comercial, razon_social, rfc_tax_id, activo) VALUES (:nombre, :razon, :rfc, 1)";
            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->bindValue(':nombre', trim($NombreComercial));
            $Stmt->bindValue(':razon', trim($RazonSocial));
            $Stmt->bindValue(':rfc', trim($Rfc));
            
            if ($Stmt->execute()) {
                return $this->pdo->lastInsertId();
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    public function actualizarCliente($id, $NombreComercial, $RazonSocial, $Rfc)
    {
        try {
            $Sql = "UPDATE clientes SET nombre_comercial = :nombre, razon_social = :razon, rfc_tax_id = :rfc WHERE id = :id";
            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->bindValue(':nombre', trim($NombreComercial));
            $Stmt->bindValue(':razon', trim($RazonSocial));
            $Stmt->bindValue(':rfc', trim($Rfc));
            $Stmt->bindValue(':id', $id, PDO::PARAM_INT);
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
