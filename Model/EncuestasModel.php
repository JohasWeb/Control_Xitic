<?php

declare(strict_types=1);

/**
 * Archivo: EncuestasModel.php
 * Propósito: Modelo de datos para la gestión de encuestas en base de datos.
 * Autor: Refactorización Expert PHP
 * Fecha: 2026-01-22
 */

include_once "DataBase.php";

/**
 * Clase EncuestasModel
 * 
 * Capa de acceso a datos para encuestas, preguntas y configuración relacionada.
 * Utiliza PDO para consultas seguras y preparadas.
 * 
 * @package Control\Model
 */
class EncuestasModel
{
    /**
     * Instancia de conexión a la base de datos.
     * @var PDO
     */
    private PDO $pdo;

    /**
     * Constructor.
     * Establece la conexión a la base de datos.
     */
    public function __construct()
    {
        $this->pdo = DataBase::conectar();
    }

    // --- ENCUESTAS ---

    /**
     * Lista las encuestas disponibles.
     * 
     * @param int|null $ClienteId ID del cliente para filtrar (opcional). Si es null, retorna todas.
     * @return array Array de encuestas (asociativo).
     */
    public function listarEncuestas(?int $ClienteId = null): array
    {
        try {
            $Sql = "SELECT e.*, c.nombre_comercial as cliente_nombre,
                    (SELECT COUNT(*) FROM encuestas_respuestas r WHERE r.encuesta_id = e.id) as total_respuestas,
                    (SELECT AVG(duracion_segundos) FROM encuestas_respuestas r2 WHERE r2.encuesta_id = e.id) as promedio_duracion
                    FROM encuestas e
                    JOIN clientes c ON e.cliente_id = c.id";
            
            $Params = array();

            if ($ClienteId !== null) {
                if ($ClienteId > 0) {
                    $Sql .= " WHERE e.cliente_id = :cliente_id";
                    $Params[':cliente_id'] = $ClienteId;
                }
            }

            $Sql .= " ORDER BY e.id DESC";

            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->execute($Params);
            
            return $Stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Obtiene una encuesta específica por ID.
     * 
     * @param int $id ID de la encuesta.
     * @return array|false Datos de la encuesta o false si falla/no existe.
     */
    public function obtenerEncuesta(int $id): array|false
    {
        try {
            $Sql = "SELECT * FROM encuestas WHERE id = :id LIMIT 1";
            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $Stmt->execute();
            
            return $Stmt->fetch(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Crea una nueva encuesta en la base de datos.
     * 
     * @param int $ClienteId ID del cliente propietario.
     * @param string $Titulo Título de la encuesta.
     * @param string $Descripcion Descripción detallada.
     * @param string $FechaInicio Fecha de inicio (Y-m-d).
     * @param string|null $FechaFin Fecha de fin (Y-m-d) o null si es indefinida.
     * @param int $CreadoPor ID del usuario creador.
     * @param int $Anonima Flag (1 o 0) si es anónima.
     * @param int $TiempoEstimado Tiempo estimado en minutos.
     * @param string|null $ImagenHeader Ruta de la imagen de cabecera (opcional).
     * @return string|false ID de la encuesta inertada o false en caso de error.
     */
    public function crearEncuesta(
        int $ClienteId, 
        string $Titulo, 
        string $Descripcion, 
        string $FechaInicio, 
        ?string $FechaFin, 
        int $CreadoPor, 
        int $Anonima = 0, 
        int $TiempoEstimado = 5, 
        ?string $ImagenHeader = null,
        ?string $ConfiguracionJson = null
    ): string|false
    {
        try {
            $Sql = "INSERT INTO encuestas 
                    (cliente_id, titulo, descripcion, fecha_inicio, fecha_fin, estado, creado_por, anonima, tiempo_estimado, imagen_header, configuracion_json) 
                    VALUES 
                    (:cliente_id, :titulo, :descripcion, :inicio, :fin, 1, :creado_por, :anonima, :tiempo_estimado, :img, :config)";
            
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
            $Stmt->bindValue(':config', $ConfiguracionJson);

            if ($Stmt->execute()) {
                return $this->pdo->lastInsertId();
            }
            return false;

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Actualiza los datos de una encuesta existente.
     * 
     * @param int $Id ID de la encuesta.
     * @param int $ClienteId ID del cliente (para validación de propiedad).
     * @param string $Titulo Título.
     * @param string $Descripcion Descripción.
     * @param string $FechaInicio Fecha de inicio.
     * @param string|null $FechaFin Fecha de fin.
     * @param int $Anonima Flag anónima.
     * @param int $TiempoEstimado Tiempo estimado.
     * @param string|null $ImagenHeader Ruta de la nueva imagen (si se actualiza).
     * @return bool True si tuvo éxito, False si falló.
     */
    public function actualizarEncuesta(
        int $Id, 
        int $ClienteId, 
        string $Titulo, 
        string $Descripcion, 
        string $FechaInicio, 
        ?string $FechaFin, 
        int $Anonima, 
        int $TiempoEstimado, 
        ?string $ImagenHeader = null,
        ?string $ConfiguracionJson = null
    ): bool
    {
        try {
            $SetImg = "";
            if ($ImagenHeader !== null) {
                $SetImg = ", imagen_header = :img";
            }

            $Sql = "UPDATE encuestas 
                    SET titulo = :titulo, descripcion = :descripcion, fecha_inicio = :inicio, 
                        fecha_fin = :fin, anonima = :anonima, tiempo_estimado = :tiempo_estimado,
                        configuracion_json = :config
                        $SetImg
                    WHERE id = :id AND cliente_id = :cliente_id";
            
            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->bindValue(':titulo', $Titulo);
            $Stmt->bindValue(':descripcion', $Descripcion);
            $Stmt->bindValue(':inicio', $FechaInicio);
            $Stmt->bindValue(':fin', $FechaFin);
            $Stmt->bindValue(':anonima', $Anonima, PDO::PARAM_INT);
            $Stmt->bindValue(':tiempo_estimado', $TiempoEstimado, PDO::PARAM_INT);
            $Stmt->bindValue(':config', $ConfiguracionJson);
            
            if ($ImagenHeader !== null) {
                $Stmt->bindValue(':img', $ImagenHeader);
            }
            
            $Stmt->bindValue(':id', $Id, PDO::PARAM_INT);
            $Stmt->bindValue(':cliente_id', $ClienteId, PDO::PARAM_INT);

            return $Stmt->execute();

        } catch (Exception $e) {
            throw $e;
        }
    }

    // --- ASIGNACIONES ---

    /**
     * Obtiene las asignaciones actuales de una encuesta.
     * 
     * @param int $EncuestaId
     * @return array Lista de asignaciones.
     */
    public function obtenerAsignaciones(int $EncuestaId): array
    {
        try {
            $Sql = "SELECT * FROM encuestas_asignaciones WHERE encuesta_id = :id";
            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->bindValue(':id', $EncuestaId, PDO::PARAM_INT);
            $Stmt->execute();
            return $Stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Limpia todas las asignaciones de una encuesta (para sobreescribir).
     * 
     * @param int $EncuestaId
     * @return bool
     */
    public function limpiarAsignaciones(int $EncuestaId): bool
    {
        try {
            $Sql = "DELETE FROM encuestas_asignaciones WHERE encuesta_id = :id";
            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->bindValue(':id', $EncuestaId, PDO::PARAM_INT);
            return $Stmt->execute();
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Guarda una nueva asignación.
     * 
     * @param int $EncuestaId
     * @param string $Nivel 'CLIENTE', 'REGION', 'SUCURSAL'
     * @param int $ValorId ID de región/sucursal o 0 si es Cliente.
     * @return bool
     */
    public function guardarAsignacion(int $EncuestaId, string $Nivel, int $ValorId = 0): bool
    {
        try {
            $Sql = "INSERT INTO encuestas_asignaciones (encuesta_id, nivel, valor_id) VALUES (:id, :nivel, :valor)";
            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->bindValue(':id', $EncuestaId, PDO::PARAM_INT);
            $Stmt->bindValue(':nivel', $Nivel);
            $Stmt->bindValue(':valor', $ValorId, PDO::PARAM_INT);
            return $Stmt->execute();
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Obtiene la lista de sucursales habilitadas para esta encuesta.
     * Resuelve la lógica de asignación (CLIENTE -> Todas, REGION -> Sucursales de región, SUCURSAL -> Específicas).
     * 
     * @param int $EncuestaId
     * @param int $ClienteId
     * @return array
     */
    public function obtenerSucursalesAlcance(int $EncuestaId, int $ClienteId): array
    {
        $Asignaciones = $this->obtenerAsignaciones($EncuestaId);
        
        // Default: CLIENTE (Todas)
        $Tipo = 'CLIENTE'; 
        if (!empty($Asignaciones)) {
            $Tipo = $Asignaciones[0]['nivel'];
        }

        try {
            if ($Tipo === 'CLIENTE') {
                // Todas las sucursales activas del cliente
                $Sql = "SELECT s.id, s.nombre, s.region 
                        FROM sucursales s 
                        WHERE s.cliente_id = :cliente_id AND s.activo = 1 
                        ORDER BY s.region ASC, s.nombre ASC";
                $Stmt = $this->pdo->prepare($Sql);
                $Stmt->bindValue(':cliente_id', $ClienteId, PDO::PARAM_INT);
                $Stmt->execute();
                return $Stmt->fetchAll(PDO::FETCH_ASSOC);

            } elseif ($Tipo === 'REGION') {
                // Sucursales que pertenezcan a las regiones asignadas
                $RegionesIds = [];
                foreach ($Asignaciones as $A) {
                    if ($A['valor_id'] > 0) $RegionesIds[] = $A['valor_id'];
                }
                
                if (empty($RegionesIds)) return [];

                $Placeholders = implode(',', array_fill(0, count($RegionesIds), '?'));
                $Sql = "SELECT s.id, s.nombre, s.region 
                        FROM sucursales s 
                        JOIN regiones r ON s.region = r.nombre
                        WHERE s.cliente_id = ? 
                          AND r.id IN ($Placeholders)
                          AND s.activo = 1 
                        ORDER BY s.region ASC, s.nombre ASC";
                
                // Nota: Relación Sucursal->Region es por NOMBRE en tabla sucursales ('region' varchar)???
                // Revisando SucursalesModel: 'region' es varchar. Pero en RegionesModel 'nombre' es varchar.
                // Es arriesgado hacer JOIN por nombre si puede cambiar.
                // PERO... Sucursales no tiene region_id? Revisé SucursalesModel y tiene 'region' string.
                // Asumimos Join por nombre region = r.nombre y r.cliente_id = s.cliente_id
                
                // Mejor aproximación si no hay region_id FK real:
                // Obtener nombres de regiones primero
                $SqlReg = "SELECT nombre FROM regiones WHERE id IN ($Placeholders)";
                $StmtReg = $this->pdo->prepare($SqlReg);
                $StmtReg->execute($RegionesIds);
                $NombresRegiones = $StmtReg->fetchAll(PDO::FETCH_COLUMN);

                if (empty($NombresRegiones)) return [];
                
                $PlaceholdersNombres = implode(',', array_fill(0, count($NombresRegiones), '?'));
                $Params = array_merge([$ClienteId], $NombresRegiones);
                
                $SqlS = "SELECT s.id, s.nombre, s.region 
                         FROM sucursales s 
                         WHERE s.cliente_id = ? 
                           AND s.region IN ($PlaceholdersNombres)
                           AND s.activo = 1 
                         ORDER BY s.region ASC, s.nombre ASC";
                $StmtS = $this->pdo->prepare($SqlS);
                $StmtS->execute($Params);
                return $StmtS->fetchAll(PDO::FETCH_ASSOC);

            } elseif ($Tipo === 'SUCURSAL') {
                // Sucursales específicas
                $SucIDs = [];
                foreach ($Asignaciones as $A) {
                    if ($A['valor_id'] > 0) $SucIDs[] = $A['valor_id'];
                }

                if (empty($SucIDs)) return [];

                $Placeholders = implode(',', array_fill(0, count($SucIDs), '?'));
                $Params = array_merge([$ClienteId], $SucIDs);
                
                $Sql = "SELECT s.id, s.nombre, s.region 
                        FROM sucursales s 
                        WHERE s.cliente_id = ? 
                          AND s.id IN ($Placeholders)
                          AND s.activo = 1 
                        ORDER BY s.region ASC, s.nombre ASC";
                $Stmt = $this->pdo->prepare($Sql);
                $Stmt->execute($Params);
                return $Stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Exception $e) {
            throw $e;
        }

        return [];
    }

    // --- PREGUNTAS ---

    /**
     * Obtiene las preguntas asociadas a una encuesta.
     * 
     * @param int $EncuestaId ID encuesta.
     * @return array Lista de preguntas ordenadas.
     */
    public function obtenerPreguntas(int $EncuestaId): array
    {
        try {
            $Sql = "SELECT * FROM encuestas_preguntas WHERE encuesta_id = :id ORDER BY orden ASC";
            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->bindValue(':id', $EncuestaId, PDO::PARAM_INT);
            $Stmt->execute();
            
            return $Stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Agrega una nueva pregunta a la encuesta.
     * 
     * @param int $EncuestaId ID de la encuesta.
     * @param string $Texto Texto de la pregunta.
     * @param string $Tipo Tipo de input (text, select, etc.).
     * @param int $Orden Número de orden.
     * @param int $Requerido 1 si es requerida, 0 si no.
     * @param string|null $OpcionesJson JSON string de opciones.
     * @param string|null $Logica Lógica condicional (opcional).
     * @param string|null $Config Configuración extra JSON (opcional).
     * @return bool Éxito o fallo.
     */
    public function agregarPregunta(
        int $EncuestaId, 
        string $Texto, 
        string $Tipo, 
        int $Orden, 
        int $Requerido, 
        ?string $OpcionesJson, 
        ?string $Logica = null, 
        ?string $Config = null
    ): bool
    {
        // Try-catch removed for debugging
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
    }

    public function actualizarPregunta(
        int $Id, 
        int $EncuestaId, 
        string $Texto, 
        string $Tipo, 
        int $Requerido, 
        ?string $OpcionesJson, 
        ?string $Logica = null, 
        ?string $Config = null
    ): bool
    {
        // Try-catch removed for debugging
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
    }

    /**
     * Reordena un conjunto de preguntas en una transacción.
     * 
     * @param array $Items Array de arays con 'id' y 'orden'.
     * @return bool Éxito o fallo.
     */
    public function reordenarPreguntas(array $Items): bool
    {
        try {
            $this->pdo->beginTransaction();
            
            $Sql = "UPDATE encuestas_preguntas SET orden = :orden WHERE id = :id";
            $Stmt = $this->pdo->prepare($Sql);

            foreach ($Items as $Item) {
                // Validación básica de estructura del item
                if (isset($Item['orden'])) {
                    if (isset($Item['id'])) {
                        $Stmt->bindValue(':orden', (int)$Item['orden'], PDO::PARAM_INT);
                        $Stmt->bindValue(':id', (int)$Item['id'], PDO::PARAM_INT);
                        $Stmt->execute();
                    }
                }
            }
            
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Elimina una pregunta de la base de datos.
     * 
     * @param int $Id ID de la pregunta.
     * @param int $EncuestaId ID de la encuesta.
     * @return bool Éxito o fallo.
     */
    public function eliminarPregunta(int $Id, int $EncuestaId): bool
    {
        try {
            $Sql = "DELETE FROM encuestas_preguntas WHERE id = :id AND encuesta_id = :encuesta_id";
            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->bindValue(':id', $Id, PDO::PARAM_INT);
            $Stmt->bindValue(':encuesta_id', $EncuestaId, PDO::PARAM_INT);
            
            return $Stmt->execute();

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Borra todas las preguntas de una encuesta.
     * 
     * @param int $EncuestaId ID de la encuesta.
     * @return bool Éxito o fallo.
     */
    public function borrarPreguntas(int $EncuestaId): bool
    {
        try {
            $Sql = "DELETE FROM encuestas_preguntas WHERE encuesta_id = :id";
            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->bindValue(':id', $EncuestaId, PDO::PARAM_INT);
            
            return $Stmt->execute();

        } catch (Exception $e) {
            throw $e;
        }
    }

    // --- QR / MEMBRESÍA ---

    /**
     * Obtiene info de un token QR.
     * 
     * @param int $SucursalId ID sucursal.
     * @param string $Mesa Identificador de mesa.
     * @return array|false Datos o false.
     */
    public function obtenerTokenQr(int $SucursalId, string $Mesa): array|false
    {
        try {
            $Sql = "SELECT * FROM sucursales_qr WHERE sucursal_id = :sucursal AND mesa = :mesa LIMIT 1";
            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->bindValue(':sucursal', $SucursalId, PDO::PARAM_INT);
            $Stmt->bindValue(':mesa', $Mesa);
            $Stmt->execute();
            
            return $Stmt->fetch(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Genera un nuevo token QR único.
     * 
     * @param int $SucursalId ID sucursal.
     * @param string $Mesa Identificador de mesa.
     * @return string|false Token generado o false.
     */
    public function generarTokenQr(int $SucursalId, string $Mesa): string|false
    {
        try {
            $Token = bin2hex(random_bytes(16)); 
            
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
            throw $e;
        }
    }
    // --- RESPUESTAS ---

    /**
     * Guarda la respuesta de una encuesta.
     * 
     * @param int $EncuestaId
     * @param int $SucursalId
     * @param array $Respuestas Array [pregunta_id => valor]
     * @param array $Comentarios Array [pregunta_id => texto]
     * @return bool
     */
    public function guardarRespuesta(int $EncuestaId, int $SucursalId, array $Respuestas, array $Comentarios = [], int $Duracion = 0): bool
    {
        try {
            $this->pdo->beginTransaction();

            // 1. Guardar Cabecera
            $SqlHead = "INSERT INTO encuestas_respuestas (encuesta_id, sucursal_id, ip_cliente, user_agent, duracion_segundos) 
                        VALUES (:encuesta, :sucursal, :ip, :ua, :dur)";
            $StmtHead = $this->pdo->prepare($SqlHead);
            $StmtHead->bindValue(':encuesta', $EncuestaId, PDO::PARAM_INT);
            $StmtHead->bindValue(':sucursal', $SucursalId, PDO::PARAM_INT);
            $StmtHead->bindValue(':ip', $_SERVER['REMOTE_ADDR'] ?? null);
            $StmtHead->bindValue(':ua', $_SERVER['HTTP_USER_AGENT'] ?? null);
            $StmtHead->bindValue(':dur', $Duracion, PDO::PARAM_INT);
            $StmtHead->execute();
            
            $RespuestaId = $this->pdo->lastInsertId();

            // 2. Guardar Detalles
            // Nota: Se valida que exista la columna 'comentario' vía migración
            $SqlDet = "INSERT INTO encuestas_respuestas_detalle (respuesta_id, pregunta_id, valor_respuesta, comentario) 
                       VALUES (:resp_id, :preg_id, :val, :comment)"; 
            
            $StmtDet = $this->pdo->prepare($SqlDet);

            foreach ($Respuestas as $PreguntaId => $Valor) {
                // Si es array (checkbox), convertir a JSON o string
                if (is_array($Valor)) {
                    $Valor = implode(', ', $Valor);
                }

                $Comentario = $Comentarios[$PreguntaId] ?? null;

                $StmtDet->bindValue(':resp_id', $RespuestaId, PDO::PARAM_INT);
                $StmtDet->bindValue(':preg_id', $PreguntaId, PDO::PARAM_INT);
                $StmtDet->bindValue(':val', $Valor); 
                $StmtDet->bindValue(':comment', $Comentario);
                
                $StmtDet->execute();
            }

            $this->pdo->commit();
            return true;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
