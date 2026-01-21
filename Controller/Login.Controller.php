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
        // 1. Silenciar errores visuales para no romper el JSON
        ini_set('display_errors', 0);
        error_reporting(0);
        
        // 2. Iniciar buffer para capturar cualquier salida inesperada
        ob_start();

        header('Content-Type: application/json; charset=utf-8');

        if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            ob_end_clean(); // Limpiar
            http_response_code(405);
            echo json_encode(array('success' => 0, 'mensaje' => 'Método no permitido'), JSON_UNESCAPED_UNICODE);
            exit;
        }

        $this->iniciarSesionSegura();

        $Token_enviado = '';
        if (isset($_POST['csrf_token'])) {
            $Token_enviado = (string) $_POST['csrf_token'];
        }

        $Token_sesion = (string) $_SESSION['_csrf_token'];

        if ($Token_enviado === '' || hash_equals($Token_sesion, $Token_enviado) === false) {
            ob_end_clean();
            http_response_code(419);
            echo json_encode(array('success' => 0, 'mensaje' => 'Sesión inválida. Recarga e intenta de nuevo.'), JSON_UNESCAPED_UNICODE);
            exit;
        }

        $Email_texto = '';
        if (isset($_POST['email'])) {
            $Email_texto = trim((string) $_POST['email']);
        }

        $Pass_texto = '';
        if (isset($_POST['pass'])) {
            $Pass_texto = (string) $_POST['pass'];
        }

        if ($Email_texto === '' || strlen($Email_texto) > 100) {
            ob_end_clean();
            echo json_encode(array('success' => 0, 'mensaje' => 'Credenciales inválidas.'), JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (!filter_var($Email_texto, FILTER_VALIDATE_EMAIL)) {
            ob_end_clean();
            echo json_encode(array('success' => 0, 'mensaje' => 'Formato de correo inválido.'), JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($Pass_texto === '' || strlen($Pass_texto) > 72) {
            ob_end_clean();
            echo json_encode(array('success' => 0, 'mensaje' => 'Contraseña inválida.'), JSON_UNESCAPED_UNICODE);
            exit;
        }

        try {
            $this->model->setEmail($Email_texto);
            $this->model->setContrasenia($Pass_texto);
        } catch (Exception $e) {
            ob_end_clean();
            echo json_encode(array('success' => 0, 'mensaje' => 'Datos inválidos.'), JSON_UNESCAPED_UNICODE);
            exit;
        }

        $Acceso_ok = $this->model->validar();

        if ($Acceso_ok === true) {

            $Info = $this->model->getUsuarioSesion();

            if (!$Info) {
                ob_end_clean();
                echo json_encode(array('success' => 0, 'mensaje' => 'Error al obtener sesión.'), JSON_UNESCAPED_UNICODE);
                exit;
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

            $_SESSION["_Es_AdminMaster"] = ($Info["Rol"] === 'AdminMaster') ? 1 : 0;
            $_SESSION["_Es_ClienteAdmin"] = ($Info["Rol"] === 'ClienteAdmin') ? 1 : 0;
            $_SESSION["_Es_Regional"] = ($Info["Rol"] === 'Regional') ? 1 : 0;
            $_SESSION["_Es_Gerente"] = ($Info["Rol"] === 'Gerente') ? 1 : 0;

            // Limpieza final y envío de éxito
            ob_end_clean();
            echo json_encode(array(
                'success' => 1,
                'redirect' => 'index.php?System=Dashboard'
            ), JSON_UNESCAPED_UNICODE);
            exit;
        }

        $Mensaje = $this->model->getLoginMotivo();
        if ($Mensaje === '') {
            $Mensaje = 'Credenciales inválidas.';
        }

        ob_end_clean();
        echo json_encode(array(
            'success' => 0,
            'mensaje' => $Mensaje
        ), JSON_UNESCAPED_UNICODE);
        exit;
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
