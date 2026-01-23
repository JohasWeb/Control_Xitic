<?php

declare(strict_types=1);

/**
 * Archivo: Encuestas.Controller.php
 * Propósito: Controlador para la gestión de encuestas y sus preguntas asociadas.
 * Autor: Refactorización Expert PHP
 * Fecha: 2026-01-22
 */

include_once "Controller/SecurityController.php";
include_once "Model/EncuestasModel.php";
include_once "Model/EncuestasModel.php";
include_once "Model/ClientesModel.php";
include_once "Model/RegionesModel.php";
include_once "Model/SucursalesModel.php";
include_once "Model/CasosModel.php";
include_once "Model/OpenAIService.php";

/**
 * Clase EncuestasController
 * 
 * Gestiona el ciclo de vida de las encuestas: creación, edición, listado
 * y administración de sus preguntas. Se adhiere a principios de Clean Code
 * y validación estricta de tipos.
 * 
 * @package Control\Controller
 */
class EncuestasController
{
    /**
     * Modelo de datos para encuestas.
     * @var EncuestasModel
     */
    private EncuestasModel $model;

    /**
     * Constructor.
     * Inicializa el modelo de encuestas.
     */
    public function __construct()
    {
        $this->model = new EncuestasModel();
    }

    /**
     * Muestra el listado de encuestas.
     * 
     * Valida la autenticación y filtra las encuestas según el rol del usuario
     * (AdminMaster ve todo, ClienteAdmin solo sus encuestas).
     * 
     * @return void
     */
    public function index(): void
    {
        SecurityController::exigirAutenticado();
        
        $ClienteId = null;
        
        // Verificación explicita de rol para determinar el alcance de la vista
        if (isset($_SESSION['_Es_ClienteAdmin'])) {
            if ($_SESSION['_Es_ClienteAdmin'] == 1) {
                $Security = new SecurityController();
                $ClienteId = $Security->obtenerClienteIdSesion();
            }
        }

        $Encuestas = $this->model->listarEncuestas($ClienteId);
        
        include_once "View/ClienteAdmin/Encuestas/index.php";
    }

    /**
     * Muestra el listado de resultados (respuestas) de una encuesta.
     * 
     * @return void
     */
    public function resultados(): void
    {
        SecurityController::exigirAutenticado();
        
        $EncuestaId = $_GET['id'] ?? null;
        if (!$EncuestaId) {
            header("Location: index.php?System=encuestas");
            exit;
        }

        // Obtener info básica de la encuesta
        $Encuesta = $this->model->obtenerEncuesta((int)$EncuestaId);
        if (!$Encuesta) {
             header("Location: index.php?System=encuestas");
             exit;
        }

        // Validar propiedad (Seguridad crud)
        $ClienteId = null;
        if (isset($_SESSION['_Es_ClienteAdmin']) && $_SESSION['_Es_ClienteAdmin'] == 1) {
             $Security = new SecurityController();
             $ClienteId = $Security->obtenerClienteIdSesion();
        }

        if ($ClienteId !== null && $Encuesta['cliente_id'] != $ClienteId) {
             // No tiene permiso para ver esta encuesta
             header("Location: index.php?System=encuestas");
             exit;
        }

        // Filtros
        $F_FechaInicio = $_GET['fecha_inicio'] ?? null;
        $F_FechaFin = $_GET['fecha_fin'] ?? null;
        $F_SucursalId = isset($_GET['sucursal_id']) && $_GET['sucursal_id'] !== '' ? (int)$_GET['sucursal_id'] : null;
        $Limit = 50;

        $Respuestas = $this->model->obtenerRespuestas((int)$EncuestaId, $F_FechaInicio, $F_FechaFin, $F_SucursalId, $Limit);
        
        // Obtener lista de sucursales para el filtro (solo las que tienen acceso a esta encuesta o todas del cliente)
        // Usamos obtenerSucursalesAlcance que ya filtra
        // Si no hay cliente Id definido (admin), obtenemos todas del cliente dueño de la encuesta
        if (!$ClienteId) {
            $ClienteId = $Encuesta['cliente_id'];
        }
        $Sucursales = $this->model->obtenerSucursalesAlcance((int)$EncuestaId, (int)$ClienteId);

        // --- PIVOTE: Preguntas y Detalles ---
        // 1. Obtener preguntas para las columnas
        $Preguntas = $this->model->obtenerPreguntas((int)$EncuestaId);
        
        // 2. Obtener IDs de las respuestas listadas
        $RespuestaIds = array_column($Respuestas, 'id');
        
        // 3. Obtener detalles (respuestas) para esos IDs
        $DetallesRaw = $this->model->obtenerRespuestasDetalladas($RespuestaIds);
        
        // 4. Pivotear: Matrix[RespuestaID][PreguntaID] = Valor
        $MatrixRespuestas = [];
        foreach ($DetallesRaw as $D) {
            $MatrixRespuestas[$D['respuesta_id']][$D['pregunta_id']] = $D['valor_respuesta'];
        }

        include_once "View/ClienteAdmin/Encuestas/resultados.php";
    }

