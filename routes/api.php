<?php

// routes/api.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegistroController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/v1/registros/ici', [RegistroController::class, 'registrosici']);
    Route::get('/v1/consulta', fn() => response()->json(['mensaje' => 'Acceso autorizado']));
});
