<?php
class DataBase
{
    public static function conectar()
    {
        try {
            //utf8mb4

            $pdo = new PDO('mysql:host=localhost;dbname=appxitic_controlmaster;charset=utf8', 'root', '');

            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
             
        } catch (Exception $e) {
            return null;
           
            echo $e->getMessage();
            return null;
 
            exit();
        }
    }

 

 
}


