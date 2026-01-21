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
            // Log del error real internamente si fuera necesario
            throw new Exception("Error BD: " . $e->getMessage());
        }
    }
}
