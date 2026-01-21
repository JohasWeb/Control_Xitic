<?php
include_once "Controller/SecurityController.php";
include_once "Model/ClientesModel.php";

class ClientesController
{
    private $model;

    public function __construct()
    {
        $this->model = new ClientesModel();
    }

    public function index()
    {
        SecurityController::exigirAdminMaster(); // Solo AdminMaster ve esto
        
        $Clientes = $this->model->listarClientes();
        include_once "View/Clientes/index.php";
    }

    public function crear()
    {
        SecurityController::exigirAdminMaster();
        include_once "View/Clientes/crear.php";
    }

    public function editar()
    {
        SecurityController::exigirAdminMaster();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $Cliente = $this->model->obtenerCliente($id);

        if (!$Cliente) {
            header("Location: index.php?System=clientes");
            exit;
        }

        include_once "View/Clientes/editar.php"; // Reutilizaremos crear.php con lógica o archivo separado
    }

    public function guardar()
    {
        SecurityController::exigirAdminMaster();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validar CSRF
            if (!SecurityController::validarCsrfPost($_POST['csrf_token'])) {
                die("Error de seguridad: Token inválido");
            }

            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $nombre = $_POST['nombre_comercial'];
            $razon = $_POST['razon_social'];
            $rfc = $_POST['rfc'];

            if ($id > 0) {
                // Editar
                $this->model->actualizarCliente($id, $nombre, $razon, $rfc);
            } else {
                // Crear
                $this->model->crearCliente($nombre, $razon, $rfc);
            }

            header("Location: index.php?System=clientes");
            exit;
        }
    }
}
