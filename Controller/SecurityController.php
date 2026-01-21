<?php
/**
 * Archivo: Controller/SecurityController.php
 *
 * SecurityController
 * ------------------
 * Centraliza lógica de seguridad compartida por todos los controladores.
 *
 * Incluye:
 * - Sesión segura con cookies seguras y headers
 * - Token CSRF (generar / obtener / validar)
 * - Restricción por rol (AdminMaster / ClienteAdmin)
 * - Exigir autenticación
 * - Obtener cliente_id del usuario (desde sesión o BD)
 *
 * Nota:
 * - Responde JSON (en vez de redirigir) si detecta X-Requested-With: fetch
 */

require_once 'Model/DataBase.php';

class SecurityController
{
    /**
     * Inicia la sesión con parámetros seguros (cookie flags y nombre fijo).
     * También crea el token CSRF si no existe.
     *
     * @return void
     */
    public static function iniciarSesionSegura()
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

    /**
     * Alias por compatibilidad con controladores que llaman exigirSesion().
     * Exige que exista autenticación.
     *
     * @return void
     */
    public function exigirSesion()
    {
        self::exigirAutenticado();
    }

    /**
     * Exige que el usuario esté autenticado (sesión iniciada).
     *
     * @return void
     */
    public static function exigirAutenticado()
    {
        self::iniciarSesionSegura();

        $Usuario_id = 0;
        if (isset($_SESSION["_AdminID_user"])) {
            $Usuario_id = (int) $_SESSION["_AdminID_user"];
        }

        if ($Usuario_id <= 0) {
            self::responderNoAutenticado();
        }
    }

    /**
     * Exige que el usuario sea AdminMaster.
     *
     * @return void
     */
    public static function exigirAdminMaster()
    {
        self::iniciarSesionSegura();

        $Es_admin = 0;
        if (isset($_SESSION["_Es_AdminMaster"])) {
            $Es_admin = (int) $_SESSION["_Es_AdminMaster"];
        }

        if ($Es_admin !== 1) {
            self::responderAccesoDenegado();
        }
    }

    /**
     * Exige rol ClienteAdmin (permite AdminMaster como excepción).
     * Se apoya en banderas ya definidas por tu LoginController.
     *
     * @return void
     */
    public static function exigirClienteAdmin()
    {
        self::iniciarSesionSegura();

        $Es_cliente_admin = 0;
        if (isset($_SESSION["_Es_ClienteAdmin"])) {
            $Es_cliente_admin = (int) $_SESSION["_Es_ClienteAdmin"];
        }

        $Es_admin_master = 0;
        if (isset($_SESSION["_Es_AdminMaster"])) {
            $Es_admin_master = (int) $_SESSION["_Es_AdminMaster"];
        }

        if ($Es_cliente_admin === 1) {
            return;
        }

        if ($Es_admin_master === 1) {
            return;
        }

        self::responderAccesoDenegado();
    }

    /**
     * Exige un rol exacto comparando contra $_SESSION["_Rol"].
     *
     * @param string $Rol_requerido
     * @return void
     */
    public static function exigirRolExacto($Rol_requerido)
    {
        self::iniciarSesionSegura();

        $Rol_actual = '';
        if (isset($_SESSION["_Rol"])) {
            $Rol_actual = (string) $_SESSION["_Rol"];
        }

        if ((string) $Rol_actual !== (string) $Rol_requerido) {
            self::responderAccesoDenegado();
        }
    }

    /**
     * Obtiene el token CSRF actual.
     *
     * @return string
     */
    public static function obtenerCsrfToken()
    {
        self::iniciarSesionSegura();
        return (string) $_SESSION['_csrf_token'];
    }

    /**
     * Alias para controladores que llaman obtenerTokenCsrf().
     *
     * @return string
     */
    public function obtenerTokenCsrf()
    {
        return self::obtenerCsrfToken();
    }

    /**
     * Valida un token CSRF enviado.
     *
     * @param string $Token_enviado
     * @return bool
     */
    public static function validarCsrfPost($Token_enviado)
    {
        self::iniciarSesionSegura();

        $Token_enviado = (string) $Token_enviado;
        if ($Token_enviado === '') {
            return false;
        }

        $Token_sesion = (string) $_SESSION['_csrf_token'];
        if ($Token_sesion === '') {
            return false;
        }

        if (hash_equals($Token_sesion, $Token_enviado) === false) {
            return false;
        }

        return true;
    }

