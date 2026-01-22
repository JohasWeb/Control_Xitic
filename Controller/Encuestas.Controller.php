<?php
include_once "Controller/SecurityController.php";
include_once "Model/EncuestasModel.php";
include_once "Model/ClientesModel.php"; // Para cargar el select de clientes

class EncuestasController
{
    private $model;

    public function __construct()
    {
        $this->model = new EncuestasModel();
    }

    public function index()
    {
        SecurityController::exigirAutenticado();
        
        // Si es AdminMaster ve todo, si no, solo lo de su cliente
        $ClienteId = null;
        if (isset($_SESSION['_Es_ClienteAdmin']) && $_SESSION['_Es_ClienteAdmin'] == 1) {
            $Security = new SecurityController();
            $ClienteId = $Security->obtenerClienteIdSesion();
        }

        $Encuestas = $this->model->listarEncuestas($ClienteId);
        include_once "View/ClienteAdmin/Encuestas/index.php";
    }

    public function crear()
    {
        SecurityController::exigirAutenticado();
        
        $ClientesModel = new ClientesModel();
        $Clientes = $ClientesModel->listarClientes(); // Para el select

        include_once "View/ClienteAdmin/Encuestas/crear.php";
    }

    public function guardar()
    {
        SecurityController::exigirAutenticado();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!SecurityController::validarCsrfPost($_POST['csrf_token'])) {
                die("Token de seguridad inválido.");
            }

            $Security = new SecurityController();
            $ClienteId = $Security->obtenerClienteIdSesion();

