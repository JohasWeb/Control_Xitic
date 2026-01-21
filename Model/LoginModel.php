<?php
include_once "DataBase.php";

class LoginModel
{
    private $pdo;

    private $usuario;
    private $contrasenia;

    private $login_motivo;
    private $login_bloqueo_activo;

    private $usuario_sesion;

    public function __construct()
    {
        $this->pdo = DataBase::conectar();
        $this->login_motivo = '';
        $this->login_bloqueo_activo = 0;
        $this->usuario_sesion = null;
    }

    // SET (permitidos)
    public function setUsuario($Usuario)
    {
        $Usuario = trim((string) $Usuario);
        $Usuario = htmlspecialchars($Usuario, ENT_QUOTES, 'UTF-8');

        if ($Usuario === '' || strlen($Usuario) > 60) {
            throw new InvalidArgumentException("Usuario inválido");
        }

        $this->usuario = $Usuario;
    }

    public function setContrasenia($Contrasenia)
    {
        $Contrasenia = (string) $Contrasenia;
        $Contrasenia = htmlspecialchars($Contrasenia, ENT_QUOTES, 'UTF-8');

        if ($Contrasenia === '' || strlen($Contrasenia) > 72) {
            throw new InvalidArgumentException("Contraseña inválida");
        }

        $this->contrasenia = $Contrasenia;
    }

    // GET (permitidos)
    public function getLoginMotivo()
    {
        return $this->login_motivo;
    }

    public function getLoginBloqueoActivo()
    {
        return $this->login_bloqueo_activo;
    }

    public function getUsuarioSesion()
    {
        return $this->usuario_sesion;
    }

    private function obtenerInfoRequest()
    {
        $Ip_remota = '';
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $Ip_remota = (string) $_SERVER['REMOTE_ADDR'];
        }

        $Ip_reenviada = '';
        if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $Ip_reenviada = (string) $_SERVER['HTTP_CF_CONNECTING_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $Ip_reenviada = (string) $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        $User_agent = '';
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $User_agent = (string) $_SERVER['HTTP_USER_AGENT'];
        }
        $User_agent = substr($User_agent, 0, 1000);

        $Host = '';
        if (isset($_SERVER['HTTP_HOST'])) {
            $Host = (string) $_SERVER['HTTP_HOST'];
        }

        $Uri = '';
        if (isset($_SERVER['REQUEST_URI'])) {
            $Uri = (string) $_SERVER['REQUEST_URI'];
        }

        $Metodo = '';
        if (isset($_SERVER['REQUEST_METHOD'])) {
            $Metodo = (string) $_SERVER['REQUEST_METHOD'];
        }

        $Protocolo = '';
        if (isset($_SERVER['SERVER_PROTOCOL'])) {
            $Protocolo = (string) $_SERVER['SERVER_PROTOCOL'];
        }

        $Https = 0;
        if (isset($_SERVER['HTTPS'])) {
            if ($_SERVER['HTTPS'] !== '' && $_SERVER['HTTPS'] !== 'off') {
                $Https = 1;
            }
        }

        $Puerto = null;
        if (isset($_SERVER['SERVER_PORT'])) {
            $Puerto = (int) $_SERVER['SERVER_PORT'];
        }

        $Sesion_php = '';
        if (function_exists('session_id')) {
            $Sesion_php = (string) session_id();
        }

        $Huella_sha256 = hash('sha256', $Ip_remota . '|' . $User_agent . '|' . $Host);

