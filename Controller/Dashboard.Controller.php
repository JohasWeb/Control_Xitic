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
        
        // Datos globales para AdminMaster
        $StatsAdmin = [];
        if (isset($_SESSION['_Es_AdminMaster']) && $_SESSION['_Es_AdminMaster'] === 1) {
            include_once "Model/ClientesModel.php";
            include_once "Model/EncuestasModel.php";
            
            $CliModel = new ClientesModel();
            $EncModel = new EncuestasModel();
            
            $StatsAdmin['clientes_activos'] = $CliModel->obtenerTotalClientesActivos();
            $StatsAdmin['encuestas_activas'] = $EncModel->obtenerTotalEncuestasActivas();
            $StatsAdmin['respuestas_totales'] = $EncModel->obtenerTotalRespuestasGlobal();
        }

        // Incluir la vista
        include_once "View/Dashboard/index.php";
    }
}