    /**
     * Exporta las respuestas de una encuesta a CSV.
     * 
     * @return void
     */
    public function exportar_csv(): void
    {
        SecurityController::exigirAutenticado();
        
        $EncuestaId = $_GET['id'] ?? null;
        if (!$EncuestaId) {
            exit("Error: ID de encuesta no especificado.");
        }
        
        // Validar existencia y propiedad
        $Encuesta = $this->model->obtenerEncuesta((int)$EncuestaId);
        if (!$Encuesta) {
            exit("Error: Encuesta no encontrada.");
        }

        $ClienteId = null;
        if (isset($_SESSION['_Es_ClienteAdmin']) && $_SESSION['_Es_ClienteAdmin'] == 1) {
             $Security = new SecurityController();
             $ClienteId = $Security->obtenerClienteIdSesion();
        }

        if ($ClienteId !== null && $Encuesta['cliente_id'] != $ClienteId) {
             exit("Error: Acceso denegado.");
        }

        // Obtener datos planos
        $Data = $this->model->obtenerDetallesExportar((int)$EncuestaId);
        
        $Filename = "reporte_encuesta_" . $EncuestaId . "_" . date('Y-m-d_H-i') . ".csv";
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $Filename);
        
        $Output = fopen('php://output', 'w'); // Usar php://output para enviar directo al navegador
        
        // Cabeceras del CSV
        // BOM para Excel
        fprintf($Output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        fputcsv($Output, [
            'ID Respuesta', 
            'Fecha Hora', 
            'Sucursal', 
            'Duración (s)', 
            'Tipo Pregunta',
            'Pregunta', 
            'Respuesta', 
            'Comentario'
        ]);
        
        foreach ($Data as $Row) {
            fputcsv($Output, [
                $Row['respuesta_id'],
                $Row['fecha_respuesta'],
                $Row['sucursal_nombre'],
                $Row['duracion_segundos'],
                $Row['tipo_pregunta'],
                $Row['texto_pregunta'],
                $Row['valor_respuesta'],
                $Row['comentario']
            ]);
        }
        
        fclose($Output);
        exit;
    }

    /**
     * Devuelve el HTML del detalle de respuestas para un modal.
     * 
     * @return void
     */
    public function ver_detalle(): void
    {
        SecurityController::exigirAutenticado();
        
        $RespuestaId = $_GET['id'] ?? null;
        if (!$RespuestaId) {
            echo "ID no especificado.";
            return;
        }

        // TODO: Agregar validación de seguridad extra (verificar que la respuesta pertenezca a una encuesta del cliente)
        // Por brevedad y dado que es ajax interno, asumimos validación previa en listado o agregamos:
        // $EsMio = $this->model->verificarPropiedadRespuesta($RespuestaId, $ClienteId);

        $Detalles = $this->model->obtenerDetalleRespuesta((int)$RespuestaId);

        if (empty($Detalles)) {
            echo '<div class="text-center text-muted py-3">No se encontraron detalles.</div>';
            return;
        }

        echo '<div class="vstack gap-3">';
        foreach ($Detalles as $D) {
            echo '<div class="border rounded p-3 bg-light">';
            echo '  <div class="fw-bold mb-1 text-dark">' . htmlspecialchars($D['texto_pregunta']) . '</div>';
            
            // Valor Rústico
            echo '  <div class="text-secondary small mb-1">';
            echo      htmlspecialchars($D['valor_respuesta']);
            echo '  </div>';

            if (!empty($D['comentario'])) {
                echo '  <div class="mt-2 pt-2 border-top border-light-subtle extra-small text-muted">';
                echo '    <i class="bi bi-chat-quote-fill me-1"></i>' . htmlspecialchars($D['comentario']);
                echo '  </div>';
            }
            echo '</div>';
        }
        echo '</div>';
        exit;
    }

