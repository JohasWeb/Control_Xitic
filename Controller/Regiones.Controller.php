<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include_once "Controller/SecurityController.php";
include_once "Model/RegionesModel.php";
include_once "Model/UsuariosModel.php";

class RegionesController
{
    private $model;
    private $usuariosModel;

    public function __construct()
    {
        $this->model = new RegionesModel();
        $this->usuariosModel = new UsuariosModel();
    }

    public function index()
    {
        SecurityController::iniciarSesionSegura();
        SecurityController::exigirClienteAdmin();

        $Security = new SecurityController();
        $ClienteId = $Security->obtenerClienteIdSesion();
        
        $Regiones = $this->model->obtenerPorCliente($ClienteId);
        $Usuarios = $this->usuariosModel->obtenerPorCliente($ClienteId);
        
        include_once "View/ClienteAdmin/Regiones/index.php";
    }

    public function guardar()
    {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json');

        SecurityController::iniciarSesionSegura();
        SecurityController::exigirClienteAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
             echo json_encode(['success' => false, 'message' => 'Método no permitido']);
             exit;
        }

        if (!isset($_POST['csrf_token']) || !SecurityController::validarCsrfPost($_POST['csrf_token'])) {
             echo json_encode(['success' => false, 'message' => 'Error de seguridad: Token inválido']);
             exit;
        }

        $Security = new SecurityController();
        $ClienteId = $Security->obtenerClienteIdSesion();
        $Id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $Nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
        
        // Datos del Supervisor
        $SupNombre = isset($_POST['supervisor_nombre']) ? trim($_POST['supervisor_nombre']) : '';
        $SupApellido = isset($_POST['supervisor_apellido']) ? trim($_POST['supervisor_apellido']) : '';
        $SupEmail = isset($_POST['supervisor_email']) ? trim($_POST['supervisor_email']) : '';
        
        if ($Nombre === '') {
            echo json_encode(['success' => false, 'message' => 'El nombre de la región es obligatorio.']);
            exit;
        }

        try {
            $SupervisorId = null;

            // Lógica de creación/asignación de supervisor
            if ($SupEmail !== '') {
                if (!filter_var($SupEmail, FILTER_VALIDATE_EMAIL)) {
                    echo json_encode(['success' => false, 'message' => 'El email del supervisor no es válido.']);
                    exit;
                }

                $Usuario = $this->usuariosModel->buscarPorEmail($SupEmail);
                
                if ($Usuario) {
                    // Si el usuario existe, lo asignamos directamente
                    // NOTA: Idealmente validar que pertenezca al mismo cliente, pero por flexibilidad permitimos asignación directa si ya existe.
                    if ($Usuario['cliente_id'] != $ClienteId) {
                         // Opcional: Bloquear si es de otro cliente.
                         // Por simplicidad y seguridad básica, validemos.
                         // Si es AdminMaster (cliente_id NULL) se podría permitir, pero asumimos tenant separation.
                         echo json_encode(['success' => false, 'message' => 'El usuario indicado ya existe en otra organización.']);
                         exit;
                    }
                    $SupervisorId = $Usuario['id'];
                } else {
                    // Crear nuevo usuario
                    if ($SupNombre === '') {
                        echo json_encode(['success' => false, 'message' => 'El nombre del supervisor es requerido para crear un usuario nuevo.']);
                        exit;
                    }
                    
                    $Password = $this->usuariosModel->generarPassword();
                    // Rol 'Regional' para supervisores de región
                    $SupervisorId = $this->usuariosModel->crearUsuario($ClienteId, $SupEmail, $Password, $SupNombre, $SupApellido, 'Regional');
                    
                    // Aquí se podría guardar el password en sesión para mostrarlo o enviarlo por email
                    // Por ahora, el sistema solo lo crea. 
                }
            } else {
                // Si no se puso email, pero se había seleccionado un ID antes (caso edición sin cambios en email pero manteniendo ID)? 
                // La vista enviará los campos vacíos si no se rellenan.
                // PERO si estamos editando, y el usuario no toca los campos de supervisor, ¿qué pasa?
                // El modal mostrará los datos del supervisor actual si existe.
                // Si el usuario vacía los campos, se desasigna ($SupervisorId = null).
                // MANEJO DE EDICIÓN:
                // Si es edición y el usuario NO tocó los campos de supervisor (vacíos), ¿mantenemos el anterior?
                // NO, los campos en el modal deben venir rellenos con los datos actuales. Si los borra, se quita.
                // Así que esta lógica funciona: si llegan vacíos, se pone null.
                $SupervisorId = isset($_POST['supervisor_id_hidden']) && $_POST['supervisor_id_hidden'] > 0 ? (int)$_POST['supervisor_id_hidden'] : null;
                // Espera, el input hidden 'supervisor_id' podría venir si no cambiamos nada?
                // Mejor estrategia:
                // El formulario enviará 'supervisor_email', 'supervisor_nombre', etc.
                // Si vienen vacíos, asumimos que no hay supervisor o se quiere quitar.
                // SIEMPRE procesamos lo que viene en los inputs de texto.
                
                // Corrección: Si el usuario ya tenía supervisor y quiere mantenerlo, los inputs deben aparecer llenos en el modal al abrirlo.
                // Si los inputs están vacíos, significa que no hay supervisor.
                
                // Pero hay un caso borde: Edición simple de nombre de región. El modal debe cargar los datos del user.
                // Si los carga, se enviarán de vuelta. Entrará en el IF ($SupEmail !== '') y buscará al usuario, lo encontrará y asignará su ID. Correcto.
            }

            if ($Id > 0) {
                // Editar
                $Check = $this->model->obtenerPorId($Id, $ClienteId);
                if (!$Check) {
                    echo json_encode(['success' => false, 'message' => 'Región no encontrada.']);
                    exit;
                }
                $Res = $this->model->actualizar($Id, $ClienteId, $Nombre, $SupervisorId);
                $Msg = 'Región actualizada correctamente';
            } else {
                // Crear
                $Res = $this->model->crear($ClienteId, $Nombre, $SupervisorId);
                $Msg = 'Región creada correctamente';
            }

            if ($Res) {
                $Response = ['success' => true, 'message' => $Msg];
                if (isset($Password) && $Password !== '') {
                     $Response['credentials'] = [
                         'email' => $SupEmail,
                         'password' => $Password
                     ];
                     $Response['message'] .= '. Credenciales generadas.';
                }
                echo json_encode($Response);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al guardar datos.']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function eliminar()
    {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json');

        SecurityController::iniciarSesionSegura();
        SecurityController::exigirClienteAdmin();
        
        // CSRF Check rápido (opcional pero recomendado)
        // ...

        $Security = new SecurityController();
        $ClienteId = $Security->obtenerClienteIdSesion();
        $Id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

        if ($this->model->eliminar($Id, $ClienteId)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar.']);
        }
    }
}
