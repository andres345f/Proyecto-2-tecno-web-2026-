<?php

use App\Http\Controllers\AulaController;
use App\Http\Controllers\BuscadorController;
use App\Http\Controllers\EntregaController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\HorarioController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\MallaCurricularController;
use App\Http\Controllers\MateriaController;
use App\Http\Controllers\MatriculaCarreraController;
use App\Http\Controllers\MatriculaGrupoController;
use App\Http\Controllers\MatriculaPeriodoController;
use App\Http\Controllers\OfertaAcademicaController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\PeriodoAcademicoController;
use App\Http\Controllers\PlanPagoController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\TareaController;
use App\Http\Controllers\VisitaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MatriculaDashboardController;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [LandingController::class, 'show'])->name('home');

Route::get('dashboard', function () {
    $user = auth()->user();

    return Inertia::render('Dashboard', [
        'user' => $user,
        'primaryRole' => $user->getPrimaryRole(),
        'roles' => $user->getRoles(),
    ]);
})->middleware(['auth', 'verified', EnsureUserIsActive::class])->name('dashboard');

// Academic management routes (admin only: director, secretaria, propietario)
Route::middleware(['auth', 'verified', EnsureUserIsActive::class, RoleMiddleware::class . ':director,secretaria,propietario'])->group(function () {
    Route::resource('aulas', AulaController::class);
    Route::resource('materias', MateriaController::class);
    Route::resource('ofertas-academicas', OfertaAcademicaController::class)->parameters([
        'ofertas-academicas' => 'oferta',
    ]);
    Route::post('ofertas-academicas/{oferta}/materias', [MallaCurricularController::class, 'store'])->name('ofertas-academicas.materias.store');
    Route::delete('ofertas-academicas/{oferta}/materias/{materia}', [MallaCurricularController::class, 'destroy'])->name('ofertas-academicas.materias.destroy');
    Route::post('ofertas-academicas/{oferta}/materias/{materia}/prerrequisitos', [MallaCurricularController::class, 'storePrerrequisito'])->name('ofertas-academicas.materias.prerrequisitos.store');
    Route::delete('ofertas-academicas/{oferta}/materias/{materia}/prerrequisitos/{prerequisito}', [MallaCurricularController::class, 'destroyPrerrequisito'])->name('ofertas-academicas.materias.prerrequisitos.destroy');
    Route::resource('horarios', HorarioController::class);
    Route::resource('periodos-academicos', PeriodoAcademicoController::class)->parameters([
        'periodos-academicos' => 'periodoAcademico',
    ]);
    Route::post('periodos-academicos/{periodoAcademico}/copiar-grupos', [PeriodoAcademicoController::class, 'copiarGruposDesdeAnterior'])
        ->name('periodos-academicos.copiar-grupos');
    Route::resource('grupo-periodo', \App\Http\Controllers\GrupoPeriodoController::class)->only(['store', 'update', 'destroy']);
    Route::resource('planes-pago', PlanPagoController::class)->parameters([
        'planes-pago' => 'planPago',
    ]);
    Route::resource('grupos', GrupoController::class)->parameters([
        'grupos' => 'grupo',
    ]);
    Route::get('matriculas-carrera/plantilla', [MatriculaCarreraController::class, 'descargarPlantilla'])->name('matriculas-carrera.plantilla');
    Route::post('matriculas-carrera/importar', [MatriculaCarreraController::class, 'importar'])->name('matriculas-carrera.importar');
    Route::resource('matriculas-carrera', MatriculaCarreraController::class)->parameters([
        'matriculas-carrera' => 'matriculaCarrera',
    ]);
    Route::resource('matriculas-periodo', MatriculaPeriodoController::class)->only(['update', 'destroy'])->parameters([
        'matriculas-periodo' => 'matriculaPeriodo',
    ]);
    Route::resource('usuarios', UserController::class)->parameters([
        'usuarios' => 'user',
    ]);
    Route::get('matriculas', [MatriculaDashboardController::class, 'index'])->name('matriculas.index');

    // Reports (director/owner only)
    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
});

