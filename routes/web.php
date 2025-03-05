<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ObligacionesController;
use App\Http\Controllers\ArchivoController;
use App\Http\Controllers\DetallesController;
use App\Http\Controllers\ResumenController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomPasswordResetController;
use App\Http\Controllers\CustomRegisterController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\AdminUsersController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\InicioController;
use App\Http\Controllers\CalendarController;
use Illuminate\Support\Facades\Auth;

// Ruta de inicio
Route::get('/', function () {
    if (Auth::check()) {
        Auth::logout();  
        request()->session()->invalidate();  
        request()->session()->regenerateToken();  
    }
    return view('auth.login');  
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    Route::get('/register', function () {
        return redirect('/'); 
    })->name('register');

    Route::get('/inicio', [InicioController::class, 'index'])->name('inicio');

    Route::get('/profile', [UsuarioController::class, 'profile']);

    // Rutas de DashboardController
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/api/resumen-obligaciones', [DashboardController::class, 'obtenerDatosGrafico']);
    Route::post('/api/obtener-avance-total', [DashboardController::class, 'obtenerAvanceTotal'])->name('api.obtenerAvanceTotal');
    Route::post('/api/obtener-avance-periodicidad', [DashboardController::class, 'obtenerResumenPorPeriodicidad']);
    Route::post('/filtrar-requisitos', [DashboardController::class, 'filtrarRequisitos'])->name('filtrar-requisitos');
    Route::post('/descargar-pdf', [DashboardController::class, 'descargarPDF'])->name('descargar-pdf');

    // Rutas de ObligacionesController
    Route::get('/obligaciones', [ObligacionesController::class, 'index'])->name('obligaciones');
    Route::get('/AddObligaciones', [ObligacionesController::class, 'store'])->name('obligaciones.create');
    Route::post('/obtener-detalles', [ObligacionesController::class, 'getDetallesEvidencia'])->name('obtener.detalles');
    Route::post('/obtener-notificaciones', [ObligacionesController::class, 'obtenerNotificaciones'])->name('obtener.notificaciones');
    Route::post('/obtener-tabla-notificaciones', [ObligacionesController::class, 'obtenerTablaNotificaciones'])->name('obtener.tabla.notificaciones');
    Route::post('/requisito/cambiar-estado', [ObligacionesController::class, 'cambiarEstado'])->name('requisito.cambiarEstado');
    Route::post('/obtener-detalle-evidencia', [ObligacionesController::class, 'obtenerDetalleEvidencia'])->name('obtener.detalle.evidencia');
    Route::post('/ruta-enviar-correo-datos-evidencia', [ObligacionesController::class, 'enviarCorreoDatosEvidencia'])->name('enviar.correo.datos.evidencia');
    Route::post('/obligaciones/verificar-archivos', [ObligacionesController::class, 'verificarArchivos'])->name('obligaciones.verificarArchivos');
    Route::post('/actualizar-porcentaje', [ObligacionesController::class, 'actualizarPorcentaje'])->name('actualizar.porcentaje');
    Route::post('/actualizar-suma-porsentaje', [ObligacionesController::class, 'actualizarPorcentajeSuma'])->name('actualizar.suma.porcentaje');
    Route::post('/obligaciones/obtener-estado', [ObligacionesController::class, 'obtenerEstado'])->name('obtener.estado');
    Route::post('/obtener-requisito-detalles', [ObligacionesController::class, 'obtenerRequisitoDetalles'])->name('obtener.requisito.detalles');
    Route::post('/obtener-responsables', [ObligacionesController::class, 'obtenerResponsables'])->name('obtener.responsables');
    Route::post('/filtrar-obligaciones', [ObligacionesController::class, 'filtrarObligaciones'])->name('filtrar.obligaciones');
    Route::post('/approved-result', [ObligacionesController::class, 'obtenerEstadoApproved'])->name('approved.resul');

    Route::get('/obligaciones/usuarios', [ObligacionesController::class, 'obtenerUsuarios'])->name('obligaciones.usuarios');
    Route::post('/guardar-usuario-notificacion', [ObligacionesController::class, 'UsuarioNuevoTablaNotificaciones'])->name('guardar.usuario.notificacion');
    Route::post('/eliminar-notificacion', [ObligacionesController::class, 'eliminarNotificacion'])->name('eliminar.usuario.notificacion');

    // Rutas de DetallesController
    Route::get('/detalles', [DetallesController::class, 'index'])->name('detalles');
    Route::match(['get', 'post'], '/detalles', [DetallesController::class, 'index'])->name('gestion_cumplimiento.detalles.index');
    Route::post('/detalles', [DetallesController::class, 'index'])->name('filtrosDetalles');
    Route::post('/export-detalles', [DetallesController::class, 'export'])->name('export-detalles');
    Route::post('/filtrar-detalle', [DetallesController::class, 'filtrarDetalles'])->name('filtrar-detalle');
    Route::get('/requisitos/{id}', [DetallesController::class, 'show'])->name('requisitos.show');
    Route::get('/obtener-archivos/{fecha_limite_cumplimiento}', [DetallesController::class, 'obtenerArchivosPorFecha'])->name('obtener.archivos.fecha');
    Route::get('/descargar-pdf', [DetallesController::class, 'descargarPDF'])->name('descargar.pdf');

    // Rutas de ResumenController
    Route::get('/resumen', [ResumenController::class, 'index'])->name('resumen');
    Route::post('/api/resumen-obligaciones', [ResumenController::class, 'apiResumenObligaciones']);
    Route::post('/api/obtener-avance-total', [ResumenController::class, 'obtenerAvanceTotal'])->name('api.obtenerAvanceTotal');
    Route::post('/api/obtener-avance-periodicidad', [ResumenController::class, 'obtenerAvancePorPeriodicidad']);

    // Rutas de ArchivoController
    Route::middleware(['throttle:uploads'])->group(function () {
        Route::post('/archivos/subir', [ArchivoController::class, 'subirArchivo'])->name('archivos.subir');
        Route::post('/archivos/eliminar', [ArchivoController::class, 'eliminar'])->name('archivos.eliminar');
    });
    Route::post('/archivos/listar', [ArchivoController::class, 'listarArchivos'])->name('archivos.listar');
    Route::post('/guardar-comentario', [ArchivoController::class, 'storeComment'])->name('guardar.comentario');
    Route::delete('/comentarios/{id}', [ArchivoController::class, 'eliminarComentario'])
        ->middleware('auth') // Solo usuarios autenticados pueden eliminar comentarios
        ->name('comentarios.eliminar');

    // Rutas de AdminUsuarios
    Route::get('/admin-usuarios', [AdminUsersController::class, 'index'])->name('adminUsuarios');
    Route::post('/adminUsuarios/register', [AdminUsersController::class, 'register'])->name('adminUsuarios.register');
    Route::post('/check-email', [AdminUsersController::class, 'checkEmail'])->name('check.email');
    Route::post('/permissions/store', [AdminUsersController::class, 'storePermission'])->name('permissions.store');
    Route::post('/roles/store', [AdminUsersController::class, 'storeRole'])->name('roles.store');
    Route::post('/admin-usuarios/{id}/delete', [AdminUsersController::class, 'destroy'])->name('adminUsuarios.destroy');
    Route::put('/adminUsuarios/update', [AdminUsersController::class, 'update'])->name('adminUsuarios.update');
    Route::post('/admin-roles/create', [AdminUsersController::class, 'createRole'])->name('adminRoles.create');
    Route::post('/admin-permissions/create', [AdminUsersController::class, 'createPermission'])->name('adminPermissions.create');
    Route::delete('/admin-roles/delete/{id}', [AdminUsersController::class, 'deleteRole'])->name('adminRoles.delete');
    Route::delete('/admin-permissions/delete/{id}', [AdminUsersController::class, 'deletePermission'])->name('adminPermissions.delete');
    Route::post('/authorizations/store', [AdminUsersController::class, 'storeAuthorization'])->name('authorizations.store');
    Route::post('/admin/authorizations/create', [AdminUsersController::class, 'createAuthorization'])->name('adminAuthorizations.create');
    Route::delete('/admin/authorizations/delete/{id}', [AdminUsersController::class, 'deleteAuthorization'])->name('adminAuthorizations.delete');

    // Rutas de Notificaciones
    Route::get('/admin-notificaciones', [NotificacionController::class, 'index'])->name('admin.notificaciones');

    // Rutas de Calendario
    Route::get('/gestion-cumplimiento/calendario', [CalendarController::class, 'index'])->name('gestion.calendario');
    Route::get('/api/requisitos', [CalendarController::class, 'fetchRequisitos'])->name('api.requisitos');

    // Aplicar throttle:global a rutas específicas
    Route::middleware(['throttle:global'])->group(function () {
        Route::post('/api/resumen-obligaciones', [DashboardController::class, 'obtenerDatosGrafico']);
        Route::post('/api/obtener-avance-total', [DashboardController::class, 'obtenerAvanceTotal'])->name('api.obtenerAvanceTotal');
        Route::post('/filtrar-requisitos', [DashboardController::class, 'filtrarRequisitos'])->name('filtrar-requisitos');
    });
});

// Rutas de CustomPasswordResetController
Route::get('custom-password-reset', [CustomPasswordResetController::class, 'show'])->name('custom.password.reset');
Route::post('custom-password-reset', [CustomPasswordResetController::class, 'submitRequest'])->name('custom.password.reset.submit');

// Rutas de CustomRegisterController
Route::get('/register_new', [CustomRegisterController::class, 'show'])->name('custom.register_new');
Route::post('/register_new', [CustomRegisterController::class, 'submitRequest'])->name('custom.account.register.submit');