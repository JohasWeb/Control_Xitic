<?php
include_once "Controller/SecurityController.php";

class DashboardController
{
    public function index()
    {
        // Verificar que el usuario esté logueado
        SecurityController::exigirAutenticado();

        // Podríamos redirigir según rol si fuera necesario, 
        // pero por ahora todos van al mismo dashboard general
        // y la vista decide qué mostrar.
        
        // Incluir la vista
        include_once "View/Dashboard/index.php";
    }
}
