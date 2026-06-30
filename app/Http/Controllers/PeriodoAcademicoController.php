<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePeriodoAcademicoRequest;
use App\Http\Requests\UpdatePeriodoAcademicoRequest;
use App\Models\PeriodoAcademico;
use App\Services\PeriodoAcademicoService;
use Inertia\Inertia;

class PeriodoAcademicoController extends Controller
{
    protected PeriodoAcademicoService $periodoService;

    public function __construct(PeriodoAcademicoService $periodoService)
    {
        $this->periodoService = $periodoService;
    }

    public function index()
    {
        $ofertaAcademicaId = request('oferta_academica_id');
        $periodos = $this->periodoService->listarPeriodos($ofertaAcademicaId);
        $ofertas = $this->periodoService->obtenerOfertas();
        return Inertia::render('PeriodoAcademico/Index', [
            'periodos' => $periodos,
            'ofertas' => $ofertas,
        ]);
    }

    public function create()
    {
        $ofertas = $this->periodoService->obtenerOfertas();
        return Inertia::render('PeriodoAcademico/Create', [
            'ofertas' => $ofertas,
        ]);
    }

    public function store(StorePeriodoAcademicoRequest $request)
    {
        $this->periodoService->crearPeriodo($request->validated());
        return redirect()->route('periodos-academicos.index');
    }

    public function show(PeriodoAcademico $periodoAcademico)
    {
        $periodoConDetalles = $this->periodoService->cargarDetalles($periodoAcademico);
        
        $materiaIds = \App\Models\MallaCurricular::where('oferta_academica_id', $periodoAcademico->oferta_academica_id)
            ->pluck('materia_id');

        $docentes = \App\Models\User::where('is_profesor', true)->orderBy('name')->get(['id', 'name']);
        $gruposCatalog = \App\Models\Grupo::whereIn('materia_id', $materiaIds)->with('materia')->orderBy('codigo')->get();
        $aulas = \App\Models\Aula::orderBy('nombre')->get(['id', 'nombre', 'codigo']);

        $tienePeriodoAnterior = PeriodoAcademico::where('oferta_academica_id', $periodoAcademico->oferta_academica_id)
            ->where('id', '!=', $periodoAcademico->id)
            ->where('fecha_inicio', '<', $periodoAcademico->fecha_inicio)
            ->exists();

        return Inertia::render('PeriodoAcademico/Show', [
            'periodo' => $periodoConDetalles,
            'docentes' => $docentes,
            'gruposCatalog' => $gruposCatalog,
            'aulas' => $aulas,
            'tienePeriodoAnterior' => $tienePeriodoAnterior,
        ]);
    }

    public function copiarGruposDesdeAnterior(PeriodoAcademico $periodoAcademico)
    {
        $periodoAnterior = PeriodoAcademico::where('oferta_academica_id', $periodoAcademico->oferta_academica_id)
            ->where('id', '!=', $periodoAcademico->id)
            ->where('fecha_inicio', '<', $periodoAcademico->fecha_inicio)
            ->orderBy('fecha_inicio', 'desc')
            ->first();

        if (!$periodoAnterior) {
            return redirect()->back()->withErrors([
                'error' => 'No se encontró un período anterior para esta oferta académica.'
            ]);
        }

        $gruposAnteriores = \App\Models\GrupoPeriodo::where('periodo_academico_id', $periodoAnterior->id)
            ->with('horarios')
            ->get();

        $copiados = 0;

        foreach ($gruposAnteriores as $gpAnterior) {
            $existing = \App\Models\GrupoPeriodo::withTrashed()
                ->where('periodo_academico_id', $periodoAcademico->id)
                ->where('grupo_id', $gpAnterior->grupo_id)
                ->first();

            if (!$existing) {
                $nuevoGp = \App\Models\GrupoPeriodo::create([
                    'grupo_id' => $gpAnterior->grupo_id,
                    'periodo_academico_id' => $periodoAcademico->id,
                    'docente_id' => $gpAnterior->docente_id,
                    'cupo_maximo' => $gpAnterior->cupo_maximo,
                ]);

                foreach ($gpAnterior->horarios as $horarioAnterior) {
                    $nuevoGp->horarios()->create([
                        'dia' => $horarioAnterior->dia,
                        'hora_inicio' => $horarioAnterior->hora_inicio,
                        'hora_fin' => $horarioAnterior->hora_fin,
                        'aula_id' => $horarioAnterior->aula_id,
                    ]);
                }
                $copiados++;
            } elseif ($existing->trashed()) {
                $existing->restore();
                $existing->update([
                    'docente_id' => $gpAnterior->docente_id,
                    'cupo_maximo' => $gpAnterior->cupo_maximo,
                ]);

                foreach ($gpAnterior->horarios as $horarioAnterior) {
                    // Restablecer u obtener horarios que no estén duplicados
                    $horarioExists = $existing->horarios()->withTrashed()
                        ->where('dia', $horarioAnterior->dia)
                        ->where('hora_inicio', $horarioAnterior->hora_inicio)
                        ->where('hora_fin', $horarioAnterior->hora_fin)
                        ->where('aula_id', $horarioAnterior->aula_id)
                        ->exists();

                    if (!$horarioExists) {
                        $existing->horarios()->create([
                            'dia' => $horarioAnterior->dia,
                            'hora_inicio' => $horarioAnterior->hora_inicio,
                            'hora_fin' => $horarioAnterior->hora_fin,
                            'aula_id' => $horarioAnterior->aula_id,
                        ]);
                    }
                }
                $copiados++;
            }
        }

        return redirect()->back()->with('success', "Se cargaron {$copiados} grupos con sus docentes y horarios desde el período anterior.");
    }

    public function edit(PeriodoAcademico $periodoAcademico)
    {
        $ofertas = $this->periodoService->obtenerOfertas();
        return Inertia::render('PeriodoAcademico/Edit', [
            'periodo' => $periodoAcademico,
            'ofertas' => $ofertas,
        ]);
    }

    public function update(UpdatePeriodoAcademicoRequest $request, PeriodoAcademico $periodoAcademico)
    {
        $this->periodoService->actualizarPeriodo($periodoAcademico, $request->validated());
        return redirect()->route('periodos-academicos.index');
    }

    public function destroy(PeriodoAcademico $periodoAcademico)
    {
        $this->periodoService->eliminarPeriodo($periodoAcademico);
        return redirect()->route('periodos-academicos.index');
    }
}
