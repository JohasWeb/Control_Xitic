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

    public function crear($NombreComercial, $RazonSocial, $Comentarios, $LogoUrl = null, $LimiteSucursales = 1)
    {
        try {
            $Sql = "INSERT INTO clientes (nombre_comercial, razon_social, comentarios, logo_url, limite_sucursales, activo) VALUES (:nombre, :razon, :comentarios, :logo, :limite, 1)";
            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->bindValue(':nombre', trim($NombreComercial ?? ''));
            $Stmt->bindValue(':razon', trim($RazonSocial ?? ''));
            $Stmt->bindValue(':comentarios', trim($Comentarios ?? ''));
            $Stmt->bindValue(':logo', $LogoUrl);
            $Stmt->bindValue(':limite', (int)$LimiteSucursales, PDO::PARAM_INT);
            
            if ($Stmt->execute()) {
                $NewId = $this->pdo->lastInsertId();
                // Automatización: Región por defecto
                $SqlR = "INSERT INTO regiones (cliente_id, nombre, activo) VALUES (:cid, 'Región 1', 1)";
                $StmtR = $this->pdo->prepare($SqlR);
                $StmtR->bindValue(':cid', $NewId, PDO::PARAM_INT);
                $StmtR->execute();

                return $NewId;
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    public function actualizar($id, $NombreComercial, $RazonSocial, $Comentarios, $LogoUrl = null, $LimiteSucursales = null)
    {
        try {
            $Sql = "UPDATE clientes SET nombre_comercial = :nombre, razon_social = :razon, comentarios = :comentarios";
            
            if ($LimiteSucursales !== null) {
                $Sql .= ", limite_sucursales = :limite";
            }

            // Solo actualizamos el logo si viene uno nuevo
            if ($LogoUrl !== null) {
                $Sql .= ", logo_url = :logo";
            }
            
            $Sql .= " WHERE id = :id";
            
            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->bindValue(':nombre', trim($NombreComercial ?? ''));
            $Stmt->bindValue(':razon', trim($RazonSocial ?? ''));
            $Stmt->bindValue(':comentarios', trim($Comentarios ?? ''));
            
            if ($LimiteSucursales !== null) {
                 $Stmt->bindValue(':limite', (int)$LimiteSucursales, PDO::PARAM_INT);
            }

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

    public function obtenerStats($id)
    {
        try {
            $Stats = [];
            
            // Usuarios
            $Stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM usuarios WHERE cliente_id = :id");
            $Stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $Stmt->execute();
            $Stats['usuarios'] = $Stmt->fetchColumn();

            // Marcas
            $Stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM marcas WHERE cliente_id = :id");
            $Stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $Stmt->execute();
            $Stats['marcas'] = $Stmt->fetchColumn();

            // Sucursales
            $Stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM sucursales WHERE cliente_id = :id");
            $Stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $Stmt->execute();
            $Stats['sucursales'] = $Stmt->fetchColumn();
            
            // Encuestas
            $Stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM encuestas WHERE cliente_id = :id");
            $Stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $Stmt->execute();
            $Stats['encuestas'] = $Stmt->fetchColumn();

            return $Stats;
        } catch (Exception $e) {
            return ['usuarios' => 0, 'marcas' => 0, 'sucursales' => 0, 'encuestas' => 0];
        }
    }
}
