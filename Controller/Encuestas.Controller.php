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
            $FechaFin = isset($_POST['fecha_fin']) ? $_POST['fecha_fin'] : date('Y-m-d', strtotime('+1 month'));
            $Anonima = isset($_POST['anonima']) ? (int)$_POST['anonima'] : 0;
            $TiempoEstimado = isset($_POST['tiempo_estimado']) ? (int)$_POST['tiempo_estimado'] : 5;
            
            // 1. Crear la cabecera
            $UsuarioId = (int)$_SESSION['usuario_id']; // Asegurar que usuario_id esté en sesión
            // En LoginController: $_SESSION['usuario_id'] = $Info['id'];
            // Si no está... usar $_SESSION["_AdminID_user"]

            $Creador = isset($_SESSION["_AdminID_user"]) ? $_SESSION["_AdminID_user"] : 1;

            $EncuestaId = $this->model->crearEncuesta($ClienteId, $Titulo, $Descripcion, $FechaInicio, $FechaFin, $Creador, $Anonima, $TiempoEstimado);

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
}
