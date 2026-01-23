<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include_once "Controller/SecurityController.php";
include_once "Model/CasosModel.php";
include_once "Model/ClientesModel.php";
include_once "Model/OpenAIService.php";

class CasosController
{
    private $model;
    private $clientesModel;
    private $aiService;

    public function __construct()
    {
        $this->model = new CasosModel();
        $this->clientesModel = new ClientesModel();
        $this->aiService = new OpenAIService();
    }

    public function index()
    {
        SecurityController::iniciarSesionSegura();
        
        $Security = new SecurityController();
        $ClienteId = $Security->obtenerClienteIdSesion();
        
        // Verificar Módulo
        $Cliente = $this->clientesModel->obtenerPorId($ClienteId);
        if (!$Cliente || $Cliente['modulo_casos'] != 1) {
            die("Módulo de Casos no habilitado para este cliente.");
        }

        $Casos = $this->model->obtenerPorCliente($ClienteId);
        
        include_once "View/ClienteAdmin/Casos/index.php";
    }

    public function crear()
    {
        SecurityController::iniciarSesionSegura();
        include_once "View/ClienteAdmin/Casos/crear.php";
    }

    public function guardar()
    {
        SecurityController::iniciarSesionSegura();
        
        $Security = new SecurityController();
        $ClienteId = $Security->obtenerClienteIdSesion();
        $UsuarioId = (int)$_SESSION["_AdminID_user"];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $Titulo = trim($_POST['titulo'] ?? '');
            $Descripcion = trim($_POST['descripcion'] ?? '');

            if (empty($Titulo) || empty($Descripcion)) {
                die("Datos incompletos");
            }

            // 1. Guardar Caso Inicial
            $CasoId = $this->model->crear($ClienteId, $UsuarioId, $Titulo, $Descripcion);

            if ($CasoId) {
                // 2. Obtener Config IA del Cliente
                $Cliente = $this->clientesModel->obtenerPorId($ClienteId);
                $Token = $Cliente['config_ia_token'];
                $Prompt = $Cliente['config_ia_prompt'];

                if (!empty($Token) && !empty($Prompt)) {
                    // 3. Llamar a OpenAI
                    $Resultado = $this->aiService->analizar($Prompt, "Asunto: $Titulo\n\n$Descripcion", $Token);

                    if (isset($Resultado['content'])) {
                        // 4. Guardar Respuesta
                        $this->model->guardarRespuestaIA($CasoId, $Resultado['content']);
                    } else {
                        // Guardar error como respuesta (opcional)
                        $Error = $Resultado['error'] ?? 'Error desconocido';
                        $this->model->guardarRespuestaIA($CasoId, "Error al generar respuesta IA: $Error");
                    }
                } else {
                    $this->model->guardarRespuestaIA($CasoId, "La IA no está configurada (Falta Token o Prompt).");
                }

                header("Location: index.php?System=casos&a=ver&id=$CasoId");
                exit;
            }
        }
    }

    public function ver()
    {
        SecurityController::iniciarSesionSegura();
        $Id = (int)($_GET['id'] ?? 0);
        
        $Caso = $this->model->obtenerPorId($Id);
        if (!$Caso) {
            header("Location: index.php?System=casos");
            exit;
        }

        include_once "View/ClienteAdmin/Casos/ver.php";
    }

    /**
     * Guarda la configuración de SLA del cliente (Ajax).
     */
    public function guardar_config()
    {
        // Limpiamos buffer
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json');

        SecurityController::iniciarSesionSegura();
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método no permitido');
            }

            if (!isset($_POST['csrf_token']) || !SecurityController::validarCsrfPost($_POST['csrf_token'])) {
                throw new Exception('Token de seguridad inválido');
            }

            $Security = new SecurityController();
            $ClienteId = $Security->obtenerClienteIdSesion();
            
            $SlaHoras = isset($_POST['sla_horas']) ? (int)$_POST['sla_horas'] : 24;
            if ($SlaHoras < 1) $SlaHoras = 1;
            if ($SlaHoras > 720) $SlaHoras = 720; // Max 30 días

            // Usamos ClientesModel para actualizar
            // Nota: ClientesModel::actualizar requiere muchos datos, mejor crear un método específico 'actualizarConfigIA' o similar en el modelo
            // Por ahora, añadiremos un método rápido al ClientesModel
            if ($this->clientesModel->actualizarSLA($ClienteId, $SlaHoras)) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception('Error al actualizar configuración');
            }

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
