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
use App\Http\Controllers\DjangoConfigController;
use App\Http\Controllers\ArchivoController;
use App\Http\Controllers\ProcesamientoHistoricoController;
use App\Http\Controllers\DisaController;
use App\Http\Controllers\UnidadEjecutoraController;
use App\Http\Controllers\AlmacenController;
use App\Http\Controllers\MatrizController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\PedidoController;

Route::middleware(['auth'])->group(function () {
    Route::get('/tokens', [TokenController::class, 'index'])->name('tokens.index');
    Route::post('/tokens/create', [TokenController::class, 'store'])->name('tokens.store');
    Route::delete('/tokens/{tokenId}', [TokenController::class, 'destroy'])->name('tokens.destroy');

    #rutas para guardar parametros de api django
    Route::get('/django-config', [DjangoConfigController::class, 'index'])->name('django-config.index');
    Route::post('/django-config', [DjangoConfigController::class, 'storeOrUpdate'])->name('django-config.store');


    //descargar archivo x archivo
    Route::get('/archivos', [ArchivoController::class, 'index'])->name('archivos.index');
    Route::get('/archivos/descargar/{id}', [ArchivoController::class, 'descargar'])->name('archivos.descargar');

    //descargar en un solo archivo
    Route::get('/archivos/descargar-zip', [ArchivoController::class, 'descargarZip'])
    ->name('archivos.descargarZip');

});

// Route::get('/acceso', [ClaveAccesoController::class, 'form'])->name('acceso.form');
// Route::post('/acceso', [ClaveAccesoController::class, 'verificar'])->name('acceso.verificar');

// Protege la ruta al formulario
Route::middleware('check.formkey')->group(function () {
    // Route::get('/formulario', [RegistroController::class, 'create'])->name('registro.create');
    // Route::post('/registro', [RegistroController::class, 'store'])->name('registro.store');
    // Route::delete('/registros/{registro}', [RegistroController::class, 'destroy'])
    // ->name('registros.destroy')
    // ->middleware('auth');

});

Route::get('/', function () {
    return redirect()->route('login');
});

Route::post('/registro', [RegistroController::class, 'store'])->name('registro.store');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    
    Route::get('/formulario', [RegistroController::class, 'create'])->name('registro.create');
    Route::post('/registro', [RegistroController::class, 'store'])->name('registro.store');
    Route::delete('/registros/{registro}', [RegistroController::class, 'destroy'])
    ->name('registros.destroy');
    
    //Unidades Ejecutoras create,store,index,update,delete
    Route::resource('unidadesejecutoras', \App\Http\Controllers\UnidadEjecutoraController::class);
    
    Route::post('/unidadesejecutoras/import', [UnidadEjecutoraController::class, 'import'])
    ->name('unidadesejecutoras.import');

    //Almacenes
    Route::resource('almacenes', \App\Http\Controllers\AlmacenController::class);
    Route::get('/matriz/search-almacen', [UserController::class, 'searchAlmacen'])->name('matriz.searchAlmacen');


    //Importar Almacenes archivo excel
    Route::post('almacenes/import', [AlmacenController::class, 'import'])->name('almacenes.import');

    //Matriz de disponibilidad
    Route::get ('/matriz/index',  [MatrizController::class, 'index'])->name('matriz.index');


    //configurar plazos
    Route::get ('/admin/plazo',  [PlazoController::class, 'edit'])->name('plazo.edit');
    Route::post('/admin/plazo',  [PlazoController::class, 'update'])->name('plazo.update');
    Route::post('/registros/procesar/{id}', [ProcesarDbfController::class, 'procesar'])->name('registros.procesar');

    Route::get('/admin', [AdminController::class, 'index'])->name('registro.index');
    //configurar claves de acceso al form
    Route::get('/admin/claves', [FormKeyController::class, 'edit'])->name('clave.edit');
    Route::post('/admin/claves', [FormKeyController::class, 'update'])->name('clave.update');

    //Históricos
    Route::get('/historicos', [ProcesamientoHistoricoController::class, 'index'])->name('historicos.index');

    //Disas
    Route::get('/disas', [DisaController::class, 'index'])->name('disas.index');
    Route::post('/disas', [DisaController::class, 'store'])->name('disas.store');
    Route::put('/disas/{disa}', [DisaController::class, 'update'])->name('disas.update');

    //Productos
    Route::resource('productos', ProductoController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::post('productos/import', [ProductoController::class, 'import'])->name('productos.import');

    //Disponibilidad
    Route::get('/registros/data', [App\Http\Controllers\RegistroController::class, 'getData'])
    ->name('registros.data');

    Route::get('/matriz/search', [MatrizController::class, 'search'])->name('matriz.search');

    //Requerimientos
    Route::get('/requerimientos', [MatrizController::class, 'requerimientosIndex'])->name('requerimientos.index');
    Route::get('/requerimientos/data', [MatrizController::class, 'requerimientosData'])->name('requerimientos.data');
    Route::post('/requerimientos/guardar', [MatrizController::class, 'guardarRequerimiento'])->name('requerimientos.guardar');

    // Nuevas rutas para gestión de confirmación
    Route::get('/requerimientos/no-confirmados', [MatrizController::class, 'getRequerimientosNoConfirmados'])->name('requerimientos.no-confirmados');
    Route::post('/requerimientos/confirmar', [MatrizController::class, 'confirmarRequerimientos'])->name('requerimientos.confirmar');
    Route::delete('/requerimientos/eliminar/{id}', [MatrizController::class, 'eliminarRequerimiento'])->name('requerimientos.eliminar');
    Route::put('/requerimientos/editar', [MatrizController::class, 'editarRequerimiento'])->name('requerimientos.editar');

    //Pedidos de los Requerimientos
    Route::get('/pedidos', [PedidoController::class, 'index'])->name('pedidos.index');
    Route::get('/pedidos/productos', [PedidoController::class, 'getProductos'])->name('pedidos.productos');
    Route::get('/pedidos/fer/{pedidoId}', [PedidoController::class, 'generarFER'])->name('pedidos.fer');
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

