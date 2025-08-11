<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\RegistroController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\FormKeyController;
use App\Http\Controllers\ClaveAccesoController;
use App\Http\Controllers\PlazoController;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\ProcesarDbfController;


Route::middleware(['auth'])->group(function () {
    Route::get('/tokens', [TokenController::class, 'index'])->name('tokens.index');
    Route::post('/tokens/create', [TokenController::class, 'store'])->name('tokens.store');
    Route::delete('/tokens/{tokenId}', [TokenController::class, 'destroy'])->name('tokens.destroy');
    
});

Route::get('/acceso', [ClaveAccesoController::class, 'form'])->name('acceso.form');
Route::post('/acceso', [ClaveAccesoController::class, 'verificar'])->name('acceso.verificar');

// Protege la ruta al formulario
Route::middleware('check.formkey')->group(function () {
    Route::get('/formulario', [RegistroController::class, 'create'])->name('registro.create');
    Route::post('/registro', [RegistroController::class, 'store'])->name('registro.store');
    Route::delete('/registros/{registro}', [RegistroController::class, 'destroy'])
    ->name('registros.destroy')
    ->middleware('auth');

});

Route::middleware('auth')->group(function () {
    Route::get ('/admin/plazo',  [PlazoController::class, 'edit'])->name('plazo.edit');
    Route::post('/admin/plazo',  [PlazoController::class, 'update'])->name('plazo.update');
    Route::post('/registros/procesar/{id}', [ProcesarDbfController::class, 'procesar'])
    ->name('registros.procesar');
});


Route::get('/', function () {
    return redirect()->route('acceso.form');
});

Route::post('/registro', [RegistroController::class, 'store'])->name('registro.store');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


Route::middleware('auth')->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/claves', [FormKeyController::class, 'edit'])->name('clave.edit');
    Route::post('/admin/claves', [FormKeyController::class, 'update'])->name('clave.update');
});


Route::middleware(['auth', 'is_admin'])->group(function () {
    // Admin routes
    Route::prefix('admin')->group(function () {
        Route::resource('users', UserController::class)->names([
            'index'   => 'admin.users.index',
            'create'  => 'admin.users.create',
            'edit'    => 'admin.users.edit',
            'store'   => 'admin.users.store',
            'show'    => 'admin.users.show',
            'update'  => 'admin.users.update',
            'destroy' => 'admin.users.destroy',
        ]);
    });
});

Route::resource('establecimientos', App\Http\Controllers\EstablecimientoController::class)->except(['create', 'edit', 'show']);
Route::get('/gracias', function () {
    return view('registro.success');
})->name('gracias');

