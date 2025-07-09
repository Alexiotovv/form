<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegistroController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\FormKeyController;
use App\Http\Controllers\ClaveAccesoController;

Route::get('/acceso', [ClaveAccesoController::class, 'form'])->name('acceso.form');
Route::post('/acceso', [ClaveAccesoController::class, 'verificar'])->name('acceso.verificar');

// Protege la ruta al formulario
Route::middleware('check.formkey')->group(function () {
    Route::get('/formulario', [RegistroController::class, 'create'])->name('registro.create');
    Route::post('/registro', [RegistroController::class, 'store'])->name('registro.store');
});

#Route::get('/', [RegistroController::class, 'create'])->name('registro.create');
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
