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

    public function crear($NombreComercial, $RazonSocial, $Comentarios, $LogoUrl = null, $LimiteSucursales = 1, $ModuloEncuestas = 0, $ModuloCasos = 0, $ConfigIaPrompt = null, $ConfigIaToken = null, $ConfigSlaHoras = 24)
    {
        try {
            $Sql = "INSERT INTO clientes (nombre_comercial, razon_social, comentarios, logo_url, limite_sucursales, activo, modulo_encuestas, modulo_casos, config_ia_prompt, config_ia_token, config_sla_horas) VALUES (:nombre, :razon, :comentarios, :logo, :limite, 1, :m_enc, :m_cas, :ia_prompt, :ia_token, :sla)";
            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->bindValue(':nombre', trim($NombreComercial ?? ''));
            $Stmt->bindValue(':razon', trim($RazonSocial ?? ''));
            $Stmt->bindValue(':comentarios', trim($Comentarios ?? ''));
            $Stmt->bindValue(':logo', $LogoUrl);
            $Stmt->bindValue(':limite', (int)$LimiteSucursales, PDO::PARAM_INT);
            $Stmt->bindValue(':m_enc', (int)$ModuloEncuestas, PDO::PARAM_INT);
            $Stmt->bindValue(':m_cas', (int)$ModuloCasos, PDO::PARAM_INT);
            $Stmt->bindValue(':ia_prompt', $ConfigIaPrompt);
            $Stmt->bindValue(':ia_token', $ConfigIaToken);
            $Stmt->bindValue(':sla', (int)$ConfigSlaHoras, PDO::PARAM_INT);
            
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

    public function actualizar($id, $NombreComercial, $RazonSocial, $Comentarios, $LogoUrl = null, $LimiteSucursales = null, $ModuloEncuestas = null, $ModuloCasos = null, $ConfigIaPrompt = null, $ConfigIaToken = null, $ConfigSlaHoras = null)
    {
        try {
            $Sql = "UPDATE clientes SET nombre_comercial = :nombre, razon_social = :razon, comentarios = :comentarios";
            
            if ($LimiteSucursales !== null) {
                $Sql .= ", limite_sucursales = :limite";
            }
            if ($ModuloEncuestas !== null) {
                $Sql .= ", modulo_encuestas = :m_enc";
            }
            if ($ModuloCasos !== null) {
                $Sql .= ", modulo_casos = :m_cas";
            }
            if ($ConfigIaPrompt !== null) {
                $Sql .= ", config_ia_prompt = :ia_prompt";
            }
            if ($ConfigIaToken !== null) {
                $Sql .= ", config_ia_token = :ia_token";
            }
            if ($ConfigSlaHoras !== null) {
                $Sql .= ", config_sla_horas = :sla";
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
            if ($ModuloEncuestas !== null) {
                 $Stmt->bindValue(':m_enc', (int)$ModuloEncuestas, PDO::PARAM_INT);
            }
            if ($ModuloCasos !== null) {
                 $Stmt->bindValue(':m_cas', (int)$ModuloCasos, PDO::PARAM_INT);
            }
            if ($ConfigIaPrompt !== null) {
                 $Stmt->bindValue(':ia_prompt', $ConfigIaPrompt);
            }
            if ($ConfigIaToken !== null) {
                 $Stmt->bindValue(':ia_token', $ConfigIaToken);
            }
            if ($ConfigSlaHoras !== null) {
                 $Stmt->bindValue(':sla', (int)$ConfigSlaHoras, PDO::PARAM_INT);
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

    public function toggleModulo($id, $Modulo, $Estado)
    {
        try {
            // Validar nombre de columna para evitar inyección (lista blanca)
            $ColumnasPermitidas = ['modulo_encuestas', 'modulo_casos'];
            if (!in_array($Modulo, $ColumnasPermitidas)) {
                return false;
            }

            $Sql = "UPDATE clientes SET $Modulo = :estado WHERE id = :id";
            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->bindValue(':estado', (int)$Estado, PDO::PARAM_INT);
            $Stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
            return $Stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    public function actualizarSLA(int $ClienteId, int $SlaHoras): bool
    {
         try {
             $Sql = "UPDATE clientes SET config_sla_horas = :sla WHERE id = :id";
             $Stmt = $this->pdo->prepare($Sql);
             return $Stmt->execute([':sla' => $SlaHoras, ':id' => $ClienteId]);
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

    public function obtenerTotalClientesActivos()
    {
        try {
            $Sql = "SELECT COUNT(*) FROM clientes WHERE activo = 1";
            $Stmt = $this->pdo->query($Sql);
            return $Stmt->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }
}
