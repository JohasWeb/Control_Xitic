<?php
// Model/UsuariosModel.php
include_once "DataBase.php";

class UsuariosModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = DataBase::conectar();
    }

    /**
     * Genera una contraseña aleatoria y segura
     */
    public function generarPassword($longitud = 12)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*';
        $password = '';
        for ($i = 0; $i < $longitud; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $password;
    }

    /**
     * Crea un nuevo usuario en la base de datos
     */
    public function crearUsuario($ClienteId, $Email, $Password, $Nombre, $Apellido, $Rol = 'ClienteAdmin')
    {
        try {
            // Verificar si el email ya existe
            $stmt = $this->pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->execute([$Email]);
            if ($stmt->fetch()) {
                throw new Exception("El correo $Email ya está registrado.");
            }

            // Hash del password
            $Hash = password_hash($Password, PASSWORD_BCRYPT, ['cost' => 12]);

            $Sql = "INSERT INTO usuarios (cliente_id, email, contrasena_hash, nombre, apellido, rol, estado) 
                    VALUES (?, ?, ?, ?, ?, ?, 'Activo')";
            
            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->execute([
                $ClienteId,
                $Email,
                $Hash,
                $Nombre,
                $Apellido,
                $Rol
            ]);

            return $this->pdo->lastInsertId();

        } catch (PDOException $e) {
            throw new Exception("Error BD: " . $e->getMessage());
        }
    }

    /**
     * Obtiene los usuarios activos de un cliente (para selectores)
     */
    public function obtenerPorCliente($ClienteId)
    {
        try {
            $Sql = "SELECT id, nombre, apellido, email, rol FROM usuarios WHERE cliente_id = ? AND estado = 'Activo' ORDER BY nombre ASC";
            $Stmt = $this->pdo->prepare($Sql);
            $Stmt->execute([$ClienteId]);
            return $Stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function buscarPorEmail($Email)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE email = ? LIMIT 1");
            $stmt->execute([$Email]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }
}
