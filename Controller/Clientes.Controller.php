<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include_once "Controller/SecurityController.php";
include_once "Model/ClientesModel.php";
include_once "Model/UsuariosModel.php";

class ClientesController
{
    private $model;
    private $userModel;

    public function __construct()
    {
        $this->model = new ClientesModel();
        $this->userModel = new UsuariosModel();
    }

    public function index()
    {
        SecurityController::iniciarSesionSegura();
        SecurityController::exigirAdminMaster();

        $Clientes = $this->model->obtenerTodos();
        
        include_once "View/AdminMaster/Clientes/index.php";
    }

    public function ver()
    {
        SecurityController::iniciarSesionSegura();
        SecurityController::exigirAdminMaster();

        $Id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $Cliente = $this->model->obtenerPorId($Id);

        if (!$Cliente) {
            header("Location: index.php?System=clientes");
            exit;
        }

        $Stats = $this->model->obtenerStats($Id);
        
        include_once "View/AdminMaster/Clientes/ver.php";
    }

    public function guardar()
    {
        // Limpiamos buffer
        while (ob_get_level()) ob_end_clean();

        SecurityController::iniciarSesionSegura();
        SecurityController::exigirAdminMaster();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
             header('Content-Type: application/json');
             echo json_encode(['success' => false, 'message' => 'Método no permitido']);
             exit;
        }

        if (!isset($_POST['csrf_token']) || !SecurityController::validarCsrfPost($_POST['csrf_token'])) {
             header('Content-Type: application/json');
             echo json_encode(['success' => false, 'message' => 'Error de seguridad: Token CSRF inválido']);
             exit;
        }

        $Id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $Nombre = isset($_POST['nombre_comercial']) ? trim($_POST['nombre_comercial']) : '';
        // RFC eliminado, se reemplaza por Comentarios
        $Comentarios = isset($_POST['comentarios']) ? trim($_POST['comentarios']) : '';
        $Razon = null; 
        
        // Manejo de Logo
        $LogoUrl = null;
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $DirSubida = '../Archivos/LogosClientes/';
            // Crear dir si no existe
            if (!is_dir($DirSubida)) {
                mkdir($DirSubida, 0755, true);
            }

            $InfoArchivo = pathinfo($_FILES['logo']['name']);
            $Ext = strtolower($InfoArchivo['extension']);
            $ExtensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];
            
            if (in_array($Ext, $ExtensionesPermitidas)) {
                $NombreNuevo = 'logo_' . uniqid() . '.' . $Ext;
                $RutaFinal = $DirSubida . $NombreNuevo;
                
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $RutaFinal)) {
                    $LogoUrl = $RutaFinal;
                }
            }
        }
        
        // Datos Encargado
        $AdminNombre = isset($_POST['admin_nombre']) ? trim($_POST['admin_nombre']) : '';
        $AdminApellido = isset($_POST['admin_apellido']) ? trim($_POST['admin_apellido']) : '';
        $EmailAdmin = isset($_POST['email_admin']) ? trim($_POST['email_admin']) : '';

        // Validación
        if ($Nombre === '' || $AdminNombre === '' || $EmailAdmin === '') {
             die(json_encode(['success' => false, 'message' => 'Error: Faltan datos obligatorios.']));
        }

        try {
            if ($Id > 0) {
                // Edición
                $this->model->actualizar($Id, $Nombre, $Razon, $Comentarios, $LogoUrl);
                $this->model->actualizar($Id, $Nombre, $Razon, $Comentarios, $LogoUrl);
                echo json_encode(['success' => true, 'message' => 'Cliente actualizado correctamente']);
                exit;
            } else {
                // Creación
                $NuevoClienteId = $this->model->crear($Nombre, $Razon, $Comentarios, $LogoUrl);

                if (!$NuevoClienteId) {
                    throw new Exception("No se pudo registrar el cliente en la base de datos.");
                }

                $PasswordRaw = $this->userModel->generarPassword(12);

                $this->userModel->crearUsuario(
                    $NuevoClienteId,
                    $EmailAdmin,
                    $PasswordRaw,
                    $AdminNombre,
                    $AdminApellido,
                    'ClienteAdmin'
                );

                // Generar TXT
                $ContenidoTxt = "BIENVENIDO A XITIC\r\n";
                $ContenidoTxt .= "==================\r\n\r\n";
                $ContenidoTxt .= "Hola $AdminNombre,\r\n\r\n";
                $ContenidoTxt .= "Bienvenido a la plataforma. Cuenta creada para: $Nombre\r\n";
                $ContenidoTxt .= "Acceso: http://" . $_SERVER['HTTP_HOST'] . "/Control/\r\n";
                $ContenidoTxt .= "Usuario: $EmailAdmin\r\n";
                $ContenidoTxt .= "Pass: $PasswordRaw\r\n";
                
                $NombreArchivo = "Credenciales_" . preg_replace('/[^a-zA-Z0-9]/', '_', $Nombre) . ".txt";

                $NombreArchivo = "Credenciales_" . preg_replace('/[^a-zA-Z0-9]/', '_', $Nombre) . ".txt";

                echo json_encode([
                    'success' => true,
                    'message' => 'Cliente creado correctamente',
                    'file' => [
                        'name' => basename($NombreArchivo),
                        'content' => base64_encode($ContenidoTxt)
                    ]
                ]);
                exit;
            }

        } catch (Exception $e) {
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al procesar: ' . $e->getMessage()]);
            exit;
        }
    }
}