// Student enrollment routes
Route::middleware(['auth', 'verified', EnsureUserIsActive::class])->group(function () {
    Route::resource('matriculas-periodo', MatriculaPeriodoController::class)->only(['index', 'create', 'store', 'show'])->parameters([
        'matriculas-periodo' => 'matriculaPeriodo',
    ]);

    Route::get('/mis-grupos', [MatriculaGrupoController::class, 'index'])->name('mis-grupos');
    Route::get('/malla-curricular', [MallaCurricularController::class, 'estudianteIndex'])->name('malla-curricular.estudiante');
    Route::get('/grupos-docente', [GrupoController::class, 'docenteIndex'])->name('grupos.docente.index');
    Route::get('/grupos-docente/{grupo}', [GrupoController::class, 'docenteShow'])->name('grupos.docente.show');

    Route::get('matriculas-grupo/grupo/{grupo}/plantilla-notas', [MatriculaGrupoController::class, 'descargarPlantillaNotas'])->name('matriculas-grupo.plantilla-notas');
    Route::post('matriculas-grupo/grupo/{grupo}/importar-notas', [MatriculaGrupoController::class, 'importarNotas'])->name('matriculas-grupo.importar-notas');

    Route::resource('matriculas-grupo', MatriculaGrupoController::class)->parameters([
        'matriculas-grupo' => 'matriculaGrupo',
    ]);

    // Tarea + Entrega routes
    Route::get('grupos/{grupo}/tareas', [TareaController::class, 'index'])->name('grupos.tareas.index');
    Route::get('grupos/{grupo}/tareas/create', [TareaController::class, 'create'])->name('grupos.tareas.create');
    Route::post('grupos/{grupo}/tareas', [TareaController::class, 'store'])->name('grupos.tareas.store');
    Route::get('grupos/{grupo}/tareas/{tarea}', [TareaController::class, 'show'])->name('grupos.tareas.show');
    Route::put('grupos/{grupo}/tareas/{tarea}', [TareaController::class, 'update'])->name('grupos.tareas.update');
    Route::delete('grupos/{grupo}/tareas/{tarea}', [TareaController::class, 'destroy'])->name('grupos.tareas.destroy');
    Route::post('entregas', [EntregaController::class, 'store'])->name('entregas.store');
    Route::get('entregas/{entrega}', [EntregaController::class, 'show'])->name('entregas.show');
    Route::put('entregas/{entrega}/calificar', [EntregaController::class, 'calificar'])->name('entregas.calificar');
    Route::get('entregas/{entrega}/download', [EntregaController::class, 'download'])->name('entregas.download');

    // Pago routes (Inertia)
    Route::get('/pagos', [PagoController::class, 'index'])->name('pagos.index');
});

// API routes for pagos (JSON)
Route::middleware('auth')->prefix('api/pagos')->name('api.pagos.')->group(function () {
    Route::post('/generar-qr', [PagoController::class, 'generarQr'])->name('generar-qr');
    Route::get('/estado/{transaccion}', [PagoController::class, 'consultarEstado'])->name('estado');
    Route::post('/webhook', [PagoController::class, 'webhook'])->name('webhook');
});

// API routes for buscador global
Route::middleware('auth')->prefix('api')->name('api.')->group(function () {
    Route::get('/buscador-global', [BuscadorController::class, 'search'])->name('buscador-global');
});

// API route for visit counter (public - no auth required)
Route::prefix('api')->name('api.')->group(function () {
    Route::post('/visitas', [VisitaController::class, 'store'])->name('visitas.store');
});

// API route for PagoFacil callback/webhook (public - no auth required)
Route::prefix('api')->group(function () {
    Route::post('/pagofacil/callback', [PagoController::class, 'pagofacilCallback'])->name('pagofacil.callback');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