    /**
     * Valida CSRF leyendo directamente desde $_POST['csrf_token'].
     * Útil para fetch POST.
     *
     * @return bool
     */
    public function validarCsrfDesdePost()
    {
        $Token_enviado = '';
        if (isset($_POST['csrf_token'])) {
            $Token_enviado = (string) $_POST['csrf_token'];
        }

        return self::validarCsrfPost($Token_enviado);
    }

    /**
     * Obtiene el cliente_id del usuario actual.
     * - Usa $_SESSION['_Cliente_id'] si ya está cacheado
     * - Si no, consulta en BD con usuarios.id = $_SESSION["_AdminID_user"]
     *
     * @return int
     */
    public function obtenerClienteIdSesion()
    {
        self::iniciarSesionSegura();

        if (isset($_SESSION['_Cliente_id'])) {
            $Cliente_cache = (int) $_SESSION['_Cliente_id'];
            if ($Cliente_cache > 0) {
                return (int) $Cliente_cache;
            }
        }

        $Usuario_id = 0;
        if (isset($_SESSION["_AdminID_user"])) {
            $Usuario_id = (int) $_SESSION["_AdminID_user"];
        }

        if ($Usuario_id <= 0) {
            self::responderNoAutenticado();
        }

        $Db = DataBase::conectar();
        if ($Db === null) {
            self::responderErrorServidor('No hay conexión a base de datos.');
        }

        $Sql = "SELECT cliente_id
                FROM usuarios
                WHERE id = :usuario_id
                LIMIT 1";

        $Stmt = $Db->prepare($Sql);
        $Stmt->bindValue(':usuario_id', $Usuario_id, PDO::PARAM_INT);
        $Stmt->execute();

        $Fila = $Stmt->fetch(PDO::FETCH_ASSOC);

        if (!$Fila) {
            self::responderAccesoDenegado();
        }

        if (!isset($Fila['cliente_id'])) {
            self::responderAccesoDenegado();
        }

        if ($Fila['cliente_id'] === null) {
            self::responderAccesoDenegado();
        }

        $Cliente_id = (int) $Fila['cliente_id'];

        if ($Cliente_id <= 0) {
            self::responderAccesoDenegado();
        }

        $_SESSION['_Cliente_id'] = (int) $Cliente_id;

        return (int) $Cliente_id;
    }

    /**
     * Determina si la petición espera respuesta JSON (fetch/ajax).
     *
     * @return bool
     */
    protected static function esPeticionJson()
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            $Valor = strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']);
            if ($Valor === 'fetch' || $Valor === 'xmlhttprequest') {
                return true;
            }
        }

        return false;
    }

    /**
     * Respuesta estandarizada cuando no hay sesión.
     *
     * @return void
     */
    protected static function responderNoAutenticado()
    {
        if (self::esPeticionJson() === true) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('success' => 0, 'mensaje' => 'Sesión no válida. Inicia sesión nuevamente.'), JSON_UNESCAPED_UNICODE);
            exit;
        }

        header('Location: index.php?System=login');
        exit;
    }

    /**
     * Respuesta estandarizada cuando no tiene permisos.
     *
     * @return void
     */
    protected static function responderAccesoDenegado()
    {
        if (self::esPeticionJson() === true) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('success' => 0, 'mensaje' => 'Acceso denegado.'), JSON_UNESCAPED_UNICODE);
            exit;
        }

        header('Location: index.php?System=login');
        exit;
    }

    /**
     * Respuesta estandarizada para errores internos.
     *
     * @param string $Mensaje
     * @return void
     */
    protected static function responderErrorServidor($Mensaje)
    {
        if (self::esPeticionJson() === true) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('success' => 0, 'mensaje' => (string) $Mensaje), JSON_UNESCAPED_UNICODE);
            exit;
        }

        http_response_code(500);
        echo htmlspecialchars((string) $Mensaje);
        exit;
    }
}
