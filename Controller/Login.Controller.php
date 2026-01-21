<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include_once "Controller/SecurityController.php";
include_once "Model/LoginModel.php";

class LoginController
{
    private $model;

    public function __construct()
    {
        $this->model = new LoginModel();
    }

    private function iniciarSesionSegura()
    {
        $Https_activo = false;

        if (isset($_SERVER['HTTPS'])) {
            if ($_SERVER['HTTPS'] !== '' && $_SERVER['HTTPS'] !== 'off') {
                $Https_activo = true;
            }
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_name('XITICSESSID');
            session_set_cookie_params(
                array(
                    'lifetime' => 0,
                    'path' => '/',
                    'domain' => '',
                    'secure' => $Https_activo,
                    'httponly' => true,
                    'samesite' => 'Lax'
                )
            );
            session_start();
        }

        if (!isset($_SESSION['_csrf_token']) || $_SESSION['_csrf_token'] === '') {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }

        header('X-Frame-Options: DENY');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: same-origin');
    }

    public function index()
    {
        $this->iniciarSesionSegura();
        include_once "View/LoginView.php";
    }

    public function validar()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(array('success' => 0, 'mensaje' => 'Método no permitido'), JSON_UNESCAPED_UNICODE);
            return;
        }

        $this->iniciarSesionSegura();

        $Token_enviado = '';
        if (isset($_POST['csrf_token'])) {
            $Token_enviado = (string) $_POST['csrf_token'];
        }

        $Token_sesion = (string) $_SESSION['_csrf_token'];

        if ($Token_enviado === '' || hash_equals($Token_sesion, $Token_enviado) === false) {
            http_response_code(419);
            echo json_encode(array('success' => 0, 'mensaje' => 'Sesión inválida. Recarga e intenta de nuevo.'), JSON_UNESCAPED_UNICODE);
            return;
        }

        $Usuario_texto = '';
        if (isset($_POST['Usuario'])) {
            $Usuario_texto = trim((string) $_POST['Usuario']);
        }

        $Pass_texto = '';
        if (isset($_POST['pass'])) {
            $Pass_texto = (string) $_POST['pass'];
        }

        if ($Usuario_texto === '' || strlen($Usuario_texto) > 60) {
            echo json_encode(array('success' => 0, 'mensaje' => 'Credenciales inválidas.'), JSON_UNESCAPED_UNICODE);
            return;
        }

        if (!preg_match('/^[a-zA-Z0-9@._-]+$/', $Usuario_texto)) {
            echo json_encode(array('success' => 0, 'mensaje' => 'Credenciales inválidas.'), JSON_UNESCAPED_UNICODE);
            return;
        }

        if ($Pass_texto === '' || strlen($Pass_texto) > 72) {
            echo json_encode(array('success' => 0, 'mensaje' => 'Credenciales inválidas.'), JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $this->model->setUsuario($Usuario_texto);
            $this->model->setContrasenia($Pass_texto);
        } catch (Exception $e) {
            echo json_encode(array('success' => 0, 'mensaje' => 'Credenciales inválidas.'), JSON_UNESCAPED_UNICODE);
            return;
        }

        $Acceso_ok = $this->model->validar();

        if ($Acceso_ok === true) {

            $Info = $this->model->getUsuarioSesion();

            if (!$Info) {
                echo json_encode(array('success' => 0, 'mensaje' => 'Error interno.'), JSON_UNESCAPED_UNICODE);
                return;
            }

            session_regenerate_id(true);

            $_SESSION["_sesion_usuario"] = $Info["Usuario"];
            $_SESSION["_AdminID_user"] = $Info["id"];
            $_SESSION["_Nombre_Sesion"] = $Info["Nombre"];
            $_SESSION["_Apellidos"] = $Info["Apellidos"];
            
            $Cliente_id = 0;
            if (isset($Info["Cliente_id"])) {
                $Cliente_id = (int) $Info["Cliente_id"];
            }

            $_SESSION["_Cliente_id"] = $Cliente_id;


            $_SESSION["_Rol"] = $Info["Rol"];
            $_SESSION["_Perfil_user_general"] = $Info["Rol"];

            $_SESSION["_Es_AdminMaster"] = 0;
            $_SESSION["_Es_ClienteAdmin"] = 0;
            $_SESSION["_Es_Regional"] = 0;
            $_SESSION["_Es_Gerente"] = 0;

            if ($Info["Rol"] === 'AdminMaster') {
                $_SESSION["_Es_AdminMaster"] = 1;
            }

            if ($Info["Rol"] === 'ClienteAdmin') {
                $_SESSION["_Es_ClienteAdmin"] = 1;
            }

            if ($Info["Rol"] === 'Regional') {
                $_SESSION["_Es_Regional"] = 1;
            }

            if ($Info["Rol"] === 'Gerente') {
                $_SESSION["_Es_Gerente"] = 1;
            }

            echo json_encode(array(
                'success' => 1,
                'redirect' => 'index.php?System=Dashboard'
            ), JSON_UNESCAPED_UNICODE);

            return;
        }

        $Mensaje = $this->model->getLoginMotivo();
        if ($Mensaje === '') {
            $Mensaje = 'Credenciales inválidas.';
        }

        echo json_encode(array(
            'success' => 0,
            'mensaje' => $Mensaje
        ), JSON_UNESCAPED_UNICODE);
    }



        public function salir()
    {
        SecurityController::iniciarSesionSegura();

        if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?System=dashboard&a=index");
            exit;
        }

        $Token_enviado = '';
        if (isset($_POST['csrf_token'])) {
            $Token_enviado = (string) $_POST['csrf_token'];
        }

        if (SecurityController::validarCsrfPost($Token_enviado) === false) {
            header("Location: index.php?System=dashboard&a=index");
            exit;
        }

        $_SESSION = array();

        if (ini_get("session.use_cookies")) {
            $Params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $Params["path"],
                $Params["domain"],
                $Params["secure"],
                $Params["httponly"]
            );
        }

        session_destroy();

        header("Location: index.php?System=login");
        exit;
    }
}