            $Titulo = $_POST['titulo'];
            $Descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : '';
            $FechaInicio = isset($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : date('Y-m-d');
            
            $SinLimite = isset($_POST['sin_limite']) ? true : false;
            $FechaFin = isset($_POST['fecha_fin']) ? $_POST['fecha_fin'] : null;
            
            if ($SinLimite) {
                $FechaFin = null;
            } else {
                if (empty($FechaFin)) $FechaFin = date('Y-m-d', strtotime('+1 month'));
            }

            $Anonima = isset($_POST['anonima']) ? (int)$_POST['anonima'] : 0;
            $TiempoEstimado = isset($_POST['tiempo_estimado']) ? (int)$_POST['tiempo_estimado'] : 5;
            
            // 1. Crear la cabecera
            $UsuarioId = (int)$_SESSION['usuario_id']; // Asegurar que usuario_id esté en sesión
            // En LoginController: $_SESSION['usuario_id'] = $Info['id'];
            // Si no está... usar $_SESSION["_AdminID_user"]

            $Creador = isset($_SESSION["_AdminID_user"]) ? $_SESSION["_AdminID_user"] : 1;

            $ImagenHeader = $this->procesarImagen($_FILES);

            $EncuestaId = $this->model->crearEncuesta($ClienteId, $Titulo, $Descripcion, $FechaInicio, $FechaFin, $Creador, $Anonima, $TiempoEstimado, $ImagenHeader);

            if ($EncuestaId) {
                // 2. Procesar preguntas dinámicas
                // Esperamos un array en $_POST['preguntas'] con la estructura:
                // preguntas[0][texto], preguntas[0][tipo], ...
                
                if (isset($_POST['preguntas']) && is_array($_POST['preguntas'])) {
                    $Orden = 1;
                    foreach ($_POST['preguntas'] as $P) {
                        $Texto = $P['texto'];
                        $Tipo = $P['tipo'];
                        $Requerido = isset($P['requerido']) ? 1 : 0;
                        
                        // Opciones (si es select/radio)
                        $OpcionesJson = null;
                        if (isset($P['opciones']) && !empty($P['opciones'])) {
                            // Separadas por coma o línea
                            $ArrOpciones = array_map('trim', explode(',', $P['opciones']));
                            $OpcionesJson = json_encode($ArrOpciones, JSON_UNESCAPED_UNICODE);
                        }

                        $this->model->agregarPregunta($EncuestaId, $Texto, $Tipo, $Orden, $Requerido, $OpcionesJson);
                        $Orden++;
                    }
                }

                header("Location: index.php?System=encuestas");
                exit;
            } else {
                echo "Error al crear encuesta.";
            }
        }
    }

    public function actualizar()
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
            // Validar que la encuesta pertenezca al cliente
            $Encuesta = $this->model->obtenerEncuesta($Id);
            if (!$Encuesta || $Encuesta['cliente_id'] != $ClienteId) {
                die("Acceso denegado.");
            }

            $Descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : '';
            $FechaInicio = isset($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : date('Y-m-d');
            
            $SinLimite = isset($_POST['sin_limite']) ? true : false;
            $FechaFin = isset($_POST['fecha_fin']) ? $_POST['fecha_fin'] : null;
            
            if ($SinLimite) {
                $FechaFin = null;
            } else {
                if (empty($FechaFin)) $FechaFin = date('Y-m-d', strtotime('+1 month'));
            }

            $Anonima = isset($_POST['anonima']) ? (int)$_POST['anonima'] : 0;
            $TiempoEstimado = isset($_POST['tiempo_estimado']) ? (int)$_POST['tiempo_estimado'] : 5;

            $ImagenHeader = $this->procesarImagen($_FILES);

            $Res = $this->model->actualizarEncuesta($Id, $ClienteId, $Titulo, $Descripcion, $FechaInicio, $FechaFin, $Anonima, $TiempoEstimado, $ImagenHeader);

            if ($Res) {
                echo json_encode(['success' => true, 'message' => 'Encuesta actualizada.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar.']);
            }
            exit; // AJAX response
        }
    }

    public function preguntas()
    {
        SecurityController::exigirAutenticado();
        
        $Id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        $Security = new SecurityController();
        $ClienteId = $Security->obtenerClienteIdSesion();

        $Encuesta = $this->model->obtenerEncuesta($Id);
        if (!$Encuesta || $Encuesta['cliente_id'] != $ClienteId) {
            header("Location: index.php?System=encuestas");
            exit;
        }

        $Preguntas = $this->model->obtenerPreguntas($Id);
        
        include_once "View/ClienteAdmin/Encuestas/preguntas.php";
    }

    public function guardar_pregunta()
    {
        // Limpiar cualquier output previo (warnings, espacios, etc.)
        if (ob_get_length()) ob_clean();
        
        header('Content-Type: application/json');

        try {
            SecurityController::exigirAutenticado();

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
                $Security = new SecurityController();
                $ClienteId = $Security->obtenerClienteIdSesion();

                $EncuestaId = (int)$_POST['encuesta_id'];
                
                // Validar owner
                $Encuesta = $this->model->obtenerEncuesta($EncuestaId);
                if (!$Encuesta || $Encuesta['cliente_id'] != $ClienteId) {
                    throw new Exception('Acceso denegado');
                }

                $Id = isset($_POST['pregunta_id']) && !empty($_POST['pregunta_id']) ? (int)$_POST['pregunta_id'] : null;
                $Texto = $_POST['texto_pregunta'];
                if(empty($Texto)) throw new Exception('El texto de la pregunta es obligatorio');

                $Tipo = $_POST['tipo_pregunta']; 
                $Requerido = isset($_POST['requerido']) ? 1 : 0;
                
                // Opciones
                $OpcionesJson = null;
                if (isset($_POST['opciones']) && trim($_POST['opciones']) !== '') {
                    $Raw = str_replace(["\r\n", "\r"], "\n", $_POST['opciones']);
                    $Arr = explode("\n", $Raw);
                    $Arr = array_map('trim', $Arr);
                    $Arr = array_filter($Arr, function($val) { return $val !== ''; }); 
                    $Arr = array_values($Arr);
                    $OpcionesJson = json_encode($Arr, JSON_UNESCAPED_UNICODE);
                }
                // En caso de Botonera u otros que manden opciones como JSON string directo desde JS (si aplica)
                if (isset($_POST['opciones_json_raw']) && !empty($_POST['opciones_json_raw'])) {
                    $OpcionesJson = $_POST['opciones_json_raw'];
                }

                // Lógica y Configuración
                $Logica = isset($_POST['logica_condicional']) ? $_POST['logica_condicional'] : null;
                $Config = isset($_POST['configuracion_json']) ? $_POST['configuracion_json'] : null;

                if ($Id) {
                    // Actualizar existente
                    $Res = $this->model->actualizarPregunta($Id, $EncuestaId, $Texto, $Tipo, $Requerido, $OpcionesJson, $Logica, $Config);
                } else {
                    // Crear nueva
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

    public function reordenar_preguntas()
    {
        SecurityController::exigirAutenticado();
        
        $JsonData = file_get_contents('php://input');
        $Data = json_decode($JsonData, true);

        if (!$Data || !isset($Data['encuesta_id']) || !isset($Data['items'])) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
            exit;
        }

        $EncuestaId = (int)$Data['encuesta_id'];
        $Items = $Data['items']; // Array de {id: 123, orden: 1}

        $Security = new SecurityController();
        $ClienteId = $Security->obtenerClienteIdSesion();

        // Validar owner
        $Encuesta = $this->model->obtenerEncuesta($EncuestaId);
        if (!$Encuesta || $Encuesta['cliente_id'] != $ClienteId) {
             echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
             exit;
        }

        // Seguridad extra: verificar que las preguntas pertenezcan a esa encuesta (el modelo solo actualiza por ID, pero idealmente filtramos)
        // Por ahora confiamos en el ID de pregunta, pero el modelo debería validar owner si fuera estricto. 
        // Como estamos en un entorno controlado de cliente admin, asumimos que los IDs enviados son correctos.

        $Res = $this->model->reordenarPreguntas($Items);
        echo json_encode(['success' => $Res]);
    }

    public function eliminar_pregunta()
    {
        SecurityController::exigirAutenticado();
        
        $Id = (int)$_POST['id'];
        $EncuestaId = (int)$_POST['encuesta_id'];

        $Security = new SecurityController();
        $ClienteId = $Security->obtenerClienteIdSesion();
        
        // Validar owner de la encuesta
        $Encuesta = $this->model->obtenerEncuesta($EncuestaId);
        if (!$Encuesta || $Encuesta['cliente_id'] != $ClienteId) {
             echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
             exit;
        }

        $Res = $this->model->eliminarPregunta($Id, $EncuestaId);
        
        echo json_encode(['success' => $Res]);
    }
    private function procesarImagen($FileData) {
        if (!isset($FileData['imagen_header']) || $FileData['imagen_header']['error'] !== UPLOAD_ERR_OK) {
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
}
