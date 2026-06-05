<?php

declare(strict_types=1);




// 2. Importación de las clases
use App\Controllers\AuthController;
use App\Controllers\Clientcontroller;
use App\Controllers\DashboardController;
use App\Controllers\ProjectController;
use App\Controllers\ReportController;
use App\Controllers\InvoiceController;
use App\Core\Router;

// 3. Inicio de sesión
session_start();

// 4. Carga del archivo de configuración
require dirname(__DIR__) . '/config/config.php';

// 5. Autoloader
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relative_class = substr($class, strlen($prefix));
    $path = dirname(__DIR__) . '/App/' . str_replace('\\', '/', $relative_class) . '.php';

    if (is_file($path)) {
        require $path;
    }
});

$router = new Router();

// Rutas de Autenticación
$router->get('/', AuthController::class, 'showLogin');
$router->get('/login', AuthController::class, 'showLogin');
$router->post('/login', AuthController::class, 'login');
$router->get('/registro', AuthController::class, 'showRegister');
$router->post('/registro', AuthController::class, 'register');
$router->get('/logout', AuthController::class, 'logout');

// Ruta del Dashboard
$router->get('/dashboard', DashboardController::class, 'index');

// Rutas de Proyectos
$router->get('/proyectos', ProjectController::class, 'index');
$router->get('/proyectos/nuevo', ProjectController::class, 'create');
$router->post('/proyectos/guardar', ProjectController::class, 'store');
$router->get('/proyectos/editar', ProjectController::class, 'edit');
$router->post('/proyectos/actualizar', ProjectController::class, 'update');

// Rutas de Clientes
$router->get('/clientes', Clientcontroller::class, 'index');
$router->get('/clientes/nuevo', Clientcontroller::class, 'create');
$router->post('/clientes/guardar', Clientcontroller::class, 'store');
$router->get('/clientes/editar', Clientcontroller::class, 'edit');
$router->post('/clientes/actualizar', Clientcontroller::class, 'update');

// Rutas de Eliminar


// Rutas de Reportes individuales
$router->get('/reportes/cliente',   ReportController::class, 'client');
$router->get('/reportes/proyecto',  ReportController::class, 'project');

// Rutas de Eliminar
$router->post('/clientes/eliminar', Clientcontroller::class, 'delete');
$router->post('/proyectos/eliminar', ProjectController::class, 'delete');

// Rutas de Reportes
$router->get('/reportes/clientes', ReportController::class, 'clients');
$router->get('/reportes/proyectos', ReportController::class, 'projects');

// Rutas de Facturas
// Rutas de Facturas
$router->get('/facturas', InvoiceController::class, 'index');
$router->get('/facturas/guardar', InvoiceController::class, 'store');  
$router->get('/facturas/pdf', InvoiceController::class, 'pdf');
$router->post('/facturas/eliminar', InvoiceController::class, 'delete');


//// DESPUÉS:
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$router->dispatch($method, $uri);