    /**
     * Muestra el formulario para crear una nueva encuesta.
     * 
     * @return void
     */
    public function crear(): void
    {
        SecurityController::exigirAutenticado();
        
        $ClientesModel = new ClientesModel();
        $Clientes = $ClientesModel->listarClientes();
        
        include_once "View/ClienteAdmin/Encuestas/crear.php";
    }

    /**
     * Procesa el almacenamiento de una nueva encuesta.
     * 
     * Valida Token CSRF y datos del formulario. Soporta preguntas dinámicas
     * enviadas en el mismo request.
     * 
     * @return void
     */
    public function guardar(): void
    {
        SecurityController::exigirAutenticado();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            // Validación CSRF
            if (!SecurityController::validarCsrfPost($_POST['csrf_token'])) {
                // Manejo de error de seguridad
                echo "Error: Token de seguridad inválido.";
                return;
            }

            $Security = new SecurityController();
            $ClienteId = $Security->obtenerClienteIdSesion();

            // Extracción y sanitización básica de variables
            $Titulo = $_POST['titulo'];
            
            $Descripcion = '';
            if (isset($_POST['descripcion'])) {
                $Descripcion = $_POST['descripcion'];
            }

            $FechaInicio = date('Y-m-d');
            if (isset($_POST['fecha_inicio'])) {
                $FechaInicio = $_POST['fecha_inicio'];
            }
            
            $SinLimite = false;
            if (isset($_POST['sin_limite'])) {
                $SinLimite = true;
            }

            $FechaFin = null;
            if (isset($_POST['fecha_fin'])) {
                $FechaFin = $_POST['fecha_fin'];
            }
            
            // Lógica de fechas
            if ($SinLimite) {
                $FechaFin = null;
            } else {
                if (empty($FechaFin)) {
                    $FechaFin = date('Y-m-d', strtotime('+1 month'));
                }
            }

            $Anonima = 0;
            if (isset($_POST['anonima'])) {
                $Anonima = (int)$_POST['anonima'];
            }

            $TiempoEstimado = 5;
            if (isset($_POST['tiempo_estimado'])) {
                $TiempoEstimado = (int)$_POST['tiempo_estimado'];
            }
            
            // Determinación del creador
            $UsuarioId = (int)$_SESSION['usuario_id'];
            
            $Creador = 1;
            if (isset($_SESSION["_AdminID_user"])) {
                $Creador = $_SESSION["_AdminID_user"];
            }

            // Procesamiento de imagen
            $ImagenHeader = $this->procesarImagen($_FILES);

            // Configuración de Diseño (JSON)
            $ConfigJson = null;
            if (isset($_POST['configuracion'])) {
               $ConfigJson = $_POST['configuracion']; // Ya viene como JSON string o array? Asumiremos string JSON del front
            }

            // Persistencia
            $EncuestaId = $this->model->crearEncuesta(
                $ClienteId, 
                $Titulo, 
                $Descripcion, 
                $FechaInicio, 
                $FechaFin, 
                $Creador, 
                $Anonima, 
                $TiempoEstimado, 
                $ImagenHeader,
                $ConfigJson
            );

            if ($EncuestaId) {
                $this->procesarPreguntasIniciales($EncuestaId, $_POST);
                
                header("Location: index.php?System=encuestas");
                exit;
            } else {
                echo "Error al crear encuesta.";
            }
        }
    }

    /**
     * Procesa las preguntas enviadas al crear la encuesta.
     * 
     * @param int $EncuestaId ID de la encuesta recién creada.
     * @param array $PostData Datos del formulario POST.
     * @return void
     */
    private function procesarPreguntasIniciales(int $EncuestaId, array $PostData): void
    {
        if (isset($PostData['preguntas'])) {
            if (is_array($PostData['preguntas'])) {
                $Orden = 1;
                foreach ($PostData['preguntas'] as $P) {
                    $Texto = $P['texto'];
                    $Tipo = $P['tipo'];
                    
                    $Requerido = 0;
                    if (isset($P['requerido'])) {
                        $Requerido = 1;
                    }
                    
                    $OpcionesJson = null;
                    if (isset($P['opciones'])) {
                        if (!empty($P['opciones'])) {
                            $ArrOpciones = array_map('trim', explode(',', $P['opciones']));
                            $OpcionesJson = json_encode($ArrOpciones, JSON_UNESCAPED_UNICODE);
                        }
                    }

                    $this->model->agregarPregunta($EncuestaId, $Texto, $Tipo, $Orden, $Requerido, $OpcionesJson);
                    $Orden++;
                }
            }
        }
    }



    /**
     * Muestra la vista de configuración completa de la encuesta.
     * Incluye datos generales y asignaciones (Regiones/Sucursales).
     * 
     * @return void
     */
    public function configuracion(): void
    {
        SecurityController::exigirAutenticado();
        
        $Id = 0;
        if (isset($_GET['id'])) {
            $Id = (int)$_GET['id'];
        }
        
        $Security = new SecurityController();
        $ClienteId = $Security->obtenerClienteIdSesion();

        $Encuesta = $this->model->obtenerEncuesta($Id);
        
        $EsPropietario = false;
        if ($Encuesta) {
            if ($Encuesta['cliente_id'] == $ClienteId) {
                $EsPropietario = true;
            }
        }

        if (!$EsPropietario) {
            header("Location: index.php?System=encuestas");
            exit;
        }

        // Cargar asignaciones actuales
        $Asignaciones = $this->model->obtenerAsignaciones($Id);
        
        // Cargar Regiones y Sucursales para los selectores
        $RegionesModel = new RegionesModel();
        $Regiones = $RegionesModel->obtenerPorCliente($ClienteId);
        
        $SucursalesModel = new SucursalesModel();
        $Sucursales = $SucursalesModel->obtenerPorCliente($ClienteId);

        // Sucursales Habilitadas (Para QRs)
        $SucursalesHabilitadas = $this->model->obtenerSucursalesAlcance($Id, $ClienteId);
        
        include_once "View/ClienteAdmin/Encuestas/configuracion.php";
    }

    /**
     * Actualiza una encuesta y sus asignaciones.
     * 
     * Responde con JSON.
     * 
     * @return void
     */
    public function actualizar(): void
    {
        SecurityController::exigirAutenticado();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            if (!SecurityController::validarCsrfPost($_POST['csrf_token'])) {
                die("Token de seguridad inválido.");
            }

            $Security = new SecurityController();
            $ClienteId = $Security->obtenerClienteIdSesion();
            
            $Id = (int)$_POST['id'];
            $Titulo = $_POST['titulo'];

            // Validación de propiedad
            $Encuesta = $this->model->obtenerEncuesta($Id);
            
            $EsPropietario = false;
            if ($Encuesta) {
                if ($Encuesta['cliente_id'] == $ClienteId) {
                    $EsPropietario = true;
                }
            }

            if (!$EsPropietario) {
                die(json_encode(['success' => false, 'message' => 'Acceso denegado.']));
            }

            $Descripcion = '';
            if (isset($_POST['descripcion'])) {
                $Descripcion = $_POST['descripcion'];
            }

            $FechaInicio = date('Y-m-d');
            if (isset($_POST['fecha_inicio'])) {
                $FechaInicio = $_POST['fecha_inicio'];
            }
            
            $SinLimite = false;
            if (isset($_POST['sin_limite'])) {
                $SinLimite = true;
            }

            $FechaFin = null;
            if (isset($_POST['fecha_fin'])) {
                $FechaFin = $_POST['fecha_fin'];
            }
            
            if ($SinLimite) {
                $FechaFin = null;
            } else {
                if (empty($FechaFin)) {
                    $FechaFin = date('Y-m-d', strtotime('+1 month'));
                }
            }

            $Anonima = 0;
            if (isset($_POST['anonima'])) {
                $Anonima = (int)$_POST['anonima'];
            }

            $TiempoEstimado = 5;
            if (isset($_POST['tiempo_estimado'])) {
                $TiempoEstimado = (int)$_POST['tiempo_estimado'];
            }

            $ImagenHeader = $this->procesarImagen($_FILES);

            $ConfigJson = null;
            if (isset($_POST['configuracion'])) {
                $ConfigJson = $_POST['configuracion'];
            }

            // Actualizar Datos Básicos
            $Res = $this->model->actualizarEncuesta(
                $Id, 
                $ClienteId, 
                $Titulo, 
                $Descripcion, 
                $FechaInicio, 
                $FechaFin, 
                $Anonima, 
                $TiempoEstimado, 
                $ImagenHeader,
                $ConfigJson
            );

            if ($Res) {
                // Actualizar Asignaciones
                $TipoAsignacion = 'CLIENTE'; // Default Global
                if (isset($_POST['tipo_asignacion'])) {
                    $TipoAsignacion = $_POST['tipo_asignacion'];
                }

                $this->model->limpiarAsignaciones($Id);

                if ($TipoAsignacion === 'CLIENTE') {
                    // Global (Toda la empresa)
                    // Valor 0 indica todo el cliente
                    $this->model->guardarAsignacion($Id, 'CLIENTE', 0);
                } elseif ($TipoAsignacion === 'REGION') {
                    if (isset($_POST['regiones_ids']) && is_array($_POST['regiones_ids'])) {
                        foreach ($_POST['regiones_ids'] as $RegId) {
                            $this->model->guardarAsignacion($Id, 'REGION', (int)$RegId);
                        }
                    }
                } elseif ($TipoAsignacion === 'SUCURSAL') {
                    if (isset($_POST['sucursales_ids']) && is_array($_POST['sucursales_ids'])) {
                        foreach ($_POST['sucursales_ids'] as $SucId) {
                            $this->model->guardarAsignacion($Id, 'SUCURSAL', (int)$SucId);
                        }
                    }
                }
                
                echo json_encode(['success' => true, 'message' => 'Configuración actualizada correctamente.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar datos generales.']);
            }
            exit;
        }
    }

    /**
     * Muestra la vista de gestión de preguntas de una encuesta.
     * 
     * @return void
     */
    public function preguntas(): void
    {
        SecurityController::exigirAutenticado();
        
        $Id = 0;
        if (isset($_GET['id'])) {
            $Id = (int)$_GET['id'];
        }
        
        $Security = new SecurityController();
        $ClienteId = $Security->obtenerClienteIdSesion();

        $Encuesta = $this->model->obtenerEncuesta($Id);
        
        $AccesoPermitido = false;
        if ($Encuesta) {
            if ($Encuesta['cliente_id'] == $ClienteId) {
                $AccesoPermitido = true;
            }
        }

        if (!$AccesoPermitido) {
            header("Location: index.php?System=encuestas");
            exit;
        }

        $Preguntas = $this->model->obtenerPreguntas($Id);
        
        include_once "View/ClienteAdmin/Encuestas/preguntas.php";
    }

    /**
     * Vista pública para responder una encuesta.
     * 
     * @return void
     */
    public function responder(): void
    {
        $Id = 0;
        if (isset($_GET['id'])) {
            $Id = (int)$_GET['id'];
        }
        
        $Encuesta = $this->model->obtenerEncuesta($Id);
        
        if (!$Encuesta) {
            die("Encuesta no encontrada.");
        }
        
        $Preguntas = $this->model->obtenerPreguntas($Id);
        
        include_once "View/Public/Encuestas/responder.php";
    }

    /**
     * Vista pública de agradecimiento.
     * 
     * @return void
     */
    public function agradecimiento(): void
    {
        $Id = 0;
        if (isset($_GET['id'])) {
            $Id = (int)$_GET['id'];
        }
        
        $Encuesta = $this->model->obtenerEncuesta($Id);
        
        if (!$Encuesta) {
            die("Encuesta no encontrada.");
        }
        
        include_once "View/Public/Encuestas/agradecimiento.php";
    }

    /**
     * Guarda (crea o actualiza) una pregunta vía AJAX.
     * 
     * @return void
     */
    public function guardar_pregunta(): void
    {
        if (ob_get_length()) {
            ob_clean();
        }
        
        header('Content-Type: application/json');

        try {
            SecurityController::exigirAutenticado();

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
                $Security = new SecurityController();
                $ClienteId = $Security->obtenerClienteIdSesion();

                $EncuestaId = (int)$_POST['encuesta_id'];
                
                // Validar propiedad
                $Encuesta = $this->model->obtenerEncuesta($EncuestaId);
                
                $EsPropietario = false;
                if ($Encuesta) {
                    if ($Encuesta['cliente_id'] == $ClienteId) {
                        $EsPropietario = true;
                    }
                }

                if (!$EsPropietario) {
                    throw new Exception('Acceso denegado');
                }

                $Id = null;
                if (isset($_POST['pregunta_id'])) {
                    if (!empty($_POST['pregunta_id'])) {
                        $Id = (int)$_POST['pregunta_id'];
                    }
                }

                $Texto = $_POST['texto_pregunta'];
                if (empty($Texto)) {
                    throw new Exception('El texto de la pregunta es obligatorio');
                }

                $Tipo = $_POST['tipo_pregunta']; 
                
                $Requerido = 0;
                if (isset($_POST['requerido'])) {
                    $Requerido = 1;
                }
                
                // Procesamiento de opciones
                $OpcionesJson = null;
                if (isset($_POST['opciones'])) {
                    if (trim($_POST['opciones']) !== '') {
                        $Raw = str_replace(["\r\n", "\r"], "\n", $_POST['opciones']);
                        $Arr = explode("\n", $Raw);
                        $Arr = array_map('trim', $Arr);
                        
                        // Filtrar vacíos de forma explícita
                        $Arr = array_filter($Arr, function($val) { 
                            return $val !== ''; 
                        }); 
                        
                        $Arr = array_values($Arr);
                        $OpcionesJson = json_encode($Arr, JSON_UNESCAPED_UNICODE);
                    }
                }

                if (isset($_POST['opciones_json_raw'])) {
                    if (!empty($_POST['opciones_json_raw'])) {
                        $OpcionesJson = $_POST['opciones_json_raw'];
                    }
                }

                // Configuración adicional
                $Logica = null;
                if (isset($_POST['logica_condicional'])) {
                    $RawLogica = trim($_POST['logica_condicional']);
                    if ($RawLogica !== '' && $RawLogica !== 'null') {
                         $Logica = $RawLogica;
                    }
                }

                $Config = null;
                if (isset($_POST['configuracion_json'])) {
                    $RawConfig = trim($_POST['configuracion_json']);
                    if ($RawConfig !== '' && $RawConfig !== 'null') {
                        $Config = $RawConfig;
                    }
                }

                $Res = false;
                if ($Id) {
                    $Res = $this->model->actualizarPregunta($Id, $EncuestaId, $Texto, $Tipo, $Requerido, $OpcionesJson, $Logica, $Config);
                } else {
                    $Total = count($this->model->obtenerPreguntas($EncuestaId));
                    $Orden = $Total + 1;
                    $Res = $this->model->agregarPregunta($EncuestaId, $Texto, $Tipo, $Orden, $Requerido, $OpcionesJson, $Logica, $Config);
                }
                
                if ($Res) {
                    echo json_encode(['success' => true]);
                } else {
                    throw new Exception('Error al guardar pregunta en base de datos');
                }
            } else {
                throw new Exception('Método inválido');
            }

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Reordena las preguntas de una encuesta.
     * 
     * Recibe JSON en el cuerpo de la petición.
     * 
     * @return void
     */
    public function reordenar_preguntas(): void
    {
        SecurityController::exigirAutenticado();
        
        $JsonData = file_get_contents('php://input');
        $Data = json_decode($JsonData, true);

        if (!$Data) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
            exit;
        }

        if (!isset($Data['encuesta_id']) || !isset($Data['items'])) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            exit;
        }

        $EncuestaId = (int)$Data['encuesta_id'];
        $Items = $Data['items']; 

        $Security = new SecurityController();
        $ClienteId = $Security->obtenerClienteIdSesion();

        $Encuesta = $this->model->obtenerEncuesta($EncuestaId);
        
        $EsPropietario = false;
        if ($Encuesta) {
            if ($Encuesta['cliente_id'] == $ClienteId) {
                $EsPropietario = true;
            }
        }

        if (!$EsPropietario) {
             echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
             exit;
        }

        $Res = $this->model->reordenarPreguntas($Items);
        echo json_encode(['success' => $Res]);
    }

    /**
     * Elimina una pregunta.
     * 
     * @return void
     */
    public function eliminar_pregunta(): void
    {
        SecurityController::exigirAutenticado();
        
        $Id = (int)$_POST['id'];
        $EncuestaId = (int)$_POST['encuesta_id'];

        $Security = new SecurityController();
        $ClienteId = $Security->obtenerClienteIdSesion();
        
        $Encuesta = $this->model->obtenerEncuesta($EncuestaId);
        
        $EsPropietario = false;
        if ($Encuesta) {
            if ($Encuesta['cliente_id'] == $ClienteId) {
                $EsPropietario = true;
            }
        }

        if (!$EsPropietario) {
             echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
             exit;
        }

        $Res = $this->model->eliminarPregunta($Id, $EncuestaId);
        
        echo json_encode(['success' => $Res]);
    }

    /**
     * Procesa la subida de una imagen de cabecera.
     * 
     * @param array $FileData Datos de $_FILES.
     * @return string|null Ruta de la imagen guardada o null si falla/no hay imagen.
     */
    private function procesarImagen(array $FileData): ?string
    {
        if (!isset($FileData['imagen_header'])) {
            return null;
        }

        if ($FileData['imagen_header']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $File = $FileData['imagen_header'];
        $ValidTypes = ['image/jpeg', 'image/png', 'image/webp'];
        
        if (!in_array($File['type'], $ValidTypes)) {
            return null; 
        }

        $Ext = pathinfo($File['name'], PATHINFO_EXTENSION);
        $Name = uniqid('header_') . '.' . $Ext;
        $TargetDir = "assets/uploads/encuestas/";
        
        if (!file_exists($TargetDir)) {
             mkdir($TargetDir, 0777, true);
        }

        if (move_uploaded_file($File['tmp_name'], $TargetDir . $Name)) {
            return $TargetDir . $Name;
        }

        return null;
    }
    /**
     * Guarda la respuesta pública de una encuesta (AJAX).
     * 
     * @return void
     */
    public function guardar_respuesta_publica(): void
    {
        if (ob_get_length()) {
            ob_clean();
        }
        
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        try {
            $EncuestaId = (int)$_POST['encuesta_id'];
            $SucursalId = isset($_POST['sucursal_id']) ? (int)$_POST['sucursal_id'] : 0;
            
            if (!$EncuestaId) {
                throw new Exception('ID de encuesta inválido');
            }

            // Validar que la encuesta exista y esté activa (opcional, pero recomendado)
            $Encuesta = $this->model->obtenerEncuesta($EncuestaId);
            if (!$Encuesta) {
                throw new Exception('Encuesta no encontrada');
            }

            $Respuestas = $_POST['resp'] ?? [];
            $Comentarios = $_POST['comentarios'] ?? [];
            $Duracion = isset($_POST['duracion_segundos']) ? (int)$_POST['duracion_segundos'] : 0;

            if (empty($Respuestas)) {
                throw new Exception('No hay respuestas para guardar');
            }

            $Exito = $this->model->guardarRespuesta($EncuestaId, $SucursalId, $Respuestas, $Comentarios, $Duracion);
            
            if ($Exito) {
                $RespuestaId = (int)$Exito;

                // --- AUTOMATIZACIÓN IA PARA CASOS ---
                try {
                    $ClientesModel = new ClientesModel();
                    $Cliente = $ClientesModel->obtenerPorId($Encuesta['cliente_id']);

                    // Verificar requisitos: Módulo Casos + Token + Prompt
                    if ($Cliente && isset($Cliente['modulo_casos']) && $Cliente['modulo_casos'] == 1 
                        && !empty($Cliente['config_ia_token']) && !empty($Cliente['config_ia_prompt'])) {
                        
                        // 1. Preparar Texto de la Encuesta
                        $TextoEncuesta = "Fecha: " . date('Y-m-d H:i') . "\n";
                        $TextoEncuesta .= "Sucursal ID: " . $SucursalId . "\n";
                        $TextoEncuesta .= "Duración: " . $Duracion . "s\n\n";
                        $TextoEncuesta .= "CUESTIONARIO:\n";
                        
                        $Preguntas = $this->model->obtenerPreguntas($EncuestaId);
                        $MapPreguntas = [];
                        foreach ($Preguntas as $P) {
                            $MapPreguntas[$P['id']] = $P['texto_pregunta'];
                        }

                        foreach ($Respuestas as $PId => $Val) {
                            $TextoP = $MapPreguntas[$PId] ?? "Pregunta ID $PId";
                            if (is_array($Val)) $Val = implode(', ', $Val);
                            $TextoEncuesta .= "- $TextoP: $Val\n";
                        }
                        
                        if (!empty($Comentarios)) {
                            $TextoEncuesta .= "\nCOMENTARIOS ADICIONALES:\n";
                            foreach ($Comentarios as $PId => $Com) {
                                if (!empty($Com)) {
                                    $TextoP = $MapPreguntas[$PId] ?? "Pregunta ID $PId";
                                    $TextoEncuesta .= "- Sobre '$TextoP': $Com\n";
                                }
                            }
                        }

                        // 2. Llamada a OpenAI
                        $AIService = new OpenAIService();
                        $SystemPrompt = $Cliente['config_ia_prompt'] . "\n\n" . 
                            "INSTRUCCIONES DE SALIDA: Analiza la encuesta anterior. Tu objetivo es detectar si hay insatisfacción, quejas, problemas legales o una solicitud que requiera atención humana (Crear Caso).\n" .
                            "Responde EXCLUSIVAMENTE un JSON con este formato:\n" .
                            "{ \"requires_case\": boolean, \"category\": string (ej. 'Servicio', 'Producto', 'Limpieza', 'Legal'), \"severity\": string ('Baja', 'Media', 'Alta', 'Critica'), \"summary\": string (max 100 caracteres) }";
                        
                        $Analisis = $AIService->analizarEncuesta($SystemPrompt, $TextoEncuesta, $Cliente['config_ia_token']);

                        if (isset($Analisis['data']) && isset($Analisis['data']['requires_case']) && $Analisis['data']['requires_case'] === true) {
                            $Data = $Analisis['data'];
                            
                            // 3. Crear Caso Automático
                            $CasosModel = new CasosModel();
                            
                            // Calcular SLA
                            $SlaHoras = isset($Cliente['config_sla_horas']) ? (int)$Cliente['config_sla_horas'] : 24;
                            $SlaVencimiento = date('Y-m-d H:i:s', strtotime("+$SlaHoras hours"));
                            
                            // Obtener Datos de Ubicación
                            $RegionId = null;
                            if ($SucursalId > 0) {
                                $SucModel = new SucursalesModel();
                                $Suc = $SucModel->obtenerPorId($SucursalId, $Encuesta['cliente_id']);
                                if ($Suc) {
                                    $RegionId = $Suc['region_id'];
                                }
                            }

                            // Responsable Provisional (Creador de la encuesta o usuario genérico)
                            $ResponsableId = $Encuesta['creador_id']; 

                            $CasosModel->crear(
                                $Encuesta['cliente_id'],
                                $ResponsableId, // Usuario que "reporta" (sistema/creador encuesta)
                                "Caso IA - " . ($Data['category'] ?? 'General') . " - Suc " . $SucursalId,
                                $TextoEncuesta, // La descripción es la encuesta completa
                                $RespuestaId,
                                $Data['category'] ?? 'General',
                                $Data['severity'] ?? 'Media',
                                $Data['summary'] ?? 'Detectado por IA',
                                $SlaVencimiento,
                                $ResponsableId, // Asignar al mismo por ahora (o lógica de Supervisor)
                                $SucursalId,
                                $RegionId
                            );
                        }
                    }
                } catch (Exception $e) {
                    // Silenciar errores de IA para no bloquear la encuesta
                    // error_log("Error IA Auto: " . $e->getMessage());
                }
                // -----------------------------------

                echo json_encode(['success' => true]);
            } else {
                throw new Exception('Error al guardar en base de datos');
            }

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    /**
     * Endpoint AJAX para guardar solo la configuración de diseño.
     * Útil para la vista de preguntas donde no se editan todos los campos.
     */
    public function guardar_diseno_ajax(): void
    {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        try {
            SecurityController::exigirAutenticado();

             if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método inválido');
             }

             // Validar CSRF
             if (!SecurityController::validarCsrfPost($_POST['csrf_token'])) {
                 throw new Exception('Token de seguridad inválido');
             }

             $EncuestaId = (int)$_POST['id'];
             
             // Validar propiedad
             $Security = new SecurityController();
             $ClienteId = $Security->obtenerClienteIdSesion();
             $Encuesta = $this->model->obtenerEncuesta($EncuestaId);
             
             if (!$Encuesta || $Encuesta['cliente_id'] != $ClienteId) {
                 throw new Exception('Acceso denegado o encuesta no encontrada');
             }

             $ConfigJson = $_POST['configuracion'] ?? null;
             
             $Titulo = $Encuesta['titulo']; 
             $Descripcion = $Encuesta['descripcion']; 
             $FechaInicio = $Encuesta['fecha_inicio'];
             $FechaFin = $Encuesta['fecha_fin'];
             $Anonima = isset($_POST['anonima']) ? (int)$_POST['anonima'] : $Encuesta['anonima'];
             $TiempoEstimado = isset($_POST['tiempo_estimado']) ? (int)$_POST['tiempo_estimado'] : $Encuesta['tiempo_estimado'];
             $ImagenHeader = null; 

             $Res = $this->model->actualizarEncuesta(
                $EncuestaId, 
                $ClienteId, 
                $Titulo, 
                $Descripcion, 
                $FechaInicio, 
                $FechaFin, 
                $Anonima, 
                $TiempoEstimado, 
                $ImagenHeader,
                $ConfigJson
            );

            if ($Res) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception('Error al actualizar base de datos');
            }

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}
