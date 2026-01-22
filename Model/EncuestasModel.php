<?php
include_once "DataBase.php";

class EncuestasModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = DataBase::conectar();
    }

    // --- ENCUESTAS ---

    public function listarEncuestas($ClienteId = null)
    {
        try {
            $Sql = "SELECT e.*, c.nombre_comercial as cliente_nombre 
                    FROM encuestas e
                    JOIN clientes c ON e.cliente_id = c.id";
            
            $Params = array();

            if ($ClienteId !== null && $ClienteId > 0) {
                $Sql .= " WHERE e.cliente_id = :cliente_id";
                $Params[':cliente_id'] = $ClienteId;
            }

            $Sql .= " ORDER BY e.id DESC";

            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->execute($Params);
            return $Stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return array();
        }
    }

    public function obtenerEncuesta($id)
    {
        try {
            $Sql = "SELECT * FROM encuestas WHERE id = :id LIMIT 1";
            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $Stmt->execute();
            return $Stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return null;
        }
    }

    public function crearEncuesta($ClienteId, $Titulo, $Descripcion, $FechaInicio, $FechaFin, $CreadoPor, $Anonima = 0, $TiempoEstimado = 5, $ImagenHeader = null)
    {
        try {
            $Sql = "INSERT INTO encuestas 
                    (cliente_id, titulo, descripcion, fecha_inicio, fecha_fin, estado, creado_por, anonima, tiempo_estimado, imagen_header) 
                    VALUES 
                    (:cliente_id, :titulo, :descripcion, :inicio, :fin, 1, :creado_por, :anonima, :tiempo_estimado, :img)";
            
            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->bindValue(':cliente_id', $ClienteId, PDO::PARAM_INT);
            $Stmt->bindValue(':titulo', $Titulo);
            $Stmt->bindValue(':descripcion', $Descripcion);
            $Stmt->bindValue(':inicio', $FechaInicio);
            $Stmt->bindValue(':fin', $FechaFin);
            $Stmt->bindValue(':creado_por', $CreadoPor, PDO::PARAM_INT);
            $Stmt->bindValue(':anonima', $Anonima, PDO::PARAM_INT);
            $Stmt->bindValue(':tiempo_estimado', $TiempoEstimado, PDO::PARAM_INT);
            $Stmt->bindValue(':img', $ImagenHeader);

            if ($Stmt->execute()) {
                return $this->pdo->lastInsertId();
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    public function actualizarEncuesta($Id, $ClienteId, $Titulo, $Descripcion, $FechaInicio, $FechaFin, $Anonima, $TiempoEstimado, $ImagenHeader = null)
    {
        try {
            $SetImg = "";
            if ($ImagenHeader !== null) {
                $SetImg = ", imagen_header = :img";
            }

            $Sql = "UPDATE encuestas 
                    SET titulo = :titulo, descripcion = :descripcion, fecha_inicio = :inicio, 
                        fecha_fin = :fin, anonima = :anonima, tiempo_estimado = :tiempo_estimado
                        $SetImg
                    WHERE id = :id AND cliente_id = :cliente_id";
            
            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->bindValue(':titulo', $Titulo);
            $Stmt->bindValue(':descripcion', $Descripcion);
            $Stmt->bindValue(':inicio', $FechaInicio);
            $Stmt->bindValue(':fin', $FechaFin);
            $Stmt->bindValue(':anonima', $Anonima, PDO::PARAM_INT);
            $Stmt->bindValue(':tiempo_estimado', $TiempoEstimado, PDO::PARAM_INT);
            if ($ImagenHeader !== null) {
                $Stmt->bindValue(':img', $ImagenHeader);
            }
            $Stmt->bindValue(':id', $Id, PDO::PARAM_INT);
            $Stmt->bindValue(':cliente_id', $ClienteId, PDO::PARAM_INT);

            return $Stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    // --- PREGUNTAS ---

    public function obtenerPreguntas($EncuestaId)
    {
        try {
            $Sql = "SELECT * FROM encuestas_preguntas WHERE encuesta_id = :id ORDER BY orden ASC";
            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->bindValue(':id', $EncuestaId, PDO::PARAM_INT);
            $Stmt->execute();
            return $Stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return array();
        }
    }

    public function agregarPregunta($EncuestaId, $Texto, $Tipo, $Orden, $Requerido, $OpcionesJson, $Logica = null, $Config = null)
    {
        try {
            $Sql = "INSERT INTO encuestas_preguntas 
                    (encuesta_id, texto_pregunta, tipo_pregunta, orden, requerido, opciones_json, logica_condicional, configuracion_json)
                    VALUES 
                    (:encuesta_id, :texto, :tipo, :orden, :requerido, :opciones, :logica, :config)";

            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->bindValue(':encuesta_id', $EncuestaId, PDO::PARAM_INT);
            $Stmt->bindValue(':texto', $Texto);
            $Stmt->bindValue(':tipo', $Tipo);
            $Stmt->bindValue(':orden', $Orden, PDO::PARAM_INT);
            $Stmt->bindValue(':requerido', $Requerido, PDO::PARAM_INT);
            $Stmt->bindValue(':opciones', $OpcionesJson);
            $Stmt->bindValue(':logica', $Logica);
            $Stmt->bindValue(':config', $Config);

            return $Stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    public function actualizarPregunta($Id, $EncuestaId, $Texto, $Tipo, $Requerido, $OpcionesJson, $Logica = null, $Config = null)
    {
        try {
            $Sql = "UPDATE encuestas_preguntas 
                    SET texto_pregunta = :texto, tipo_pregunta = :tipo, requerido = :requerido, 
                        opciones_json = :opciones, logica_condicional = :logica, configuracion_json = :config
                    WHERE id = :id AND encuesta_id = :encuesta_id";

            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->bindValue(':texto', $Texto);
            $Stmt->bindValue(':tipo', $Tipo);
            $Stmt->bindValue(':requerido', $Requerido, PDO::PARAM_INT);
            $Stmt->bindValue(':opciones', $OpcionesJson);
            $Stmt->bindValue(':logica', $Logica);
            $Stmt->bindValue(':config', $Config);
            $Stmt->bindValue(':id', $Id, PDO::PARAM_INT);
            $Stmt->bindValue(':encuesta_id', $EncuestaId, PDO::PARAM_INT);

            return $Stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    public function reordenarPreguntas($Items)
    {
        try {
            $this->pdo->beginTransaction();
            $Sql = "UPDATE encuestas_preguntas SET orden = :orden WHERE id = :id";
            $Stmt = $this->pdo->prepare($Sql);

            foreach ($Items as $Item) {
                $Stmt->bindValue(':orden', $Item['orden'], PDO::PARAM_INT);
                $Stmt->bindValue(':id', $Item['id'], PDO::PARAM_INT);
                $Stmt->execute();
            }
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function eliminarPregunta($Id, $EncuestaId)
    {
        try {
            $Sql = "DELETE FROM encuestas_preguntas WHERE id = :id AND encuesta_id = :encuesta_id";
            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->bindValue(':id', $Id, PDO::PARAM_INT);
            $Stmt->bindValue(':encuesta_id', $EncuestaId, PDO::PARAM_INT);
            return $Stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    public function borrarPreguntas($EncuestaId)
    {
        // Útil para actualizar la encuesta borrando y recreando preguntas
        try {
            $Sql = "DELETE FROM encuestas_preguntas WHERE encuesta_id = :id";
            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->bindValue(':id', $EncuestaId, PDO::PARAM_INT);
            return $Stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    // --- QR / MEMBRESÍA ---

    public function obtenerTokenQr($SucursalId, $Mesa)
    {
        try {
            $Sql = "SELECT * FROM sucursales_qr WHERE sucursal_id = :sucursal AND mesa = :mesa LIMIT 1";
            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->bindValue(':sucursal', $SucursalId, PDO::PARAM_INT);
            $Stmt->bindValue(':mesa', $Mesa);
            $Stmt->execute();
            return $Stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return null;
        }
    }

    public function generarTokenQr($SucursalId, $Mesa)
    {
        try {
            $Token = bin2hex(random_bytes(16)); // 32 caracteres
            
            $Sql = "INSERT INTO sucursales_qr (sucursal_id, mesa, token_unico, activo) 
                    VALUES (:sucursal, :mesa, :token, 1)";
            
            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->bindValue(':sucursal', $SucursalId, PDO::PARAM_INT);
            $Stmt->bindValue(':mesa', $Mesa);
            $Stmt->bindValue(':token', $Token);
            
            if ($Stmt->execute()) {
                return $Token;
            }
            return false;
        } catch (Exception $e) {
            // Si falla por duplicidad de token, reintentar recursivamente (caso raro)
            return false;
        }
    }
}