        return array(
            'ip_remota' => $Ip_remota,
            'ip_reenviada' => $Ip_reenviada,
            'user_agent' => $User_agent,
            'host' => $Host,
            'uri' => $Uri,
            'metodo' => $Metodo,
            'protocolo' => $Protocolo,
            'https' => $Https,
            'puerto' => $Puerto,
            'sesion_php' => $Sesion_php,
            'huella_sha256' => $Huella_sha256
        );
    }

    private function bloqueoVigente($Usuario_texto, $Ip_remota)
    {
        $Sql = "SELECT fecha_bloqueo
                FROM sesiones
                WHERE usuario_texto = BINARY ?
                  AND ip_remota = ?
                  AND bloqueo_activo = 1
                  AND fecha_bloqueo IS NOT NULL
                ORDER BY fecha_registro DESC
                LIMIT 1";

        $Stmt = $this->pdo->prepare($Sql);
        $Stmt->execute(array($Usuario_texto, $Ip_remota));
        $Info = $Stmt->fetch(PDO::FETCH_ASSOC);

        if (!$Info) {
            return false;
        }

        $Fecha_bloqueo = (string) $Info['fecha_bloqueo'];
        if ($Fecha_bloqueo === '') {
            return false;
        }

        $Sql_vigente = "SELECT (NOW() < (TIMESTAMP(?) + INTERVAL 15 MINUTE)) AS vigente";
        $Stmt2 = $this->pdo->prepare($Sql_vigente);
        $Stmt2->execute(array($Fecha_bloqueo));
        $Row = $Stmt2->fetch(PDO::FETCH_ASSOC);

        if ($Row && isset($Row['vigente'])) {
            if ((int) $Row['vigente'] === 1) {
                return true;
            }
        }

        return false;
    }

    private function obtenerUltimoIntento($Usuario_texto, $Ip_remota)
    {
        $Sql = "SELECT intentos_fallidos_consecutivos, fecha_registro
                FROM sesiones
                WHERE usuario_texto = BINARY ?
                  AND ip_remota = ?
                ORDER BY fecha_registro DESC
                LIMIT 1";

        $Stmt = $this->pdo->prepare($Sql);
        $Stmt->execute(array($Usuario_texto, $Ip_remota));
        $Info = $Stmt->fetch(PDO::FETCH_ASSOC);

        if (!$Info) {
            return array('intentos' => 0, 'fecha' => null);
        }

        $Intentos = 0;
        if (isset($Info['intentos_fallidos_consecutivos'])) {
            $Intentos = (int) $Info['intentos_fallidos_consecutivos'];
        }

        $Fecha = null;
        if (isset($Info['fecha_registro'])) {
            $Fecha = $Info['fecha_registro'];
        }

        return array('intentos' => $Intentos, 'fecha' => $Fecha);
    }

    private function registrarEvento($Usuario_id, $Usuario_texto, $Tipo_evento, $Exito, $Motivo, $Intentos, $Bloqueo_activo, $Bloqueo_motivo)
    {
        $Req = $this->obtenerInfoRequest();

        $Fecha_bloqueo = null;
        if ((int) $Bloqueo_activo === 1) {
            $Fecha_bloqueo = date('Y-m-d H:i:s');
        }

        $Sql = "INSERT INTO sesiones
                (usuario_id, usuario_texto, tipo_evento, exito_login, motivo,
                 intentos_fallidos_consecutivos, bloqueo_activo, fecha_bloqueo, bloqueo_motivo,
                 ip_remota, ip_reenviada, user_agent, host, uri, metodo, protocolo, https, puerto,
                 sesion_php, huella_sha256)
                VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $Stmt = $this->pdo->prepare($Sql);

        $Stmt->execute(array(
            $Usuario_id,
            $Usuario_texto,
            $Tipo_evento,
            $Exito,
            $Motivo,
            $Intentos,
            $Bloqueo_activo,
            $Fecha_bloqueo,
            $Bloqueo_motivo,
            $Req['ip_remota'],
            $Req['ip_reenviada'],
            $Req['user_agent'],
            $Req['host'],
            $Req['uri'],
            $Req['metodo'],
            $Req['protocolo'],
            $Req['https'],
            $Req['puerto'],
            $Req['sesion_php'],
            $Req['huella_sha256']
        ));

        return true;
    }

    private function actualizarUltimoAcceso($Usuario_id)
    {
        $Sql = "UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?";
        $Stmt = $this->pdo->prepare($Sql);
        $Stmt->execute(array($Usuario_id));
        return true;
    }

    // Retorna true/false, y deja:
    // - $this->usuario_sesion (array) si éxito
    // - $this->login_motivo / $this->login_bloqueo_activo si falla
    public function validar()
    {
        $this->login_motivo = '';
        $this->login_bloqueo_activo = 0;
        $this->usuario_sesion = null;

        $Usuario_texto = (string) $this->usuario;
        $Contrasenia_texto = (string) $this->contrasenia;

        $Req = $this->obtenerInfoRequest();
        $Ip_remota = (string) $Req['ip_remota'];

        if ($this->bloqueoVigente($Usuario_texto, $Ip_remota) === true) {
            $this->login_motivo = 'Demasiados intentos. Intenta más tarde.';
            $this->login_bloqueo_activo = 1;

            $this->registrarEvento(null, $Usuario_texto, 'LOGIN', 0, 'Bloqueo vigente', 0, 1, 'Bloqueo vigente');
            return false;
        }

        $Sql_user = "SELECT id, usuario, cliente_id, rol, nombre, apellido, contrasena_hash, estado
                    FROM usuarios
                    WHERE usuario = BINARY ?
                    LIMIT 1";

        $Stmt = $this->pdo->prepare($Sql_user);
        $Stmt->execute(array($Usuario_texto));
        $Info_user = $Stmt->fetch(PDO::FETCH_ASSOC);

        // Hash falso para evitar enumeración por tiempo
        $Hash_falso = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

        $Usuario_id = null;
        $Hash_db = $Hash_falso;
        $Estado = '';
        $Motivo_interno = '';

        if ($Info_user) {
            $Usuario_id = (int) $Info_user['id'];
            $Hash_db = (string) $Info_user['contrasena_hash'];
            $Estado = (string) $Info_user['estado'];
        }

        $Password_ok = password_verify($Contrasenia_texto, $Hash_db);

        if (!$Info_user) {
            $Motivo_interno = 'Usuario no encontrado';
            $Password_ok = false;
        } else {
            if ($Estado !== 'Activo') {
                $Motivo_interno = 'Usuario no activo: ' . $Estado;
                $Password_ok = false;
            } else {
                if ($Password_ok === true) {
                    $Motivo_interno = 'Login correcto';
                } else {
                    $Motivo_interno = 'Contraseña incorrecta';
                }
            }
        }

        $Ultimo = $this->obtenerUltimoIntento($Usuario_texto, $Ip_remota);

        $Intentos_actual = 0;
        if (isset($Ultimo['intentos'])) {
            $Intentos_actual = (int) $Ultimo['intentos'];
        }

        $Fecha_ultimo = null;
        if (isset($Ultimo['fecha'])) {
            $Fecha_ultimo = $Ultimo['fecha'];
        }

        $Es_consecutivo = false;
        if ($Fecha_ultimo !== null) {
            $Sql_reciente = "SELECT (TIMESTAMP(?) >= (NOW() - INTERVAL 10 MINUTE)) AS reciente";
            $StmtR = $this->pdo->prepare($Sql_reciente);
            $StmtR->execute(array($Fecha_ultimo));
            $RowR = $StmtR->fetch(PDO::FETCH_ASSOC);

            if ($RowR && isset($RowR['reciente'])) {
                if ((int) $RowR['reciente'] === 1) {
                    $Es_consecutivo = true;
                }
            }
        }

        if ($Password_ok === true) {

            $this->registrarEvento($Usuario_id, $Usuario_texto, 'LOGIN', 1, $Motivo_interno, 0, 0, null);
            $this->actualizarUltimoAcceso($Usuario_id);

            $this->usuario_sesion = array(
                'id' => (int) $Info_user['id'],
                'Usuario' => (string) $Info_user['usuario'],
                'Rol' => (string) $Info_user['rol'],
                'Nombre' => (string) $Info_user['nombre'],
                'Apellidos' => (string) $Info_user['apellido']
            );

            return true;
        }

        $Intentos_nuevo = 1;
        if ($Es_consecutivo === true) {
            $Intentos_nuevo = $Intentos_actual + 1;
        }

        if ($Intentos_nuevo >= 5) {
            $Motivo_bloqueo = 'Demasiados intentos fallidos (>=5 en 10 min)';

            $this->login_motivo = 'Demasiados intentos. Intenta más tarde.';
            $this->login_bloqueo_activo = 1;

            $this->registrarEvento($Usuario_id, $Usuario_texto, 'LOGIN', 0, $Motivo_interno, $Intentos_nuevo, 1, $Motivo_bloqueo);
            $this->registrarEvento($Usuario_id, $Usuario_texto, 'BLOQUEO', 0, 'Bloqueo por intentos', $Intentos_nuevo, 1, $Motivo_bloqueo);

            return false;
        }

        $this->registrarEvento($Usuario_id, $Usuario_texto, 'LOGIN', 0, $Motivo_interno, $Intentos_nuevo, 0, null);

        $this->login_motivo = 'Credenciales inválidas.';
        $this->login_bloqueo_activo = 0;

        return false;
    }
}
