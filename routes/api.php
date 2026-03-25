<?php

// routes/api.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegistroController;
use App\Http\Controllers\Api\MovimientosController;
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/v1/registros/ici', [RegistroController::class, 'registrosici']);
    Route::get('/v1/consulta', fn() => response()->json(['mensaje' => 'Acceso autorizado']));
});

// Rutas para movimientos
Route::prefix('movimientos')->group(function () {
    // Endpoints para recibir datos
    Route::post('/store', [MovimientosController::class, 'storeMovimientos']);
    Route::post('/store-detalles', [MovimientosController::class, 'storeMovimientosDet']);
    Route::post('/store-completo', [MovimientosController::class, 'storeMovimientosCompleto']);
    
    // Endpoints para consultar datos
    Route::get('/', [MovimientosController::class, 'getMovimientos']);
    Route::get('/estadisticas', [MovimientosController::class, 'getEstadisticas']);
});