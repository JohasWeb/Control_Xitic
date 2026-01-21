<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('XITICSESSID');
    session_start();
}

/* ===== Strings ===== */
$Nombre = '';
if (isset($_SESSION["_Nombre_Sesion"])) { $Nombre = (string) $_SESSION["_Nombre_Sesion"]; }

$Apellidos = '';
if (isset($_SESSION["_Apellidos"])) { $Apellidos = (string) $_SESSION["_Apellidos"]; }

$Usuario = '';
if (isset($_SESSION["_sesion_usuario"])) { $Usuario = (string) $_SESSION["_sesion_usuario"]; }

$Rol = '';
if (isset($_SESSION["_Rol"])) { $Rol = (string) $_SESSION["_Rol"]; }

$Perfil_user_general = '';
if (isset($_SESSION["_Perfil_user_general"])) { $Perfil_user_general = (string) $_SESSION["_Perfil_user_general"]; }

$Csrf_token = '';
if (isset($_SESSION["_csrf_token"])) { $Csrf_token = (string) $_SESSION["_csrf_token"]; }

/* ===== Ints ===== */
$AdminID_user = 0;
if (isset($_SESSION["_AdminID_user"])) { $AdminID_user = (int) $_SESSION["_AdminID_user"]; }

$Cliente_id = 0;
if (isset($_SESSION["_Cliente_id"])) { $Cliente_id = (int) $_SESSION["_Cliente_id"]; }

$Es_AdminMaster = 0;
if (isset($_SESSION["_Es_AdminMaster"])) { $Es_AdminMaster = (int) $_SESSION["_Es_AdminMaster"]; }

$Es_ClienteAdmin = 0;
if (isset($_SESSION["_Es_ClienteAdmin"])) { $Es_ClienteAdmin = (int) $_SESSION["_Es_ClienteAdmin"]; }

$Es_Regional = 0;
if (isset($_SESSION["_Es_Regional"])) { $Es_Regional = (int) $_SESSION["_Es_Regional"]; }

$Es_Gerente = 0;
if (isset($_SESSION["_Es_Gerente"])) { $Es_Gerente = (int) $_SESSION["_Es_Gerente"]; }

/* ===== Ejemplos de uso (comparación) =====
if ($Usuario === '') { header('Location: index.php?System=Login'); exit; }

if ($Rol === 'AdminMaster') { // o if ($Es_AdminMaster === 1)
    // mostrar menú master
}

if ($Es_ClienteAdmin === 1) {
    // permisos de cliente admin
}

if ($Cliente_id > 0) {
    // filtrar info por cliente
}
*/
