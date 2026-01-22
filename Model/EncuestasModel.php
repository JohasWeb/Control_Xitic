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
            $Sql = "SELECT e.*, c.nombre_comercial as cliente_nombre 
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
            return array();
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
            return false;
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
        ?string $ImagenHeader = null
    ): string|false
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
        ?string $ImagenHeader = null
    ): bool
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
            return [];
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
            return false;
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
            return false;
        }
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
            return array();
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

    /**
     * Actualiza una pregunta existente.
     * 
     * @param int $Id ID de la pregunta.
     * @param int $EncuestaId ID de la encuesta (para safe check).
     * @param string $Texto Texto.
     * @param string $Tipo Tipo.
     * @param int $Requerido Requerido (1/0).
     * @param string|null $OpcionesJson Opciones JSON.
     * @param string|null $Logica Lógica condicional.
     * @param string|null $Config Configuración JSON.
     * @return bool Éxito o fallo.
     */
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
            return false;
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
            return false;
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
            return false;
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
            return false;
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
            return false;
        }
    }
}
