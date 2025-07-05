<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Mobile\BalnearioController;
use App\Http\Controllers\Mobile\BrazaleteController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BalnearioAdminController;
use App\Http\Controllers\SuperAdmin\UserController;
use App\Http\Controllers\SuperAdmin\BalnearioSuperAdminController;
use App\Http\Controllers\SuperAdmin\EventoController;
use App\Http\Controllers\SuperAdmin\SuperAdminReportesController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Autenticación pública
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Ruta de prueba de conexión
Route::get('/test', function () {
    return response()->json([
        'message' => 'Conexion exitosa con la API',
        'status' => 'operational',
        'version' => '1.0.0'
    ]);
});

//Rutas de Registros
Route::apiResource('users', UserCollection::class);
Route::apiResource('balnearios', UserController::class);

// Rutas protegidas para app móvil (Checadores)
Route::middleware(['auth:sanctum', 'mobile.auth'])->prefix('mobile')->group(function () {
    // Información de balnearios
    Route::get('/balneario', [BalnearioController::class, 'index']);
    Route::get('/balneario/{balneario}', [BalnearioController::class, 'show']);
    
    // Gestión de brazaletes
    Route::post('/brazalete/verificar', [BrazaleteController::class, 'verificar']);
    Route::get('/brazalete/buscar', [BrazaleteController::class, 'buscar']);
    Route::get('/brazalete/estadisticas', [BrazaleteController::class, 'estadisticas']);
    
    // Estadísticas del checador
    Route::get('/checador/estadisticas', [BalnearioController::class, 'estadisticasChecador']);
    
    // Autenticación
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user-profile', [AuthController::class, 'userProfile']);
});

// Rutas protegidas para admin panel (Administradores de balneario)
Route::middleware(['auth:sanctum', 'admin.auth'])->prefix('admin')->group(function () {
    // Dashboard principal
    Route::get('/dashboard', [DashboardController::class, 'index']);
    
    // Gestión de balnearios
    Route::get('/balnearios', [BalnearioAdminController::class, 'index']);
    Route::get('/balnearios/{balneario}', [BalnearioAdminController::class, 'show']);
    Route::patch('/balnearios/{balneario}', [BalnearioAdminController::class, 'update']);
    
    // Estadísticas y reportes
    Route::get('/estadisticas', [DashboardController::class, 'estadisticas']);
    Route::get('/reportes/ingresos', [BalnearioAdminController::class, 'reporteIngresos']);
    Route::get('/reportes/egresos', [BalnearioAdminController::class, 'reporteEgresos']);
    Route::get('/reportes/eventos/{evento}', [BalnearioAdminController::class, 'reporteEvento']);
});

// Rutas protegidas para super admin (Super Administradores)
Route::middleware(['auth:sanctum', 'superadmin.auth'])->prefix('superadmin')->group(function () {
    // Gestión de usuarios
    Route::apiResource('/usuarios', UserController::class)->except(['edit', 'create']);
    
    // Gestión de balnearios
    Route::apiResource('/balnearios', BalnearioSuperAdminController::class)->except(['edit', 'create']);
    Route::post('/balnearios/{balneario}/servicios', [BalnearioSuperAdminController::class, 'agregarServicio']);
    Route::delete('/balnearios/{balneario}/servicios/{servicio}', [BalnearioSuperAdminController::class, 'eliminarServicio']);
    
    // Gestión de eventos
    Route::apiResource('/eventos', EventoController::class)->except(['edit', 'create']);
    Route::get('/eventos/{evento}/estadisticas', [EventoController::class, 'estadisticas']);
    
    // Reportes globales
    Route::get('/reportes/globales', [SuperAdminReportesController::class, 'generarReporteGlobal']);
    Route::post('/reportes/por-fechas', [SuperAdminReportesController::class, 'reportePorFechas']);
    Route::get('/reportes/utilizacion', [SuperAdminReportesController::class, 'reporteUtilizacion']);
    
    // Backup y mantenimiento
    Route::post('/backup', [SuperAdminReportesController::class, 'generarBackup']);
});

// Manejo de rutas no encontradas
Route::fallback(function () {
    return response()->json([
        'message' => 'Endpoint no encontrado',
        'status' => 'error',
        'code' => 404
    ], 404);
});